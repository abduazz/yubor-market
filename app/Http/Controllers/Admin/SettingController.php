<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\CatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(CatalogService $catalog): View
    {
        return view('admin.settings.index', [
            'settings' => $catalog->settings(),
            'receivingCards' => $catalog->receivingCards(),
        ]);
    }

    public function updateCards(Request $request): RedirectResponse
    {
        $rawCards = $request->input('cards', []);
        $normalizedCards = [];
        $indexMap = [];

        if (is_array($rawCards)) {
            foreach ($rawCards as $origIdx => $card) {
                $digits = preg_replace('/\D/', '', (string) ($card['number'] ?? ''));
                $holder = trim((string) ($card['holder'] ?? ''));

                if ($digits === '' && $holder === '') {
                    continue;
                }

                $indexMap[] = $origIdx;
                $normalizedCards[] = [
                    'digits' => $digits,
                    'holder' => mb_strtoupper($holder),
                ];
            }
        }

        $validator = Validator::make([
            'cards' => $normalizedCards,
        ], [
            'cards' => ['nullable', 'array'],
            'cards.*.digits' => [
                'required',
                'string',
                'distinct',
                'regex:/^(8600|5614|5440|6262|9860)\d{12}$/',
            ],
            'cards.*.holder' => ['nullable', 'string', 'max:80'],
        ], [
            'cards.*.digits.required' => __('admin.settings.card_required'),
            'cards.*.digits.distinct' => __('admin.settings.card_duplicate'),
            'cards.*.digits.regex' => __('admin.settings.card_invalid'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $remapped = new MessageBag;
            foreach ($errors->messages() as $key => $messages) {
                if (preg_match('/^cards\.(\d+)\.digits$/', $key, $m)) {
                    $orig = $indexMap[(int) $m[1]] ?? $m[1];
                    $remapped->merge(["cards.{$orig}.number" => $messages]);
                } else {
                    $remapped->merge([$key => $messages]);
                }
            }

            return back()->withErrors($remapped)->withInput();
        }

        $savedCards = [];
        foreach ($normalizedCards as $card) {
            $digits = $card['digits'];
            $savedCards[] = [
                'number' => trim(chunk_split($digits, 4, ' ')),
                'holder' => $card['holder'],
                'type' => str_starts_with($digits, '9860') ? 'humo' : 'uzcard',
            ];
        }

        Setting::setValue(
            'receiving_cards',
            json_encode($savedCards, JSON_UNESCAPED_UNICODE),
            'cards',
            'Qabul qiluvchi kartalar'
        );

        return back()->with('success', __('admin.flash.cards_updated'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delivery_provider_text' => ['required', 'string', 'max:160'],
            'delivery_badge_text' => ['required', 'string', 'max:160'],
            'support_chat_url' => ['required', 'string', 'max:255'],
            'marketplace_phone' => ['nullable', 'string', 'max:60'],
        ]);

        foreach ([
            'delivery_provider_text',
            'delivery_badge_text',
            'support_chat_url',
            'marketplace_phone',
        ] as $key) {
            Setting::setValue($key, trim((string) ($validated[$key] ?? '')));
        }

        return back()->with('success', __('admin.flash.settings_updated'));
    }
}
