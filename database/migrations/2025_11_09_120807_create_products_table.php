<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();

            $table->bigInteger('main_price');
            $table->bigInteger('discount')->nullable();
            $table->string('discount_type')->default('percent');
            $table->integer('total_quantity')->default(0);


            $table->string('status')->default('active'); // active, inactive, archived , deleted
            // $table->string('stock_status')->default('in_stock');
            $table->integer('min_qty')->default(1);
            // $table->integer('total_quantity')->default(0);


            $table->boolean('is_featured')->default(false);
            // $table->boolean('is_best_selling')->default(false); // add order table
            // $table->boolean('is_flash_sale')->default(false);  // new table
            // $table->boolean('is_exclusive')->default(false);

            // $table->boolean('todays_deal')->default(false); //new table

            $table->string('unit')->nullable();
            $table->decimal('weight', 8, 2)->nullable();

            // $table->float('rating_avg')->default(0);
            // $table->integer('reviews_count')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
