<?php

namespace App\Http\Controllers;

use App\Models\CbMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * CB Kanał 19: tekstowe „radio” kierowców. Kanały krajów, opcjonalny numer drogi, wiadomości znikają po 3 h.
 */
class CbController extends Controller
{
    public function index(): View
    {
        return view('cb.index');
    }

    public function messages(Request $request): JsonResponse
    {
        $data = $request->validate([
            'kanal' => ['required', Rule::in(CbMessage::CHANNELS)],
            'droga' => ['nullable', 'string', 'max:12'],
            'po' => ['nullable', 'integer', 'min:0'],
        ]);
        $viewer = $request->user();

        $messages = CbMessage::visible()->where('channel', $data['kanal'])
            ->when($data['droga'] ?? null, fn ($q, $road) => $q->where('road', strtoupper(trim($road))))
            ->when($data['po'] ?? null, fn ($q, $after) => $q->where('id', '>', $after))
            ->with('author.driverProfile', 'author.companyProfile', 'author.advertiserProfile')
            ->latest('id')->limit(100)->get()->reverse()->values();

        return response()->json($messages->map(fn (CbMessage $m) => [
            'id' => $m->id,
            'who' => $m->signature(),
            'road' => $m->road,
            'body' => $m->body,
            'minutes' => (int) $m->created_at->diffInMinutes(now()),
            'mine' => $viewer && (int) $m->user_id === (int) $viewer->id,
        ]));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'channel' => ['required', Rule::in(CbMessage::CHANNELS)],
            'road' => ['nullable', 'string', 'max:12', 'regex:/^[A-Za-z0-9 .\-]+$/'],
            'body' => ['required', 'string', 'min:2', 'max:280'],
        ]);
        $data['road'] = isset($data['road']) && $data['road'] !== '' ? strtoupper(trim($data['road'])) : null;

        $m = new CbMessage($data);
        $m->user_id = $request->user()->id;
        $m->save();

        return response()->json(['ok' => true, 'id' => $m->id]);
    }

    public function report(Request $request, CbMessage $message): JsonResponse
    {
        abort_if((int) $message->user_id === (int) $request->user()->id, 422);
        $message->reports()->firstOrCreate(['user_id' => $request->user()->id]);
        if ($message->reports()->count() >= CbMessage::HIDE_AFTER_REPORTS) {
            $message->forceFill(['hidden' => true])->save();
        }

        return response()->json(['ok' => true, 'message' => __('Dziękujemy, zgłoszenie przyjęte.')]);
    }

    public function destroy(Request $request, CbMessage $message): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || (int) $message->user_id === (int) $user->id, 403);
        $message->delete();

        return response()->json(['ok' => true]);
    }
}
