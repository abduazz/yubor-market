# Yubor Market — frontend (Laravel + Blade + Tailwind)

Figma: **Yubor-DEV → "MVP · Market (minimal)"** dizayni asosida mobil frontend.
Backend yo'q — barcha ma'lumotlar statik (`config/catalog.php`), holat brauzer `localStorage`'ida saqlanadi.

## Tez ishga tushirish

Talablar: **PHP 8.3+** (Composer va Node.js shart emas — `vendor/` va tayyor `public/build/` arxivda bor).

```bash
cp .env.example .env
php artisan key:generate
php artisan serve
```

So'ng brauzerda: http://localhost:8000 (telefon o'lchamida ko'rish uchun DevTools → mobil rejim).

## Dizaynni o'zgartirib ishlash (ixtiyoriy)

CSS/JS o'zgartirilsa, qayta yig'ish kerak (Node.js 20+):

```bash
npm install
npm run dev     # ishlab chiqish (avtomatik yangilanadi)
npm run build   # production uchun public/build/ ni yangilash
```

## Sahifalar

| Manzil | Ekran |
|---|---|
| `/` | Bosh sahifa: banner karuseli, kategoriyalar, faol buyurtma kartasi, bo'limlar ("Ko‘proq ko‘rish") |
| `/category/{slug}` | Kategoriya (telefonlar, maishiy-texnika, bolalar-uchun) |
| `/product/{slug}` | Tovar: galereya (surish + to'liq ekran), ulashish, xususiyatlar |
| `/product/{slug}/specs` | Barcha xususiyatlar |
| `/checkout` | Buyurtmani rasmiylashtirish: validatsiya, +7/+998 maska, viloyat/tuman bottom sheet |
| `/checkout/payment` | Sovg‘a va to‘lov |
| `/checkout/processing` | To‘lov tekshirilmoqda (3.5 s dan keyin buyurtmaga o'tadi) |
| `/orders/{id}` | Buyurtma tafsiloti |

### Buyurtma statuslari (demo)

Backend yo'qligi sababli status manzil orqali beriladi:

```
/orders/482913?status=paid        # To‘landi
/orders/482913?status=packing     # Tayyorlanmoqda
/orders/482913?status=shipping    # Kuryerga topshirildi (standart)
/orders/482913?status=delivered   # Buyurtma yetkazildi — bosh sahifadagi karta yo'qoladi
/orders/482913?status=cancelled   # Bekor qilindi
```

## Tuzilma

```
config/catalog.php                 — barcha statik ma'lumotlar: tovarlar, bannerlar, viloyat/tumanlar,
                                     buyurtma statuslari va ranglari (backend ulanganda shu yer API'ga almashadi)
routes/web.php                     — sahifa marshrutlari
resources/views/pages/             — sahifalar (home, category, product, specs, checkout, payment, processing, order)
resources/views/components/        — qayta ishlatiladigan komponentlar (product-card, field, bottom-sheet,
                                     image-viewer, share-sheet, banner-carousel, step-icon, ...)
resources/css/app.css              — Tailwind v4 + Figma tokenlari (ranglar, shriftlar)
resources/js/app.js                — interaktivlik (galereya, karusel, validatsiya, bottom sheet, ulashish, statuslar)
public/img/                        — Figma'dan yuklab olingan rasmlar va ikonkalar
```
