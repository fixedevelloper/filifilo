<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Countries
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 5);
            $table->string('currency')->nullable();
            $table->decimal('default_latitude', 10, 7)->nullable();
            $table->decimal('default_longitude', 10, 7)->nullable();
            $table->timestamps();
        });

        // Cities
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('postal_code')->nullable();
            $table->string('timezone')->nullable();
            $table->decimal('default_latitude', 10, 7)->nullable();
            $table->decimal('default_longitude', 10, 7)->nullable();
            $table->timestamps();
        });

        // Merchants
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // Vehicles
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->string('color');
            $table->string('type');
            $table->integer('seats')->default(1);
            $table->string('registration');
            $table->string('device_id')->nullable();//pour suivre le vehicule lorque le chauffeur n est pas dans le vehicule
            $table->timestamps();
        });

        // Drivers
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->cascadeOnDelete();
            $table->decimal('current_latitude', 10, 7)->nullable();
            $table->decimal('current_longitude', 10, 7)->nullable();
            $table->timestamps();
        });


        Schema::create('driver_positions', function (Blueprint $table) {
            $table->id();
            $table->string('device_id');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->timestamps();

        });

        // Customers
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // Stores
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained('merchants')->cascadeOnDelete();
            $table->string('name');
            $table->enum('store_type', ['shop','store','restaurant']);
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->time('time_open')->nullable();
            $table->time('time_close')->nullable();
            $table->enum('status', ['active','inactive'])->default('active');
            $table->string('image_url')->nullable();
            $table->timestamps();
        });

        // Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('store_type', ['shop','store','restaurant']);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10,2);
            $table->integer('stock_quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('stock_alert_level')->default(0);
            $table->enum('status', ['active','inactive'])->default('active');
            $table->json('ingredients')->nullable();
            $table->json('addons')->nullable();
            $table->boolean('is_deliverable')->default(true);
            $table->boolean('is_pickup')->default(false);
            $table->string('image_url')->nullable();
            $table->timestamps();
        });

        // Addresses
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('address_line');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });

        // Payment Methods
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->json('details');
            $table->string('token')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference');
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_address_id')->nullable()->constrained('addresses');
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods');
            $table->enum('status', ['pending','preparation','in_delivery','delivered','cancelled'])->default('pending');
            $table->enum('payment_status', ['pending','paid','failed'])->default('pending');
            $table->decimal('total_amount', 10,2);
            $table->text('instructions')->nullable();
            $table->integer('preparation_time')->nullable();
            $table->timestamps();
        });

        // Order Items
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->integer('quantity');
            $table->decimal('unit_price',10,2);
            $table->decimal('total_price',10,2);
            $table->json('addons')->nullable();
            $table->string('instructions')->nullable();
            $table->boolean('product_virtual')->default(false);
            $table->timestamps();
        });

        // Deliveries
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->cascadeOnDelete();
            $table->enum('status', ['assigned','in_delivery','delivered'])->default('assigned');
            $table->decimal('current_latitude', 10, 7)->nullable();
            $table->decimal('current_longitude', 10, 7)->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        // Loyalty Points
        Schema::create('loyalty_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->integer('points')->default(0);
            $table->timestamp('expiry_date')->nullable();
            $table->timestamps();
        });

        // Coupons
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->enum('discount_type',['percentage','fixed']);
            $table->decimal('discount_value',10,2);
            $table->decimal('min_order_amount',10,2)->default(0);
            $table->enum('status',['active','expired','used'])->default('active');
            $table->timestamp('expiry_date')->nullable();
            $table->timestamps();
        });

        // Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->enum('recipient_type',['customer','merchant','driver','admin']);
            $table->unsignedBigInteger('recipient_id');
            $table->foreignId('order_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->enum('status',['sent','read','failed'])->default('sent');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->morphs('rateable'); // rateable_type + rateable_id
            $table->tinyInteger('rating')->unsigned(); // 1 à 5
            $table->text('comment')->nullable();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('loyalty_points');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('stores');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('merchants');
        Schema::dropIfExists('users');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('countries');
    }
};

