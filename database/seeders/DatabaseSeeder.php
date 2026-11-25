<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Category;
use App\Models\District;
use App\Models\Order;
use App\Models\Product;
use App\Models\Region;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@yubor.uz'],
            [
                'name' => 'Yubor Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        $categoryImages = [
            'telefonlar' => 'img/cat-phones.png',
            'maishiy-texnika' => 'img/cat-appl2.png',
            'bolalar-uchun' => 'img/teddy.png',
        ];

        $categoryMap = [];
        foreach (config('catalog.categories', []) as $idx => $catData) {
            $category = Category::query()->updateOrCreate(
                ['slug' => $catData['slug']],
                [
                    'title' => $catData['title'],
                    'section' => $catData['section'] ?? $catData['title'],
                    'image' => $categoryImages[$catData['slug']] ?? null,
                    'demo_count' => $catData['demo_count'] ?? 4,
                    'sort_order' => $idx + 1,
                    'is_active' => true,
                ]
            );
            $categoryMap[$category->slug] = $category;
        }

        foreach (config('catalog.banners', []) as $idx => $bannerData) {
            Banner::query()->updateOrCreate(
                ['title' => $bannerData['title'], 'url' => $bannerData['url']],
                [
                    'subtitle' => $bannerData['subtitle'],
                    'subtitle_color' => $bannerData['subtitle_color'] ?? '#d0e7fd',
                    'gradient' => $bannerData['gradient'],
                    'image' => $bannerData['image'],
                    'image_class' => $bannerData['image_class'] ?? 'top-[10px] right-[10px] h-[155px] w-[145px]',
                    'sort_order' => $idx + 1,
                    'is_active' => true,
                ]
            );
        }

        $productMap = [];
        foreach (config('catalog.products', []) as $idx => $prodData) {
            $priceRub = (int) preg_replace('/\D/', '', (string) $prodData['price']);
            $category = $categoryMap[$prodData['category']] ?? null;

            $product = Product::query()->updateOrCreate(
                ['slug' => $prodData['slug']],
                [
                    'category_id' => $category?->id,
                    'title' => $prodData['title'],
                    'full_title' => $prodData['full_title'] ?? $prodData['title'],
                    'brand' => $prodData['brand'] ?? 'Original',
                    'price_rub' => $priceRub,
                    'old_price_rub' => match ($prodData['slug']) {
                        'galaxy-a55' => 37990,
                        'artel-muzlatgich-345' => 45990,
                        'ayiqcha-80' => 2990,
                        default => null,
                    },
                    'stock' => match ($prodData['slug']) {
                        'galaxy-a55' => 24,
                        'iphone-15' => 12,
                        'artel-muzlatgich-345' => 8,
                        'multivarka-artel-5l' => 35,
                        'ayiqcha-80' => 60,
                        'ayiqcha-120' => 18,
                        default => 25,
                    },
                    'image' => $prodData['image'],
                    'gallery' => $prodData['gallery'] ?? $prodData['image'],
                    'images' => $prodData['images'] ?? [$prodData['gallery'] ?? $prodData['image']],
                    'specs' => $prodData['specs'] ?? config('catalog.specs'),
                    'spec_groups' => $prodData['spec_groups'] ?? null,
                    'description' => $prodData['description'] ?? 'Galaxy A55 — metall korpus, 120 Hz yorqin ekran va kechasi ham aniq suratga oladigan 50 MP kamera. Ota-onangiz uchun qulay: katta shrift rejimi va 2 kunlik batareya. Qurilma IP67 standartida suv va changdan himoyalangan, 4 yil davomida Android yangilanishlari kafolatlanadi.',
                    'warranty_text' => 'Rasmiy kafolat 12 oy · Original mahsulot',
                    'sort_order' => $idx + 1,
                    'is_active' => true,
                ]
            );
            $productMap[$product->slug] = $product;
        }

        $districtsConfig = config('catalog.districts', []);
        foreach (config('catalog.regions', []) as $rIdx => $regionName) {
            $region = Region::query()->updateOrCreate(
                ['name' => $regionName],
                [
                    'delivery_days' => in_array($regionName, ['Toshkent shahri', 'Toshkent viloyati']) ? '1–2 kun' : '2–3 kun',
                    'sort_order' => $rIdx + 1,
                    'is_active' => true,
                ]
            );

            foreach ($districtsConfig[$regionName] ?? [] as $dIdx => $districtName) {
                District::query()->updateOrCreate(
                    ['region_id' => $region->id, 'name' => $districtName],
                    [
                        'sort_order' => $dIdx + 1,
                        'is_active' => true,
                    ]
                );
            }
        }

        $defaultSettings = [
            ['key' => 'receiving_cards', 'value' => '[]', 'group' => 'cards', 'label' => 'Qabul qiluvchi kartalar (Uzcard / Humo)'],
            ['key' => 'delivery_provider_text', 'value' => '2-4 kunda EMU po‘chta orqali', 'group' => 'delivery', 'label' => 'Yetkazish xizmati matni'],
            ['key' => 'delivery_badge_text', 'value' => 'Yetkazish bepul · 2–3 kun (EMU)', 'group' => 'delivery', 'label' => 'Checkout yetkazish xabari'],
            ['key' => 'support_chat_url', 'value' => 'https://t.me/yubormarket_support', 'group' => 'general', 'label' => 'Yordam chat havolasi (Telegram)'],
            ['key' => 'marketplace_phone', 'value' => '+998 71 200-00-00', 'group' => 'general', 'label' => 'Call-markaz raqami'],
        ];

        foreach ($defaultSettings as $s) {
            Setting::query()->updateOrCreate(['key' => $s['key']], $s);
        }

        // Canonical Order #482913 from Figma / config/catalog.php
        $order482913 = Order::query()->updateOrCreate(
            ['order_number' => '482913'],
            [
                'status' => 'shipping',
                'sender_name' => 'Rustam Karimov',
                'sender_phone' => '+7 926 450 - 18 - 90',
                'recipient_name' => 'Mohira Karimova',
                'recipient_phone' => '+998 90 123-45-67',
                'recipient_phone_2' => '+998 91 654-32-10',
                'region' => 'Farg‘ona',
                'district' => 'Marg‘ilon shahri',
                'address' => 'Navro‘z mahallasi, Bog‘ ko‘chasi 12-uy',
                'landmark' => '15-maktab yonida',
                'wish' => 'Onajon, tug‘ilgan kuningiz bilan! Sog‘ bo‘ling',
                'payment_method' => 'card',
                'exchange_rate' => 158.000,
                'card_masked' => 'MIR •4417',
                'items_total_rub' => 89960,
                'discount_rub' => 13000,
                'oversize_fee_rub' => 990,
                'gift_wrap_fee_rub' => 190,
                'delivery_fee_rub' => 0,
                'total_rub' => 78140,
                'courier_name' => 'Bekzod',
                'courier_phone' => '+998 93 777-11-22',
                'estimated_delivery_text' => '20 noyabr',
                'admin_note' => 'Asosiy demo buyurtma (Figma). Kuryer Bekzodga topshirilgan.',
            ]
        );

        $order482913->items()->delete();
        $order482913->items()->createMany([
            [
                'product_id' => $productMap['galaxy-a55']?->id,
                'title' => 'Galaxy A55 5G, 8/256 GB',
                'quantity' => 1,
                'qty_label' => '1 dona',
                'unit_price_rub' => 32990,
                'total_price_rub' => 32990,
                'image' => 'img/gallery-a55.png',
            ],
            [
                'product_id' => $productMap['artel-muzlatgich-345']?->id,
                'title' => 'Muzlatgich Artel 345 L',
                'quantity' => 1,
                'qty_label' => '1 dona',
                'unit_price_rub' => 38990,
                'total_price_rub' => 38990,
                'image' => 'img/thumb-2.png',
            ],
            [
                'product_id' => $productMap['ayiqcha-80']?->id,
                'title' => 'Yumshoq ayiqcha, 80 sm',
                'quantity' => 2,
                'qty_label' => '2 dona',
                'unit_price_rub' => 2490,
                'total_price_rub' => 4980,
                'image' => 'img/thumb-3.png',
            ],
        ]);

        // Additional realistic orders for Admin Panel analytics
        $extraOrders = [
            [
                'order_number' => '482914',
                'status' => 'paid',
                'sender_name' => 'Jasur Rahimov',
                'sender_phone' => '+7 903 811 - 24 - 55',
                'recipient_name' => 'Dilnoza Rahimova',
                'recipient_phone' => '+998 93 540-19-88',
                'region' => 'Samarqand',
                'district' => 'Samarqand shahri',
                'address' => 'Registon ko‘chasi, 44-uy, 12-xonadon',
                'landmark' => 'Universitet xiyoboni ro‘parasida',
                'wish' => 'Aziz singlim, yangi telefoning muborak bo‘lsin!',
                'payment_method' => 'sbp',
                'exchange_rate' => 156.250,
                'card_masked' => 'SBP •8821',
                'items_total_rub' => 69990,
                'discount_rub' => 0,
                'oversize_fee_rub' => 0,
                'gift_wrap_fee_rub' => 190,
                'delivery_fee_rub' => 0,
                'total_rub' => 70180,
                'estimated_delivery_text' => '25 noyabr',
                'items' => [
                    [
                        'product_id' => $productMap['iphone-15']?->id,
                        'title' => 'Smartfon iPhone 15, 128 GB, pushti',
                        'quantity' => 1,
                        'qty_label' => '1 dona',
                        'unit_price_rub' => 69990,
                        'total_price_rub' => 69990,
                        'image' => 'img/phone-pink.png',
                    ],
                ],
            ],
            [
                'order_number' => '482910',
                'status' => 'packing',
                'sender_name' => 'Sardor Usmonov',
                'sender_phone' => '+7 916 332 - 09 - 14',
                'recipient_name' => 'Xolida Usmonova',
                'recipient_phone' => '+998 97 710-44-55',
                'region' => 'Toshkent shahri',
                'district' => 'Yunusobod',
                'address' => '11-mavze, 28-uy, 54-xonadon',
                'landmark' => 'Mega Planet savdo markazi yaqinida',
                'wish' => 'Oshxonangizda doim shirin taomlar pishsin!',
                'payment_method' => 'card',
                'exchange_rate' => 158.000,
                'card_masked' => 'MIR •1093',
                'items_total_rub' => 10480,
                'discount_rub' => 0,
                'oversize_fee_rub' => 0,
                'gift_wrap_fee_rub' => 190,
                'delivery_fee_rub' => 0,
                'total_rub' => 10670,
                'estimated_delivery_text' => '24 noyabr',
                'items' => [
                    [
                        'product_id' => $productMap['multivarka-artel-5l']?->id,
                        'title' => 'Multivarka-bosimli qozon Artel, 5 L',
                        'quantity' => 1,
                        'qty_label' => '1 dona',
                        'unit_price_rub' => 6490,
                        'total_price_rub' => 6490,
                        'image' => 'img/cat-appl1.png',
                    ],
                    [
                        'product_id' => $productMap['ayiqcha-120']?->id,
                        'title' => 'Yumshoq ayiqcha, 120 sm, katta',
                        'quantity' => 1,
                        'qty_label' => '1 dona',
                        'unit_price_rub' => 3990,
                        'total_price_rub' => 3990,
                        'image' => 'img/thumb-3.png',
                    ],
                ],
            ],
            [
                'order_number' => '482895',
                'status' => 'delivered',
                'sender_name' => 'Otabek Nurmatov',
                'sender_phone' => '+7 999 120 - 77 - 01',
                'recipient_name' => 'Zulfiya Nurmatova',
                'recipient_phone' => '+998 94 205-88-11',
                'region' => 'Andijon',
                'district' => 'Andijon shahri',
                'address' => 'Bobur shoh ko‘chasi, 75-uy',
                'landmark' => 'Markaziy bog‘ yonida',
                'wish' => 'Oilamizga sog‘lik va xursandchilik tilayman!',
                'payment_method' => 'card',
                'exchange_rate' => 158.000,
                'card_masked' => 'MIR •9042',
                'items_total_rub' => 32990,
                'discount_rub' => 0,
                'oversize_fee_rub' => 0,
                'gift_wrap_fee_rub' => 0,
                'delivery_fee_rub' => 0,
                'total_rub' => 32990,
                'courier_name' => 'Sherzod',
                'estimated_delivery_text' => '20 noyabr',
                'delivered_at' => now()->subDays(2),
                'items' => [
                    [
                        'product_id' => $productMap['galaxy-a55']?->id,
                        'title' => 'Smartfon Galaxy A55 5G, 8/256 GB',
                        'quantity' => 1,
                        'qty_label' => '1 dona',
                        'unit_price_rub' => 32990,
                        'total_price_rub' => 32990,
                        'image' => 'img/gallery-a55.png',
                    ],
                ],
            ],
        ];

        foreach ($extraOrders as $orderData) {
            $items = $orderData['items'];
            unset($orderData['items']);
            $ord = Order::query()->updateOrCreate(
                ['order_number' => $orderData['order_number']],
                $orderData
            );
            $ord->items()->delete();
            $ord->items()->createMany($items);
        }
    }
}
