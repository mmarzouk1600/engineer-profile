<?php

namespace Modules\Album\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Modules\Album\Entities\Album;
use Modules\Album\Entities\AlbumFile;
use Modules\Album\Entities\Payment;
use Modules\Album\Entities\Purchase;
use Modules\Album\Enums\AlbumStatus;
use Modules\Album\Enums\PurchaseStatus;
use Tests\TestCase;

class AlbumModuleTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function customer(): User
    {
        return User::factory()->create(['role' => User::ROLE_CUSTOMER]);
    }

    private function token(User $user): string
    {
        return auth('api')->login($user);
    }

    private function authHeaders(User $user): array
    {
        return ['Authorization' => 'Bearer ' . $this->token($user)];
    }

    private function publishedAlbum(array $overrides = []): Album
    {
        return Album::create(array_merge([
            'title' => 'Modern Villa Structural Design',
            'slug' => 'modern-villa-structural-design',
            'description' => 'Full structural drawings for a modern villa.',
            'price' => 250,
            'currency' => 'SAR',
            'status' => AlbumStatus::Published->value,
            'created_by' => $this->admin()->id,
        ], $overrides));
    }

    // ---------------------------------------------------------------
    // Authentication
    // ---------------------------------------------------------------

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)->assertJsonStructure(['token', 'user']);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertOk()->assertJsonStructure(['token']);
    }

    public function test_unauthenticated_user_cannot_access_admin_albums(): void
    {
        $this->getJson('/api/admin/albums')->assertStatus(401);
    }

    public function test_non_admin_cannot_access_admin_albums(): void
    {
        $customer = $this->customer();

        $this->withHeaders($this->authHeaders($customer))
            ->getJson('/api/admin/albums')
            ->assertStatus(403);
    }

    // ---------------------------------------------------------------
    // Albums
    // ---------------------------------------------------------------

    public function test_admin_can_create_album(): void
    {
        $admin = $this->admin();

        $response = $this->withHeaders($this->authHeaders($admin))->postJson('/api/admin/albums', [
            'title' => 'Villa Structural Drawings',
            'description' => 'A complete set of structural drawings.',
            'price' => 300,
            'currency' => 'SAR',
            'status' => 'draft',
        ]);

        $response->assertStatus(201)->assertJsonPath('data.title', 'Villa Structural Drawings');
        $this->assertDatabaseHas('albums', ['title' => 'Villa Structural Drawings']);
    }

    public function test_admin_can_upload_images_and_select_cover(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $album = $this->publishedAlbum();

        $upload = $this->withHeaders($this->authHeaders($admin))->postJson(
            "/api/admin/albums/{$album->slug}/images",
            ['images' => [UploadedFile::fake()->image('plan1.jpg', 1200, 900)]]
        );

        $upload->assertStatus(201);
        $imageId = $upload->json('data.0.id');

        $this->assertDatabaseHas('album_images', ['album_id' => $album->id]);
        $this->assertEquals($imageId, $album->fresh()->cover_image_id, 'First uploaded image should become the cover automatically.');
    }

    public function test_admin_can_upload_files(): void
    {
        Storage::fake('local');
        $admin = $this->admin();
        $album = $this->publishedAlbum();

        $response = $this->withHeaders($this->authHeaders($admin))->postJson(
            "/api/admin/albums/{$album->slug}/files",
            ['files' => [UploadedFile::fake()->create('structural.pdf', 500, 'application/pdf')]]
        );

        $response->assertStatus(201);
        $this->assertDatabaseHas('album_files', ['album_id' => $album->id, 'original_name' => 'structural.pdf']);
    }

    public function test_public_can_browse_only_published_albums(): void
    {
        $this->publishedAlbum();
        Album::create([
            'title' => 'Draft Album', 'slug' => 'draft-album', 'price' => 100,
            'currency' => 'SAR', 'status' => AlbumStatus::Draft->value, 'created_by' => $this->admin()->id,
        ]);

        $response = $this->getJson('/api/albums');

        $response->assertOk();
        $titles = collect($response->json('data'))->pluck('title');
        $this->assertTrue($titles->contains('Modern Villa Structural Design'));
        $this->assertFalse($titles->contains('Draft Album'));
    }

    public function test_search_matches_title_and_description(): void
    {
        $this->publishedAlbum();

        $response = $this->getJson('/api/albums?search=villa');

        $response->assertOk();
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    // ---------------------------------------------------------------
    // Purchases
    // ---------------------------------------------------------------

    public function test_user_can_initiate_a_purchase(): void
    {
        Http::fake([
            '*/charges' => Http::response([
                'id' => 'chg_test_123',
                'status' => 'INITIATED',
                'transaction' => ['url' => 'https://tap.company/pay/chg_test_123'],
            ], 200),
        ]);

        $customer = $this->customer();
        $album = $this->publishedAlbum();

        $response = $this->withHeaders($this->authHeaders($customer))
            ->postJson("/api/albums/{$album->slug}/purchase");

        $response->assertOk()->assertJsonPath('already_purchased', false);
        $this->assertDatabaseHas('purchases', [
            'user_id' => $customer->id, 'album_id' => $album->id, 'status' => PurchaseStatus::Pending->value,
        ]);
    }

    public function test_user_cannot_purchase_the_same_album_twice_after_successful_payment(): void
    {
        $customer = $this->customer();
        $album = $this->publishedAlbum();

        $purchase = Purchase::create([
            'user_id' => $customer->id, 'album_id' => $album->id, 'amount' => $album->price,
            'currency' => 'SAR', 'status' => PurchaseStatus::Paid->value, 'payment_gateway' => 'tap', 'paid_at' => now(),
        ]);

        $response = $this->withHeaders($this->authHeaders($customer))
            ->postJson("/api/albums/{$album->slug}/purchase");

        $response->assertOk()->assertJsonPath('already_purchased', true);
        $this->assertEquals(1, Purchase::where('user_id', $customer->id)->where('album_id', $album->id)->count());
    }

    // ---------------------------------------------------------------
    // Download security
    // ---------------------------------------------------------------

    private function albumWithFile(): array
    {
        Storage::fake('local');
        $album = $this->publishedAlbum();
        Storage::disk('local')->put('albums/files/test.pdf', 'dummy-pdf-content');
        $file = AlbumFile::create([
            'album_id' => $album->id, 'path' => 'albums/files/test.pdf',
            'original_name' => 'structural.pdf', 'mime_type' => 'application/pdf', 'size' => 18, 'sort_order' => 1,
        ]);

        return [$album, $file];
    }

    public function test_unauthenticated_user_cannot_download(): void
    {
        [$album, $file] = $this->albumWithFile();

        $this->getJson("/api/albums/{$album->slug}/files/{$file->id}/download")->assertStatus(401);
    }

    public function test_authenticated_unpaid_user_cannot_download(): void
    {
        [$album, $file] = $this->albumWithFile();
        $customer = $this->customer();

        $this->withHeaders($this->authHeaders($customer))
            ->getJson("/api/albums/{$album->slug}/files/{$file->id}/download")
            ->assertStatus(403);
    }

    public function test_authenticated_paid_user_can_download(): void
    {
        [$album, $file] = $this->albumWithFile();
        $customer = $this->customer();

        Purchase::create([
            'user_id' => $customer->id, 'album_id' => $album->id, 'amount' => $album->price,
            'currency' => 'SAR', 'status' => PurchaseStatus::Paid->value, 'payment_gateway' => 'tap', 'paid_at' => now(),
        ]);

        $this->withHeaders($this->authHeaders($customer))
            ->get("/api/albums/{$album->slug}/files/{$file->id}/download")
            ->assertOk();
    }

    public function test_user_who_purchased_album_a_cannot_download_album_b_file(): void
    {
        [$albumA] = $this->albumWithFile();
        $albumB = $this->publishedAlbum(['title' => 'Other Album', 'slug' => 'other-album']);
        $fileB = AlbumFile::create([
            'album_id' => $albumB->id, 'path' => 'albums/files/other.pdf',
            'original_name' => 'other.pdf', 'mime_type' => 'application/pdf', 'size' => 10, 'sort_order' => 1,
        ]);

        $customer = $this->customer();
        Purchase::create([
            'user_id' => $customer->id, 'album_id' => $albumA->id, 'amount' => $albumA->price,
            'currency' => 'SAR', 'status' => PurchaseStatus::Paid->value, 'payment_gateway' => 'tap', 'paid_at' => now(),
        ]);

        $this->withHeaders($this->authHeaders($customer))
            ->getJson("/api/albums/{$albumB->slug}/files/{$fileB->id}/download")
            ->assertStatus(403);
    }

    // ---------------------------------------------------------------
    // Tap webhook
    // ---------------------------------------------------------------

    public function test_webhook_marks_purchase_as_paid_on_successful_charge(): void
    {
        $customer = $this->customer();
        $album = $this->publishedAlbum();

        $purchase = Purchase::create([
            'user_id' => $customer->id, 'album_id' => $album->id, 'amount' => $album->price,
            'currency' => 'SAR', 'status' => PurchaseStatus::Pending->value, 'payment_gateway' => 'tap',
        ]);

        $payment = Payment::create([
            'user_id' => $customer->id, 'album_id' => $album->id, 'purchase_id' => $purchase->id,
            'gateway' => 'tap', 'charge_id' => 'chg_webhook_test', 'status' => PurchaseStatus::Pending->value,
            'amount' => $album->price, 'currency' => 'SAR',
        ]);

        Http::fake([
            '*/charges/chg_webhook_test' => Http::response(['id' => 'chg_webhook_test', 'status' => 'CAPTURED'], 200),
        ]);

        $response = $this->postJson('/api/payments/tap/webhook', ['id' => 'chg_webhook_test']);

        $response->assertOk();
        $this->assertEquals(PurchaseStatus::Paid, $purchase->fresh()->status);
    }

    public function test_webhook_is_idempotent_and_does_not_duplicate_purchases(): void
    {
        $customer = $this->customer();
        $album = $this->publishedAlbum();

        $purchase = Purchase::create([
            'user_id' => $customer->id, 'album_id' => $album->id, 'amount' => $album->price,
            'currency' => 'SAR', 'status' => PurchaseStatus::Pending->value, 'payment_gateway' => 'tap',
        ]);

        Payment::create([
            'user_id' => $customer->id, 'album_id' => $album->id, 'purchase_id' => $purchase->id,
            'gateway' => 'tap', 'charge_id' => 'chg_idempotent', 'status' => PurchaseStatus::Pending->value,
            'amount' => $album->price, 'currency' => 'SAR',
        ]);

        Http::fake([
            '*/charges/chg_idempotent' => Http::response(['id' => 'chg_idempotent', 'status' => 'CAPTURED'], 200),
        ]);

        $this->postJson('/api/payments/tap/webhook', ['id' => 'chg_idempotent'])->assertOk();
        $this->postJson('/api/payments/tap/webhook', ['id' => 'chg_idempotent'])->assertOk();

        $this->assertEquals(1, Purchase::where('user_id', $customer->id)->where('album_id', $album->id)->count());
    }

    public function test_failed_payment_does_not_unlock_files(): void
    {
        $customer = $this->customer();
        $album = $this->publishedAlbum();

        $purchase = Purchase::create([
            'user_id' => $customer->id, 'album_id' => $album->id, 'amount' => $album->price,
            'currency' => 'SAR', 'status' => PurchaseStatus::Pending->value, 'payment_gateway' => 'tap',
        ]);

        Payment::create([
            'user_id' => $customer->id, 'album_id' => $album->id, 'purchase_id' => $purchase->id,
            'gateway' => 'tap', 'charge_id' => 'chg_failed', 'status' => PurchaseStatus::Pending->value,
            'amount' => $album->price, 'currency' => 'SAR',
        ]);

        Http::fake([
            '*/charges/chg_failed' => Http::response(['id' => 'chg_failed', 'status' => 'DECLINED'], 200),
        ]);

        $this->postJson('/api/payments/tap/webhook', ['id' => 'chg_failed'])->assertOk();

        $this->assertEquals(PurchaseStatus::Failed, $purchase->fresh()->status);
    }
}
