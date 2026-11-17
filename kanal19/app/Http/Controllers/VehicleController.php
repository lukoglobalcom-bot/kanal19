<?php

namespace App\Http\Controllers;

use App\Models\VehicleListing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Giełda pojazdów: lista z filtrami, ogłoszenie, wystawianie ze zdjęciami (szkic → podgląd → publikacja).
 */
class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'typ' => ['nullable', Rule::in(array_keys(VehicleListing::TYPES))],
            'stan' => ['nullable', Rule::in(array_keys(VehicleListing::CONDITIONS))],
            'marka' => ['nullable', 'string', 'max:60'],
            'cena_do' => ['nullable', 'integer', 'min:0'],
            'rok_od' => ['nullable', 'integer', 'min:1950', 'max:2100'],
            'sort' => ['nullable', Rule::in(['new', 'cheap', 'expensive'])],
        ]);

        $query = VehicleListing::active()->with('photos');
        if (! empty($filters['typ'])) {
            $query->where('type', $filters['typ']);
        }
        if (! empty($filters['stan'])) {
            $query->where('condition', $filters['stan']);
        }
        if (! empty($filters['marka'])) {
            $query->where('make', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $filters['marka']).'%');
        }
        if (! empty($filters['cena_do'])) {
            $query->where('price', '<=', $filters['cena_do']);
        }
        if (! empty($filters['rok_od'])) {
            $query->where('year', '>=', $filters['rok_od']);
        }
        match ($filters['sort'] ?? 'new') {
            'cheap' => $query->orderBy('price'),
            'expensive' => $query->orderByDesc('price'),
            default => $query->orderByDesc('published_at'),
        };

        return view('vehicles.index', [
            'listings' => $query->paginate(12)->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, VehicleListing $vehicle): View
    {
        $user = $request->user();
        if (! $vehicle->isActive()) {
            abort_unless($user && $user->can('update', $vehicle), 404);
        }

        return view('vehicles.show', ['listing' => $vehicle->load('photos', 'seller')]);
    }

    public function mine(Request $request): View
    {
        return view('vehicles.mine', [
            'listings' => VehicleListing::where('user_id', $request->user()->id)->with('photos')->latest()->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', VehicleListing::class);

        return view('vehicles.form', ['listing' => new VehicleListing([
            'condition' => 'used', 'currency' => 'PLN', 'price_type' => 'netto', 'country' => 'PL',
            'phone' => $request->user()->phone,
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', VehicleListing::class);
        $data = $this->validated($request, 0);

        $listing = new VehicleListing($data);
        $listing->user_id = $request->user()->id;
        $listing->save();
        $this->savePhotos($request, $listing);

        return redirect()->route('vehicles.preview', $listing)->with('status', __('Ogłoszenie zapisane. Sprawdź podgląd i opublikuj.'));
    }

    public function edit(VehicleListing $vehicle): View
    {
        $this->authorize('update', $vehicle);

        return view('vehicles.form', ['listing' => $vehicle->load('photos')]);
    }

    public function update(Request $request, VehicleListing $vehicle): RedirectResponse
    {
        $this->authorize('update', $vehicle);
        $data = $this->validated($request, $vehicle->photos()->count());

        $vehicle->fill($data)->save();

        foreach ((array) $request->input('remove_photos', []) as $id) {
            $photo = $vehicle->photos()->whereKey((int) $id)->first();
            if ($photo) {
                File::delete(public_path($photo->path));
                $photo->delete();
            }
        }
        $this->savePhotos($request, $vehicle);

        return redirect()->route($vehicle->isPublished() ? 'vehicles.show' : 'vehicles.preview', $vehicle)
            ->with('status', __('Zmiany zapisane.'));
    }

    public function preview(VehicleListing $vehicle): View
    {
        $this->authorize('update', $vehicle);

        return view('vehicles.show', ['listing' => $vehicle->load('photos', 'seller'), 'preview' => true]);
    }

    public function publish(VehicleListing $vehicle): RedirectResponse
    {
        $this->authorize('update', $vehicle);
        $vehicle->publish();

        return redirect()->route('vehicles.show', $vehicle)->with('status', __('Ogłoszenie opublikowane. Będzie widoczne przez 30 dni.'));
    }

    public function destroy(Request $request, VehicleListing $vehicle): RedirectResponse
    {
        $this->authorize('delete', $vehicle);
        File::deleteDirectory(public_path('uploads/pojazdy/'.$vehicle->id));
        $vehicle->delete();

        return redirect()->route($request->user()->isAdmin() ? 'admin.index' : 'vehicles.mine')->with('status', __('Ogłoszenie usunięte.'));
    }

    private function validated(Request $request, int $existingPhotos): array
    {
        $max = VehicleListing::MAX_PHOTOS - $existingPhotos + count((array) $request->input('remove_photos', []));

        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(VehicleListing::TYPES))],
            'condition' => ['required', Rule::in(array_keys(VehicleListing::CONDITIONS))],
            'make' => ['required', 'string', 'max:60'],
            'model' => ['required', 'string', 'max:80'],
            'year' => ['nullable', 'integer', 'min:1950', 'max:'.(date('Y') + 1)],
            'mileage' => ['nullable', 'integer', 'min:0', 'max:5000000'],
            'power_hp' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'fuel' => ['nullable', Rule::in(array_keys(VehicleListing::FUELS))],
            'gearbox' => ['nullable', Rule::in(array_keys(VehicleListing::GEARBOXES))],
            'euro' => ['nullable', Rule::in(VehicleListing::EURO)],
            'price' => ['required', 'integer', 'min:1', 'max:100000000'],
            'currency' => ['required', Rule::in(VehicleListing::CURRENCIES)],
            'price_type' => ['required', Rule::in(['netto', 'brutto'])],
            'vat_invoice' => ['boolean'],
            'city' => ['required', 'string', 'max:80'],
            'country' => ['required', 'string', 'size:2'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9 ]{9,20}$/'],
            'photos' => ['nullable', 'array', 'max:'.max(0, $max)],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'remove_photos' => ['nullable', 'array'],
            'remove_photos.*' => ['integer'],
        ]);
        $data['vat_invoice'] = $request->boolean('vat_invoice');
        $data['country'] = strtoupper($data['country']);
        unset($data['photos'], $data['remove_photos']);

        return $data;
    }

    private function savePhotos(Request $request, VehicleListing $listing): void
    {
        $dir = 'uploads/pojazdy/'.$listing->id;
        $position = (int) $listing->photos()->max('position');
        foreach ($request->file('photos', []) as $file) {
            if (! $file->isValid()) {
                continue;
            }
            $name = Str::random(24).'.'.$file->guessExtension();
            $file->move(public_path($dir), $name);
            $listing->photos()->create(['path' => $dir.'/'.$name, 'position' => ++$position]);
        }
    }
}
