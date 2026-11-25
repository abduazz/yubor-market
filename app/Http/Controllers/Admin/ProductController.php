<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()->with('category');

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('full_title', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->query('status') === 'active');
        }

        $products = $query->orderBy('sort_order')->orderByDesc('id')->paginate(15)->withQueryString();
        $categories = Category::query()->orderBy('sort_order')->get();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product([
                'brand' => 'Samsung',
                'stock' => 25,
                'sort_order' => Product::query()->max('sort_order') + 1,
                'is_active' => true,
                'image' => 'img/phone-blue.png',
                'gallery' => null,
                'images' => [],
                'warranty_text' => 'Rasmiy kafolat 12 oy · Original mahsulot',
                'specs' => config('catalog.specs'),
            ]),
            'categories' => Category::query()->orderBy('sort_order')->get(),
            'availableImages' => $this->availablePresetImages(),
            'locales' => Product::LOCALES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request);
        $product = Product::query()->create($data);

        return redirect()->route('admin.products.index')
            ->with('success', __('admin.flash.product_created', ['title' => $product->title]));
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::query()->orderBy('sort_order')->get(),
            'availableImages' => $this->availablePresetImages(),
            'locales' => Product::LOCALES,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validateProduct($request, $product);
        $product->update($data);

        return redirect()->route('admin.products.index')
            ->with('success', __('admin.flash.product_updated', ['title' => $product->title]));
    }

    public function toggle(Product $product): RedirectResponse
    {
        $product->update(['is_active' => ! $product->is_active]);
        $msgKey = $product->is_active ? 'admin.flash.product_toggled_on' : 'admin.flash.product_toggled_off';

        return back()->with('success', __($msgKey, ['title' => $product->title]));
    }

    public function destroy(Product $product): RedirectResponse
    {
        $title = $product->title;
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', __('admin.flash.product_deleted', ['title' => $title]));
    }

    protected function validateProduct(Request $request, ?Product $product = null): array
    {
        $localeKeys = array_keys(Product::LOCALES);
        $rawTranslations = $request->input('translations', []);

        // Resolve primary title from UZ or first non-empty language or legacy title field
        $baseTitle = trim((string) ($rawTranslations['uz']['title'] ?? ''));
        if ($baseTitle === '') {
            foreach ($localeKeys as $loc) {
                $candidate = trim((string) ($rawTranslations[$loc]['title'] ?? ''));
                if ($candidate !== '') {
                    $baseTitle = $candidate;
                    break;
                }
            }
        }
        if ($baseTitle === '') {
            $baseTitle = trim((string) $request->input('title', ''));
        }

        if ($baseTitle !== '') {
            $request->merge(['title' => $baseTitle]);
        }

        if (! $request->filled('slug') && $baseTitle !== '') {
            $request->merge(['slug' => Str::slug($baseTitle)]);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'translations' => ['nullable', 'array'],
            'translations.*.title' => ['nullable', 'string', 'max:160'],
            'translations.*.full_title' => ['nullable', 'string', 'max:200'],
            'translations.*.warranty_text' => ['nullable', 'string', 'max:200'],
            'translations.*.description' => ['nullable', 'string', 'max:3000'],
            'translations.*.spec_keys' => ['nullable', 'array'],
            'translations.*.spec_values' => ['nullable', 'array'],
            'slug' => [
                'required',
                'string',
                'max:160',
                Rule::unique('products', 'slug')->ignore($product?->id),
            ],
            'category_id' => ['required', 'exists:categories,id'],
            'brand' => ['required', 'string', 'max:80'],
            'price_rub' => ['required', 'integer', 'min:1'],
            'old_price_rub' => ['nullable', 'integer', 'min:1'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'string', 'max:255'],
            'image_upload' => ['nullable', 'image', 'max:5120'],
            'gallery' => ['nullable', 'string', 'max:255'],
            'gallery_upload' => ['nullable', 'image', 'max:5120'],
            'gallery_uploads' => ['nullable', 'array'],
            'gallery_uploads.*' => ['image', 'max:5120'],
            'images_text' => ['nullable', 'string'],
            'spec_groups_json' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $translations = [];
        foreach ($localeKeys as $loc) {
            $locInput = is_array($rawTranslations[$loc] ?? null) ? $rawTranslations[$loc] : [];
            $locSpecs = [];
            $sKeys = $locInput['spec_keys'] ?? [];
            $sVals = $locInput['spec_values'] ?? [];
            if (is_array($sKeys)) {
                foreach ($sKeys as $idx => $k) {
                    $keyStr = trim((string) $k);
                    $valStr = trim((string) ($sVals[$idx] ?? ''));
                    if ($keyStr !== '' && $valStr !== '') {
                        $locSpecs[$keyStr] = $valStr;
                    }
                }
            }

            $translations[$loc] = [
                'title' => trim((string) ($locInput['title'] ?? '')),
                'full_title' => trim((string) ($locInput['full_title'] ?? '')),
                'warranty_text' => trim((string) ($locInput['warranty_text'] ?? '')),
                'description' => trim((string) ($locInput['description'] ?? '')),
                'specs' => $locSpecs,
            ];
        }

        // Determine primary locale record for top-level columns (prefer uz, then first non-empty)
        $primaryLoc = $translations['uz'];
        if ($primaryLoc['title'] === '') {
            foreach ($localeKeys as $loc) {
                if ($translations[$loc]['title'] !== '') {
                    $primaryLoc = $translations[$loc];
                    break;
                }
            }
        }

        $uploadedNewCardImage = false;
        $imagePath = $validated['image'] ?? $product?->image ?? 'img/phone-blue.png';
        if ($request->hasFile('image_upload')) {
            $imagePath = $this->handleUpload($request->file('image_upload'));
            $uploadedNewCardImage = true;
        }

        $uploadedGalleryImages = [];
        if ($request->hasFile('gallery_upload')) {
            $uploadedGalleryImages[] = $this->handleUpload($request->file('gallery_upload'));
        }
        if ($request->hasFile('gallery_uploads')) {
            foreach ($request->file('gallery_uploads') as $gFile) {
                $uploadedGalleryImages[] = $this->handleUpload($gFile);
            }
        }

        if (! empty($uploadedGalleryImages)) {
            $galleryPath = $uploadedGalleryImages[0];
        } elseif ($uploadedNewCardImage) {
            $galleryPath = $imagePath;
        } else {
            $galleryPath = ! empty($validated['gallery']) ? $validated['gallery'] : $imagePath;
        }

        $imagesList = [];
        if (! empty($uploadedGalleryImages)) {
            $imagesList = $uploadedGalleryImages;
        } elseif (! empty($validated['images_text'])) {
            $lines = preg_split('/[\r\n,]+/', $validated['images_text']) ?: [];
            foreach ($lines as $line) {
                $clean = ltrim(trim($line), '/');
                if ($clean !== '') {
                    $imagesList[] = $clean;
                }
            }
            if ($uploadedNewCardImage && $imagesList === ['img/gallery-a55.png']) {
                $imagesList = [$imagePath];
            }
        }
        if (empty($imagesList)) {
            $imagesList = [$galleryPath];
        }

        $specGroups = $product?->spec_groups;
        if ($request->filled('spec_groups_json')) {
            $decoded = json_decode((string) $request->input('spec_groups_json'), true);
            if (is_array($decoded)) {
                $specGroups = $decoded;
            }
        }

        return [
            'category_id' => (int) $validated['category_id'],
            'slug' => Str::slug($validated['slug']),
            'title' => $primaryLoc['title'] ?: $baseTitle,
            'full_title' => $primaryLoc['full_title'] ?: ($primaryLoc['title'] ?: $baseTitle),
            'brand' => $validated['brand'],
            'price_rub' => (int) $validated['price_rub'],
            'old_price_rub' => ! empty($validated['old_price_rub']) ? (int) $validated['old_price_rub'] : null,
            'stock' => (int) $validated['stock'],
            'image' => ltrim($imagePath, '/'),
            'gallery' => ltrim($galleryPath, '/'),
            'images' => $imagesList,
            'specs' => ! empty($primaryLoc['specs']) ? $primaryLoc['specs'] : null,
            'spec_groups' => $specGroups,
            'translations' => $translations,
            'description' => $primaryLoc['description'] ?: null,
            'warranty_text' => $primaryLoc['warranty_text'] ?: 'Rasmiy kafolat 12 oy · Original mahsulot',
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    protected function handleUpload(\Illuminate\Http\UploadedFile $file): string
    {
        $uploadDir = public_path('img/uploads');
        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = time().'-'.Str::random(6).'.'.$file->getClientOriginalExtension();
        $file->move($uploadDir, $filename);

        return 'img/uploads/'.$filename;
    }

    protected function availablePresetImages(): array
    {
        if (app()->getLocale() === 'uz') {
            return [
                'img/phone-blue.png' => 'Samsung Galaxy A55 (Kartochka)',
                'img/gallery-a55.png' => 'Samsung Galaxy A55 (Galereya 1)',
                'img/a55-2.jpg' => 'Smartfon — Rasm 2',
                'img/a55-3.jpg' => 'Smartfon — Rasm 3',
                'img/phone-pink.png' => 'iPhone 15 Pushti',
                'img/cat-appl1.png' => 'Multivarka Artel',
                'img/cat-appl2.png' => 'Muzlatgich Artel',
                'img/thumb-2.png' => 'Muzlatgich (Miniatyura)',
                'img/teddy.png' => 'Yumshoq ayiqcha (Kartochka)',
                'img/thumb-3.png' => 'Yumshoq ayiqcha (Galereya)',
                'img/cat-phones.png' => 'Telefonlar kategoriyasi',
            ];
        }

        return [
            'img/phone-blue.png' => 'Samsung Galaxy A55 (Карточка)',
            'img/gallery-a55.png' => 'Samsung Galaxy A55 (Галерея 1)',
            'img/a55-2.jpg' => 'Смартфон — Фото 2',
            'img/a55-3.jpg' => 'Смартфон — Фото 3',
            'img/phone-pink.png' => 'iPhone 15 Pink',
            'img/cat-appl1.png' => 'Мультиварка Artel',
            'img/cat-appl2.png' => 'Холодильник Artel',
            'img/thumb-2.png' => 'Холодильник (Миниатюра)',
            'img/teddy.png' => 'Плюшевый мишка (Карточка)',
            'img/thumb-3.png' => 'Плюшевый мишка (Галерея)',
            'img/cat-phones.png' => 'Категория Телефоны',
        ];
    }
}
