<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type',['SHOP','RESTAURANT']);
            $table->timestamps();
        });
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->timestamps();
        });
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('imageUrl')->nullable();
            $table->enum('type',['SHOP','RESTAURANT']);
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->time('time_open')->nullable();
            $table->time('time_close')->nullable();
            $table->integer('note')->default(0);
            $table->boolean('is_close')->default(false);
            $table->foreignId('vendor_id')->nullable()->constrained('users','id');
            $table->foreignId('city_id')->nullable()->constrained("cities",'id')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('mp_vendor_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->nullable()->constrained('users','id');
            $table->decimal('balance', 15)->default(0);
            $table->decimal('total_fee', 15)->default(0);
            $table->decimal('total_revenue', 15)->default(0);
            $table->string('signature')->nullable();
            $table->text('bank_info')->nullable();
            $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price')->default(0.0);
            $table->string('imageUrl')->nullable();
            $table->foreignId('store_id')->nullable()->constrained("stores",'id')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained("categories",'id')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference');
            $table->enum('type',['SHOP','STORE','DELIVERY','CLEANING']);
            $table->decimal('total')->default(0.0);
            $table->integer('quantity')->default(0);
            $table->decimal('total_ttc')->default(0.0);
            $table->enum('status',['PREPARATION','EN_LIVRAISON','LIVREE','ANNULLEE']);
            $table->foreignId('store_id')->nullable()->constrained("stores",'id')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained("users",'id')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('line_items', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->decimal('price')->default(0.0);
        $table->integer('quantity')->default(0);
        $table->decimal('total')->default(0.0);
        $table->foreignId('order_id')->nullable()->constrained("orders",'id')->nullOnDelete();
        $table->timestamps();
    });
        Schema::create('vendor_revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->nullable()->constrained('users','id');
            $table->foreignId('order_id')->nullable();
            $table->decimal('sub_amount', 15)->default(0)->unsigned()->nullable();
            $table->decimal('fee', 15)->default(0)->unsigned()->nullable();
            $table->decimal('amount', 15)->default(0)->unsigned()->nullable();
            $table->decimal('current_balance', 15)->default(0)->unsigned()->nullable();
            $table->string('currency', 120)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('vendor_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->nullable()->constrained('users','id');
            $table->decimal('fee', 15)->default(0)->unsigned()->nullable();
            $table->decimal('amount', 15)->default(0)->unsigned()->nullable();
            $table->decimal('current_balance', 15)->default(0)->unsigned()->nullable();
            $table->string('currency', 120)->nullable();
            $table->text('description')->nullable();
            $table->text('bank_info')->nullable();
            $table->string('payment_channel', 60)->nullable();
            $table->foreignId('user_id')->default(0);
            $table->string('status', 60)->default('pending');
            $table->text('images')->nullable();
            $table->timestamps();
        });
        Schema::create('vendor_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id');
            $table->foreignId('customer_id')->nullable()->constrained('users','id');
            $table->string('name', 60);
            $table->string('email', 60);
            $table->longText('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('init_tables');
    }
};
