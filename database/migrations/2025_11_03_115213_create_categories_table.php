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
            $table->string('slug')->unique();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('categories')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('icon')->nullable();
            $table->string('banner')->nullable();        // بانر علوي
            $table->string('image')->nullable();   // صورة غلاف


            $table->text('description')->nullable();

            $table->boolean('is_featured')->default(false);
            // $table->boolean('is_hot')->default(false);
            $table->string('status')->default('active'); // active, inactive, archived , deleted
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
