<?php

namespace App\Http\Controllers;

use App\Models\CompanyReview;
use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Publiczna strona firmy: aktywne oferty i opinie kierowców. */
class CompanyPageController extends Controller
{
    private function company(User $company): User
    {
        abort_unless($company->isCompany() && ! $company->blocked_at, 404);

        return $company->loadMissing('companyProfile');
    }

    public function show(Request $request, User $company): View
    {
        $company = $this->company($company);
        $viewer = $request->user();

        return view('companies.show', [
            'company' => $company,
            'offers' => JobOffer::active()->where('user_id', $company->id)->latest('published_at')->get(),
            'reviews' => CompanyReview::visible()->where('company_id', $company->id)->with('author.driverProfile')->latest()->paginate(20),
            'summary' => CompanyReview::summaryFor($company->id),
            'aspects' => collect(CompanyReview::ASPECTS)->map(fn ($label, $k) => CompanyReview::visible()->where('company_id', $company->id)->avg($k)),
            'mine' => $viewer ? CompanyReview::where('company_id', $company->id)->where('user_id', $viewer->id)->first() : null,
            'canReview' => $viewer && $viewer->isDriver() && $viewer->hasVerifiedPhone(),
        ]);
    }

    public function review(Request $request, User $company): RedirectResponse
    {
        $company = $this->company($company);
        $user = $request->user();
        abort_unless($user->isDriver(), 403);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'pay' => ['nullable', 'integer', 'between:1,5'],
            'vehicles' => ['nullable', 'integer', 'between:1,5'],
            'atmosphere' => ['nullable', 'integer', 'between:1,5'],
            'worked_there' => ['nullable', 'boolean'],
            'comment' => ['required', 'string', 'min:20', 'max:1000'],
            'rules' => ['accepted'],
        ]);
        unset($data['rules']);
        $data['worked_there'] = $request->boolean('worked_there');

        $review = CompanyReview::firstOrNew(['company_id' => $company->id, 'user_id' => $user->id]);
        $review->company_id = $company->id;
        $review->user_id = $user->id;
        $review->fill($data);
        $review->hidden = false;
        $review->save();

        return redirect()->route('companies.show', $company)->with('status', __('Dziękujemy za opinię!'));
    }

    public function reply(Request $request, CompanyReview $review): RedirectResponse
    {
        abort_unless((int) $review->company_id === (int) $request->user()->id, 403);
        $review->forceFill([
            'reply' => $request->validate(['reply' => ['required', 'string', 'min:5', 'max:1000']])['reply'],
            'replied_at' => now(),
        ])->save();

        return back()->with('status', __('Odpowiedź opublikowana.'));
    }

    public function report(Request $request, CompanyReview $review): RedirectResponse
    {
        abort_if((int) $review->user_id === (int) $request->user()->id, 422);
        $review->reports()->firstOrCreate(['user_id' => $request->user()->id]);
        if ($review->reports()->count() >= CompanyReview::HIDE_AFTER_REPORTS) {
            $review->forceFill(['hidden' => true])->save();
        }

        return back()->with('status', __('Dziękujemy, zgłoszenie przyjęte.'));
    }

    public function destroy(Request $request, CompanyReview $review): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || (int) $review->user_id === (int) $user->id, 403);
        $review->delete();

        return back()->with('status', __('Opinia usunięta.'));
    }
}
