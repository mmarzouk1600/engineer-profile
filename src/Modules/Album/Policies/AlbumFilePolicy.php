<?php

namespace Modules\Album\Policies;

use App\Models\User;
use Modules\Album\Entities\Album;
use Modules\Album\Entities\AlbumFile;
use Modules\Album\Services\PurchaseService;

class AlbumFilePolicy
{
    public function __construct(private PurchaseService $purchaseService) {}

    public function download(User $user, Album $album, AlbumFile $file): bool
    {
        if ($file->album_id !== $album->id) {
            return false;
        }

        return $this->purchaseService->userHasPaidAccess($user, $album);
    }
}
