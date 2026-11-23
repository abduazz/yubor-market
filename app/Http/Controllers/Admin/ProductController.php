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
        if (! $request->filled('slug') && $request->filled('title')) {
            $request->merge(['slug' => Str::slug($request->input('title'))]);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'full_title' => ['nullable', 'string', 'max:200'],
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
            'spec_keys' => ['nullable', 'array'],
            'spec_values' => ['nullable', 'array'],
            'spec_groups_json' => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:3000'],
            'warranty_text' => ['nullable', 'string', 'max:200'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

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
            // When user uploads a new product photo, use it for gallery too unless they explicitly picked a different gallery preset
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
            // If user uploaded a new card photo and images_text still had only the default placeholder, replace it with the uploaded photo
            if ($uploadedNewCardImage && $imagesList === ['img/gallery-a55.png']) {
                $imagesList = [$imagePath];
            }
        }
        if (empty($imagesList)) {
            $imagesList = [$galleryPath];
        }

        $specs = [];
        $keys = $request->input('spec_keys', []);
        $values = $request->input('spec_values', []);
        foreach ($keys as $idx => $key) {
            $k = trim((string) $key);
            $v = trim((string) ($values[$idx] ?? ''));
            if ($k !== '' && $v !== '') {
                $specs[$k] = $v;
            }
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
            'title' => $validated['title'],
            'full_title' => $validated['full_title'] ?: $validated['title'],
            'brand' => $validated['brand'],
            'price_rub' => (int) $validated['price_rub'],
            'old_price_rub' => ! empty($validated['old_price_rub']) ? (int) $validated['old_price_rub'] : null,
            'stock' => (int) $validated['stock'],
            'image' => ltrim($imagePath, '/'),
            'gallery' => ltrim($galleryPath, '/'),
            'images' => $imagesList,
            'specs' => ! empty($specs) ? $specs : null,
            'spec_groups' => $specGroups,
            'description' => $validated['description'] ?? null,
            'warranty_text' => $validated['warranty_text'] ?: 'Rasmiy kafolat 12 oy · Original mahsulot',
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
