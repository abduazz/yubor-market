<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegionController extends Controller
{
    public function index(): View
    {
        $regions = Region::query()
            ->with(['districts' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.regions.index', [
            'regions' => $regions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:regions,name'],
            'delivery_days' => ['required', 'string', 'max:60'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        Region::query()->create([
            'name' => $data['name'],
            'delivery_days' => $data['delivery_days'],
            'sort_order' => (int) ($data['sort_order'] ?? (Region::query()->max('sort_order') + 1)),
            'is_active' => true,
        ]);

        return back()->with('success', __('admin.flash.region_created', ['name' => $data['name']]));
    }

    public function update(Request $request, Region $region): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('regions', 'name')->ignore($region->id)],
            'delivery_days' => ['required', 'string', 'max:60'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $region->update([
            'name' => $data['name'],
            'delivery_days' => $data['delivery_days'],
            'sort_order' => (int) ($data['sort_order'] ?? $region->sort_order),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', __('admin.flash.region_updated', ['name' => $region->name]));
    }

    public function destroy(Region $region): RedirectResponse
    {
        $name = $region->name;
        $region->delete();

        return back()->with('success', __('admin.flash.region_deleted', ['name' => $name]));
    }

    public function storeDistrict(Request $request, Region $region): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        District::query()->updateOrCreate(
            ['region_id' => $region->id, 'name' => trim($data['name'])],
            [
                'sort_order' => $region->districts()->max('sort_order') + 1,
                'is_active' => true,
            ]
        );

        return back()->with('success', __('admin.flash.district_created', ['district' => $data['name'], 'region' => $region->name]));
    }

    public function destroyDistrict(District $district): RedirectResponse
    {
        $name = $district->name;
        $district->delete();

        return back()->with('success', __('admin.flash.district_deleted', ['name' => $name]));
    }
}
