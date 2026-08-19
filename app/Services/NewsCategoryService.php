<?php

namespace App\Services;

use App\Models\NewsCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class NewsCategoryService
{
    public function create(
        User $actor,
        string $name,
        ?string $description = null,
        int $sortOrder = 0,
        bool $isActive = true,
    ): NewsCategory {
        Gate::forUser(
            $actor,
        )->authorize(
            'news.categories.manage',
        );

        $name = $this->cleanName(
            $name,
        );

        $description = $this->cleanDescription(
            $description,
        );

        $sortOrder = $this->normaliseSortOrder(
            $sortOrder,
        );

        $slug = $this->uniqueSlug(
            $name,
        );

        return DB::transaction(
            function () use (
                $actor,
                $name,
                $slug,
                $description,
                $sortOrder,
                $isActive,
            ): NewsCategory {
                $category = NewsCategory::query()
                    ->create([
                        'name' => $name,

                        'slug' => $slug,

                        'description' => $description,

                        'is_active' => $isActive,

                        'sort_order' => $sortOrder,

                        'created_by' => $actor->id,

                        'updated_by' => $actor->id,
                    ]);

                app(
                    AuditLogger::class,
                )->log(
                    event: 'news.category-created',

                    description: 'A news category was created.',

                    actor: $actor,

                    subject: $category,

                    oldValues: [],

                    newValues: [
                        'name' => $name,

                        'slug' => $slug,

                        'is_active' => $isActive,

                        'sort_order' => $sortOrder,
                    ],
                );

                return $category->refresh();
            },
        );
    }

    public function update(
        NewsCategory $category,
        User $actor,
        string $name,
        ?string $description = null,
        int $sortOrder = 0,
        bool $isActive = true,
    ): NewsCategory {
        Gate::forUser(
            $actor,
        )->authorize(
            'news.categories.manage',
        );

        if ($category->trashed()) {
            throw ValidationException::withMessages([
                'category' => 'A deleted news category cannot be updated.',
            ]);
        }

        $name = $this->cleanName(
            $name,
        );

        $description = $this->cleanDescription(
            $description,
        );

        $sortOrder = $this->normaliseSortOrder(
            $sortOrder,
        );

        $categoryId = (int) $category->getKey();

        $slug = $this->uniqueSlug(
            name: $name,
            ignoreId: $categoryId,
        );

        return DB::transaction(
            function () use (
                $category,
                $actor,
                $name,
                $slug,
                $description,
                $sortOrder,
                $isActive,
            ): NewsCategory {
                $oldValues = [
                    'name' => $category->getAttribute(
                        'name',
                    ),

                    'slug' => $category->getAttribute(
                        'slug',
                    ),

                    'is_active' => (bool) $category->getAttribute(
                        'is_active',
                    ),

                    'sort_order' => (int) $category->getAttribute(
                        'sort_order',
                    ),
                ];

                $category->forceFill([
                    'name' => $name,

                    'slug' => $slug,

                    'description' => $description,

                    'is_active' => $isActive,

                    'sort_order' => $sortOrder,

                    'updated_by' => $actor->id,
                ])->save();

                app(
                    AuditLogger::class,
                )->log(
                    event: 'news.category-updated',

                    description: 'A news category was updated.',

                    actor: $actor,

                    subject: $category,

                    oldValues: $oldValues,

                    newValues: [
                        'name' => $name,

                        'slug' => $slug,

                        'is_active' => $isActive,

                        'sort_order' => $sortOrder,
                    ],
                );

                return $category->refresh();
            },
        );
    }

    public function setActive(
        NewsCategory $category,
        User $actor,
        bool $isActive,
    ): NewsCategory {
        Gate::forUser(
            $actor,
        )->authorize(
            'news.categories.manage',
        );

        if ($category->trashed()) {
            throw ValidationException::withMessages([
                'category' => 'A deleted news category cannot be activated or deactivated.',
            ]);
        }

        $currentStatus =
            (bool) $category->getAttribute(
                'is_active',
            );

        if ($currentStatus === $isActive) {
            return $category;
        }

        return DB::transaction(
            function () use (
                $category,
                $actor,
                $currentStatus,
                $isActive,
            ): NewsCategory {
                $category->forceFill([
                    'is_active' => $isActive,

                    'updated_by' => $actor->id,
                ])->save();

                app(
                    AuditLogger::class,
                )->log(
                    event: $isActive
                        ? 'news.category-activated'
                        : 'news.category-deactivated',

                    description: $isActive
                        ? 'A news category was activated.'
                        : 'A news category was deactivated.',

                    actor: $actor,

                    subject: $category,

                    oldValues: [
                        'is_active' => $currentStatus,
                    ],

                    newValues: [
                        'is_active' => $isActive,
                    ],
                );

                return $category->refresh();
            },
        );
    }

    private function cleanName(
        string $name,
    ): string {
        $name = strip_tags(
            $name,
        );

        $normalised = preg_replace(
            '/\s+/u',
            ' ',
            $name,
        );

        $name = trim(
            is_string($normalised)
                ? $normalised
                : '',
        );

        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => 'The category name is required.',
            ]);
        }

        if (mb_strlen($name) > 150) {
            throw ValidationException::withMessages([
                'name' => 'The category name may not exceed 150 characters.',
            ]);
        }

        return $name;
    }

    private function cleanDescription(
        ?string $description,
    ): ?string {
        if ($description === null) {
            return null;
        }

        $description = strip_tags(
            $description,
        );

        $normalised = preg_replace(
            '/\s+/u',
            ' ',
            $description,
        );

        $description = trim(
            is_string($normalised)
                ? $normalised
                : '',
        );

        if ($description === '') {
            return null;
        }

        if (mb_strlen($description) > 2000) {
            throw ValidationException::withMessages([
                'description' => 'The category description may not exceed 2000 characters.',
            ]);
        }

        return $description;
    }

    private function normaliseSortOrder(
        int $sortOrder,
    ): int {
        return min(
            65535,
            max(
                0,
                $sortOrder,
            ),
        );
    }

    private function uniqueSlug(
        string $name,
        ?int $ignoreId = null,
    ): string {
        $baseSlug = Str::slug(
            $name,
        );

        if ($baseSlug === '') {
            $baseSlug =
                'category';
        }

        /*
         * Keep enough space for "-2", "-3", etc.
         * while remaining inside the 180-char
         * database column.
         */
        $baseSlug = mb_substr(
            $baseSlug,
            0,
            170,
        );

        $slug =
            $baseSlug;

        $counter =
            2;

        while (
            $this->slugExists(
                slug: $slug,
                ignoreId: $ignoreId,
            )
        ) {
            $suffix =
                '-'.$counter;

            $slug =
                mb_substr(
                    $baseSlug,
                    0,
                    180 - mb_strlen(
                        $suffix,
                    ),
                )
                .$suffix;

            $counter++;
        }

        return $slug;
    }

    private function slugExists(
        string $slug,
        ?int $ignoreId = null,
    ): bool {
        $query = NewsCategory::query()
            ->withTrashed()
            ->where(
                'slug',
                $slug,
            );

        if ($ignoreId !== null) {
            $query->where(
                'id',
                '!=',
                $ignoreId,
            );
        }

        return $query->exists();
    }
}
