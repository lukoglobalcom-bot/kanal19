<?php

namespace App\Http\Controllers;

use App\Models\JobOffer;
use App\Models\Post;
use App\Models\VehicleListing;
use Illuminate\Http\Response;

/** Mapa strony dla wyszukiwarek: aktywne oferty, pojazdy, artykuły i strony stałe. */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect(['home', 'vehicles.index', 'posts.index', 'map.index', 'cb.index', 'route.index', 'pause', 'home.trip', 'calculator', 'terms', 'privacy'])
            ->map(fn ($name) => ['loc' => route($name), 'lastmod' => null]);

        JobOffer::active()->latest('published_at')->limit(5000)->get(['id', 'published_at', 'updated_at'])
            ->each(fn ($o) => $urls->push(['loc' => route('offers.show', $o), 'lastmod' => ($o->updated_at ?? $o->published_at)?->toAtomString()]));
        VehicleListing::active()->latest('id')->limit(5000)->get(['id', 'updated_at'])
            ->each(fn ($v) => $urls->push(['loc' => route('vehicles.show', $v), 'lastmod' => $v->updated_at?->toAtomString()]));
        Post::published()->latest('published_at')->limit(5000)->get(['id', 'updated_at'])
            ->each(fn ($p) => $urls->push(['loc' => route('posts.show', $p), 'lastmod' => $p->updated_at?->toAtomString()]));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $u) {
            $xml .= '  <url><loc>'.e($u['loc']).'</loc>'.($u['lastmod'] ? '<lastmod>'.$u['lastmod'].'</lastmod>' : '').'</url>'."\n";
        }
        $xml .= '</urlset>'."\n";

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
