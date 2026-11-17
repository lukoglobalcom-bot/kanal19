<?php

namespace App\Policies;

use App\Models\AdCampaign;
use App\Models\User;

/**
 * Kampanie: reklamodawca tworzy i poprawia swoją kampanię do momentu akceptacji.
 * Po akceptacji zmieniać może wyłącznie administrator.
 */
class AdCampaignPolicy
{
    public function create(User $user): bool
    {
        return $user->isAdvertiser() && ! $user->isBlocked();
    }

    public function view(User $user, AdCampaign $campaign): bool
    {
        return $user->isAdmin() || (int) $campaign->user_id === (int) $user->id;
    }

    public function update(User $user, AdCampaign $campaign): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return (int) $campaign->user_id === (int) $user->id && ! $campaign->isAccepted() && ! $user->isBlocked();
    }
}
