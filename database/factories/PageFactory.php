<?php

namespace Database\Factories;

use App\Enums\PageStatus;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
final class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = fake()
            ->unique()
            ->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'blocks' => null,
            'status' => PageStatus::Draft->value,
            'submitted_at' => null,
            'approved_at' => null,
            'published_at' => null,
            'archived_at' => null,
            'created_by' => null,
            'updated_by' => null,
            'approved_by' => null,
        ];
    }

    public function submitted(): static
    {
        return $this->state(
            fn (array $attributes): array => [
                'status' => PageStatus::Submitted->value,
                'submitted_at' => now(),
            ],
        );
    }

    public function approved(): static
    {
        return $this->state(
            fn (array $attributes): array => [
                'status' => PageStatus::Approved->value,
                'submitted_at' => now()->subHour(),
                'approved_at' => now(),
            ],
        );
    }

    public function published(): static
    {
        return $this->state(
            fn (array $attributes): array => [
                'status' => PageStatus::Published->value,
                'submitted_at' => now()->subHours(2),
                'approved_at' => now()->subHour(),
                'published_at' => now(),
            ],
        );
    }

    public function archived(): static
    {
        return $this->state(
            fn (array $attributes): array => [
                'status' => PageStatus::Archived->value,
                'archived_at' => now(),
            ],
        );
    }
}
