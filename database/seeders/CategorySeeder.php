<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        // إنشاء أقسام رئيسية
        $mainCategories = Category::factory(15)->create();

        $mainCategories->each(function ($parent) {
            Category::factory(rand(2, 4))->create([
                'parent_id' => $parent->id,
            ]);
        });
    }
}
