<?php

namespace App\Policies;

use App\Models\JobOffer;
use App\Models\User;

/**
 * Zasady dla ofert pracy:
 * - dodawać może tylko zweryfikowana firma (lub admin),
 * - przed publikacją firma może poprawiać swój szkic,
 * - po publikacji edytować i usuwać może wyłącznie administrator.
 */
class JobOfferPolicy
{
    public function create(User $user): bool
    {
        return $user->isAdmin() || ($user->isCompany() && ! $user->isBlocked());
    }

    public function updateDraft(User $user, JobOffer $offer): bool
    {
        return ! $offer->isPublished() && $offer->user_id === $user->id && ! $user->isBlocked();
    }

    public function update(User $user, JobOffer $offer): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, JobOffer $offer): bool
    {
        return $user->isAdmin();
    }

    public function viewApplications(User $user, JobOffer $offer): bool
    {
        return $user->isAdmin() || $offer->user_id === $user->id;
    }

    public function apply(User $user, JobOffer $offer): bool
    {
        return $user->isDriver() && $user->hasVerifiedPhone() && ! $user->isBlocked() && $offer->isActive();
    }
}
