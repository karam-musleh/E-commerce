<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition()
    {
        $name = $this->faker->unique()->words(2, true);
        $slug = Str::slug($name);

        return [
            'name' => $name,
            'slug' => $slug,
            'description' => $this->faker->sentence(),
            'image' => null,
            'banner' => null,
            'icon' => null,
            'parent_id' => null, // سيتم ربط بعض الفروع لاحقاً في seeder
            'is_active' => $this->faker->boolean(90), // 90% تكون مفعلة
            'is_featured' => $this->faker->boolean(30),
            'is_hot' => $this->faker->boolean(20),
        ];
    }
}
