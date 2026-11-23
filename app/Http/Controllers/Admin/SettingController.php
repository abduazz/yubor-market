<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\CatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(CatalogService $catalog): View
    {
        return view('admin.settings.index', [
            'settings' => $catalog->settings(),
            'admin' => Auth::user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rate_card_uzs' => ['required', 'string', 'max:30'],
            'rate_sbp_uzs' => ['required', 'string', 'max:30'],
            'gift_wrap_fee_rub' => ['required', 'integer', 'min:0'],
            'oversize_fee_rub' => ['required', 'integer', 'min:0'],
            'delivery_provider_text' => ['required', 'string', 'max:160'],
            'delivery_badge_text' => ['required', 'string', 'max:160'],
            'support_chat_url' => ['required', 'string', 'max:255'],
            'marketplace_phone' => ['nullable', 'string', 'max:60'],
        ]);

        foreach ($data as $key => $value) {
            Setting::setValue($key, $value);
        }

        return back()->with('success', __('admin.flash.settings_updated'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email,'.$user->id],
            'new_password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (! empty($data['new_password'])) {
            $payload['password'] = Hash::make($data['new_password']);
        }

        $user->update($payload);

        return back()->with('success', __('admin.flash.profile_updated'));
    }
}
