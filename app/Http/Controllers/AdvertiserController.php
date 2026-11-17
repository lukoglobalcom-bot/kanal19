<?php

namespace App\Http\Controllers;

use App\Models\AdCampaign;
use App\Models\AdCreative;
use App\Support\Languages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Panel reklamodawcy: kampania → reklama i artykuł w każdym języku → podgląd → akceptacja (blokada edycji).
 * Ten sam edytor obsługuje administratora, który może zmieniać także zaakceptowane kampanie.
 */
class AdvertiserController extends Controller
{
    public function index(Request $request): View
    {
        return view('advertiser.index', [
            'campaigns' => AdCampaign::where('user_id', $request->user()->id)->withCount('creatives')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', AdCampaign::class);

        return view('advertiser.campaign-create', ['campaign' => new AdCampaign(['format' => 'billboard', 'languages' => [app()->getLocale()]])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', AdCampaign::class);
        $campaign = new AdCampaign($this->validatedCampaign($request));
        $campaign->user_id = $request->user()->id;
        $campaign->save();

        return redirect()->route('advertiser.campaigns.edit', $campaign)->with('status', __('Kampania utworzona. Dodaj reklamę i artykuł w każdym języku.'));
    }

    public function edit(Request $request, AdCampaign $campaign): View
    {
        $this->authorize('view', $campaign);
        $locale = $request->query('jezyk', $campaign->languages[0] ?? 'pl');
        abort_unless(in_array($locale, $campaign->languages ?? [], true), 404);

        return view('advertiser.campaign-edit', [
            'campaign' => $campaign->load('creatives'),
            'locale' => $locale,
            'creative' => $campaign->creatives->firstWhere('locale', $locale) ?? new AdCreative(['locale' => $locale]),
            'canEdit' => $request->user()->can('update', $campaign),
        ]);
    }

    public function update(Request $request, AdCampaign $campaign): RedirectResponse
    {
        $this->authorize('update', $campaign);
        $campaign->fill($this->validatedCampaign($request))->save();
        $campaign->creatives()->whereNotIn('locale', $campaign->languages)->get()->each(fn ($c) => $this->deleteCreative($c));

        return redirect()->route('advertiser.campaigns.edit', $campaign)->with('status', __('Zmiany zapisane.'));
    }

    public function saveCreative(Request $request, AdCampaign $campaign, string $locale): RedirectResponse
    {
        $this->authorize('update', $campaign);
        abort_unless(in_array($locale, $campaign->languages ?? [], true), 404);

        $data = $request->validate([
            'headline' => ['required', 'string', 'max:80'],
            'text' => ['required', 'string', 'max:200'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'article_title' => ['required', 'string', 'max:150'],
            'article_body' => ['required', 'string', 'min:100', 'max:20000'],
        ]);

        $creative = $campaign->creatives()->firstOrNew(['locale' => $locale]);
        if ($request->hasFile('image')) {
            if ($creative->image) {
                File::delete(public_path($creative->image));
            }
            $dir = 'uploads/reklamy/'.$campaign->id;
            $name = Str::random(24).'.'.$request->file('image')->guessExtension();
            $request->file('image')->move(public_path($dir), $name);
            $data['image'] = $dir.'/'.$name;
        } else {
            unset($data['image']);
        }
        $creative->fill($data)->save();

        $next = $campaign->missingLanguages()[0] ?? null;

        return redirect()->route($next ? 'advertiser.campaigns.edit' : 'advertiser.campaigns.preview', $next ? [$campaign, 'jezyk' => $next] : $campaign)
            ->with('status', __('Zapisano wersję :lang.', ['lang' => Languages::ALL[$locale] ?? $locale]));
    }

    public function preview(Request $request, AdCampaign $campaign): View
    {
        $this->authorize('view', $campaign);

        return view('advertiser.campaign-preview', [
            'campaign' => $campaign->load('creatives'),
            'missing' => $campaign->missingLanguages(),
            'canEdit' => $request->user()->can('update', $campaign),
        ]);
    }

    public function accept(Request $request, AdCampaign $campaign): RedirectResponse
    {
        $this->authorize('update', $campaign);
        abort_if($campaign->isAccepted(), 403);
        $request->validate(['confirm' => ['accepted']]);

        if ($missing = $campaign->missingLanguages()) {
            return back()->withErrors(['confirm' => __('Brakuje reklamy i artykułu w językach: :list', ['list' => implode(', ', $missing)])]);
        }

        $campaign->forceFill(['accepted_at' => now()])->save();

        return redirect()->route('advertiser.index')->with('status', __('Kampania zaakceptowana. Od teraz zmiany może wprowadzać tylko administrator. Kampania ruszy po włączeniu przez administratora.'));
    }

    public function article(Request $request, AdCreative $creative): View
    {
        $campaign = $creative->campaign;
        if (! $campaign->isLive()) {
            abort_unless($request->user()?->can('view', $campaign), 404);
        }

        return view('advertiser.article', ['creative' => $creative, 'campaign' => $campaign]);
    }

    private function validatedCampaign(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'format' => ['required', Rule::in(array_keys(AdCampaign::FORMATS))],
            'target_url' => ['required', 'url:https,http', 'max:255'],
            'languages' => ['required', 'array', 'min:1'],
            'languages.*' => [Rule::in(array_keys(Languages::ALL))],
        ]);
    }

    private function deleteCreative(AdCreative $creative): void
    {
        if ($creative->image) {
            File::delete(public_path($creative->image));
        }
        $creative->delete();
    }
}
