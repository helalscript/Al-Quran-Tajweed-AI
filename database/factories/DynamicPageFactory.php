<?php

namespace Database\Factories;

use App\Models\DynamicPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\DynamicPage>
 */
class DynamicPageFactory extends Factory
{
    protected $model = DynamicPage::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(3);

        return [
            'page_title' => $title,
            'page_slug' => \Str::slug($title).'-'.$this->faker->unique()->numberBetween(1, 10000),
            'page_content' => '<p>'.$this->faker->paragraph(3).'</p>',
            'status' => 'active',
        ];
    }
}

