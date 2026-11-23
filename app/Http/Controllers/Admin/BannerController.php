<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        $banners = Banner::query()->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.banners.index', [
            'banners' => $banners,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'image_upload' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
            'url' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $imagePath = $this->handleUpload($request->file('image_upload'));

        Banner::query()->create([
            'title' => '',
            'subtitle' => '',
            'subtitle_color' => null,
            'gradient' => '',
            'image' => $imagePath,
            'image_class' => 'full',
            'url' => trim((string) ($data['url'] ?? '')) ?: '/',
            'sort_order' => (int) (Banner::query()->max('sort_order') + 1),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', __('admin.flash.banner_created'));
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $data = $request->validate([
            'image_upload' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
            'url' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'url' => trim((string) ($data['url'] ?? '')) ?: '/',
        ];

        if ($request->has('is_active')) {
            $payload['is_active'] = $request->boolean('is_active');
        }

        if ($request->hasFile('image_upload')) {
            $payload['image'] = $this->handleUpload($request->file('image_upload'));
            $payload['image_class'] = 'full';
            $payload['title'] = '';
            $payload['subtitle'] = '';
            $payload['gradient'] = '';
        }

        $banner->update($payload);

        return back()->with('success', __('admin.flash.banner_updated'));
    }

    public function toggle(Banner $banner): RedirectResponse
    {
        $banner->update(['is_active' => !$banner->is_active]);

        return back()->with('success', __('admin.flash.banner_toggled'));
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $banner->delete();

        return back()->with('success', __('admin.flash.banner_deleted'));
    }

    protected function handleUpload(UploadedFile $file): string
    {
        $uploadDir = public_path('img/uploads/banners');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'banner-' . time() . '-' . Str::random(6) . '.' . $file->getClientOriginalExtension();
        $file->move($uploadDir, $filename);

        return 'img/uploads/banners/' . $filename;
    }
}
