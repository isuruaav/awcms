<?php

namespace Database\Factories;

use App\Models\NewsCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NewsCategory>
 */
final class NewsCategoryFactory extends Factory
{
    protected $model = NewsCategory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $words = fake()
            ->unique()
            ->words(
                2,
            );

        $name = is_array(
            $words,
        )
            ? implode(
                ' ',
                $words,
            )
            : $words;

        return [
            'name' => Str::title(
                $name,
            ),

            'slug' => Str::slug(
                $name,
            ).'-'.fake()
                ->unique()
                ->numberBetween(
                    1000,
                    999999,
                ),

            'description' => fake()
                ->optional()
                ->sentence(),

            'is_active' => true,

            'sort_order' => 0,

            'created_by' => null,

            'updated_by' => null,
        ];
    }
}
