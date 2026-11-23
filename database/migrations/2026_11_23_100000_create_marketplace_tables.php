<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false)->after('password');
            }
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('section');
            $table->string('image')->nullable();
            $table->unsignedInteger('demo_count')->default(4);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('full_title');
            $table->string('brand')->default('Original');
            $table->unsignedInteger('price_rub');
            $table->unsignedInteger('old_price_rub')->nullable();
            $table->unsignedInteger('stock')->default(50);
            $table->string('image');
            $table->string('gallery')->nullable();
            $table->json('images')->nullable();
            $table->json('specs')->nullable();
            $table->json('spec_groups')->nullable();
            $table->text('description')->nullable();
            $table->string('warranty_text')->default('Rasmiy kafolat 12 oy · Original mahsulot');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle');
            $table->string('subtitle_color')->nullable();
            $table->string('gradient');
            $table->string('image');
            $table->string('image_class')->default('top-[10px] right-[10px] h-[155px] w-[145px]');
            $table->string('url')->default('/');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('delivery_days')->default('2–3 kun');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['region_id', 'name']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('status')->default('paid')->index(); // paid, packing, shipping, delivered, cancelled
            $table->string('sender_name');
            $table->string('sender_phone');
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->string('recipient_phone_2')->nullable();
            $table->string('region');
            $table->string('district');
            $table->string('address');
            $table->string('landmark')->nullable();
            $table->text('wish')->nullable();
            $table->string('payment_method')->default('card'); // card, sbp
            $table->decimal('exchange_rate', 10, 3)->default(158.000);
            $table->string('card_masked')->default('MIR •4417');
            $table->unsignedInteger('items_total_rub')->default(0);
            $table->unsignedInteger('discount_rub')->default(0);
            $table->unsignedInteger('oversize_fee_rub')->default(0);
            $table->unsignedInteger('gift_wrap_fee_rub')->default(0);
            $table->unsignedInteger('delivery_fee_rub')->default(0);
            $table->unsignedInteger('total_rub')->default(0);
            $table->string('courier_name')->nullable();
            $table->string('courier_phone')->nullable();
            $table->string('estimated_delivery_text')->nullable();
            $table->string('status_note_title')->nullable();
            $table->string('status_note_text')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('title');
            $table->unsignedInteger('quantity')->default(1);
            $table->string('qty_label')->default('1 dona');
            $table->unsignedInteger('unit_price_rub')->default(0);
            $table->unsignedInteger('total_price_rub')->default(0);
            $table->string('image')->default('img/gallery-a55.png');
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->string('label')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_admin')) {
                $table->dropColumn('is_admin');
            }
        });
    }
};
