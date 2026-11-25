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
            'locales' => Banner::LOCALES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $localeKeys = array_keys(Banner::LOCALES);

        $data = $request->validate([
            'image_upload' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
            'images_upload' => ['nullable', 'array'],
            'images_upload.*' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
            'url' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $imagesByLocale = [];
        foreach ($localeKeys as $loc) {
            if ($request->hasFile("images_upload.{$loc}")) {
                $imagesByLocale[$loc] = $this->handleUpload($request->file("images_upload.{$loc}"), $loc);
            }
        }

        if (empty($imagesByLocale) && $request->hasFile('image_upload')) {
            $imagesByLocale['uz'] = $this->handleUpload($request->file('image_upload'), 'uz');
        }

        if (empty($imagesByLocale)) {
            return back()->withErrors([
                'images_upload' => __('admin.banners.at_least_one_image'),
            ])->withInput();
        }

        $primaryImage = $imagesByLocale['uz'] ?? reset($imagesByLocale);

        Banner::query()->create([
            'title' => '',
            'subtitle' => '',
            'subtitle_color' => null,
            'gradient' => '',
            'image' => $primaryImage,
            'images_by_locale' => $imagesByLocale,
            'image_class' => 'full',
            'url' => trim((string) ($data['url'] ?? '')) ?: '/',
            'sort_order' => (int) (Banner::query()->max('sort_order') + 1),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', __('admin.flash.banner_created'));
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $localeKeys = array_keys(Banner::LOCALES);

        $data = $request->validate([
            'image_upload' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
            'images_upload' => ['nullable', 'array'],
            'images_upload.*' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
            'url' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $imagesByLocale = is_array($banner->images_by_locale) ? $banner->images_by_locale : [];
        if (empty($imagesByLocale) && ! empty($banner->image)) {
            $imagesByLocale['uz'] = $banner->image;
        }

        $hasNewUpload = false;
        foreach ($localeKeys as $loc) {
            if ($request->hasFile("images_upload.{$loc}")) {
                $imagesByLocale[$loc] = $this->handleUpload($request->file("images_upload.{$loc}"), $loc);
                $hasNewUpload = true;
            }
        }

        if ($request->hasFile('image_upload')) {
            $imagesByLocale['uz'] = $this->handleUpload($request->file('image_upload'), 'uz');
            $hasNewUpload = true;
        }

        $payload = [
            'url' => trim((string) ($data['url'] ?? '')) ?: '/',
            'images_by_locale' => $imagesByLocale,
        ];

        if ($request->has('is_active')) {
            $payload['is_active'] = $request->boolean('is_active');
        }

        if ($hasNewUpload) {
            $payload['image'] = $imagesByLocale['uz'] ?? reset($imagesByLocale) ?: $banner->image;
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
        $banner->update(['is_active' => ! $banner->is_active]);

        return back()->with('success', __('admin.flash.banner_toggled'));
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $banner->delete();

        return back()->with('success', __('admin.flash.banner_deleted'));
    }

    protected function handleUpload(UploadedFile $file, string $locale = 'uz'): string
    {
        $uploadDir = public_path('img/uploads/banners');
        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'banner-'.$locale.'-'.time().'-'.Str::random(6).'.'.$file->getClientOriginalExtension();
        $file->move($uploadDir, $filename);

        return 'img/uploads/banners/'.$filename;
    }
}
