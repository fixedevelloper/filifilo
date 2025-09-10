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
        Schema::create('order_item_drink', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('drink_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
        Schema::table('order_items', function (Blueprint $table) {

            $table->json('supplements')->nullable();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('delivery_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2);
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
        });
        Schema::table('loyalty_points', function (Blueprint $table) {
           $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete(); // ✅ lien avec une commande
            $table->enum('type', ['earned', 'spent'])->default('earned'); // ✅ suivi du type
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ✅ Supprimer les colonnes ajoutées dans l'ordre inverse

        Schema::table('loyalty_points', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
            $table->dropColumn('type');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('discount_amount');
            $table->dropColumn('final_amount');
            $table->dropConstrainedForeignId('coupon_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('supplements');
        });

        Schema::dropIfExists('order_item_drink');
    }

};
