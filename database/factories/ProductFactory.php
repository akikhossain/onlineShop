<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $title = fake()->unique()->name();
        $slug = Str::slug($title);
        $subCategories = [9, 17, 20];
        $subCatRandKey = array_rand($subCategories);
        $brands = [1, 2, 3, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17];
        $brandRandKey = array_rand($brands);
        return [
            'title' => $title,
            'slug' => $slug,
            'category_id' => 66,
            'sub_category_id' => $subCategories[$subCatRandKey],
            'brand_id' => $brands[$brandRandKey],
            'price' => rand(100, 1000),
            'sku' => rand(1000, 10000),
            'track_qty' => 'Yes',
            'qty' => 10,
            'is_featured' => 'Yes',
            'status' => 1,
        ];
    }
}
