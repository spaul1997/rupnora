<?php

namespace App\Policies;

use App\Models\AffiliateProfile;
use App\Models\User;

class AffiliateProfilePolicy
{
    public function view(User $user, AffiliateProfile $profile): bool
    {
        return $user->isAdmin() || $profile->user_id === $user->id;
    }

    public function update(User $user, AffiliateProfile $profile): bool
    {
        return $user->isAdmin() || $profile->user_id === $user->id;
    }
}
