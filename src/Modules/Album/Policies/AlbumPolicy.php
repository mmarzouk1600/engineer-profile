<?php

namespace Modules\Album\Policies;

use App\Models\User;
use Modules\Album\Entities\Album;

class AlbumPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Album $album): bool
    {
        return $user->isAdmin() || $album->status === \Modules\Album\Enums\AlbumStatus::Published;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Album $album): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Album $album): bool
    {
        return $user->isAdmin();
    }
}
