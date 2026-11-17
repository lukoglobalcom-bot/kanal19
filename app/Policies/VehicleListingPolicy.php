<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VehicleListing;

/**
 * Giełda pojazdów: wystawiać może każde konto z potwierdzonym telefonem,
 * poprawiać i usuwać – właściciel ogłoszenia albo administrator.
 */
class VehicleListingPolicy
{
    public function create(User $user): bool
    {
        return ! $user->isBlocked() && ($user->isAdmin() || $user->hasVerifiedPhone());
    }

    public function update(User $user, VehicleListing $listing): bool
    {
        return $user->isAdmin() || ((int) $listing->user_id === (int) $user->id && ! $user->isBlocked());
    }

    public function delete(User $user, VehicleListing $listing): bool
    {
        return $this->update($user, $listing);
    }
}
