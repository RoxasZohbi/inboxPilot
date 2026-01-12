<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Email;

class DashboardComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Get all Google account IDs for the authenticated user
            $googleAccountIds = $user->googleAccounts()->pluck('id')->toArray();
            
            // Get unlisted pending emails count
            $unlistedPendingCount = Email::whereIn('google_account_id', $googleAccountIds)
                ->unlisted()
                ->pending()
                ->count();
            
            $view->with('unlistedPendingCount', $unlistedPendingCount);
        }
    }
}
