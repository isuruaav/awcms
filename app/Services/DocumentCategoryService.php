<?php

namespace App\Services;

use App\Models\DocumentCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class DocumentCategoryService
{
    public function create(
        User $actor,
        string $name,
        ?string $description = null,
        int $sortOrder = 0,
        bool $isActive = true,
    ): DocumentCategory {
        Gate::forUser(
            $actor,
        )->authorize(
            'documents.categories.manage',
        );

        $name =
            $this->cleanName(
                $name,
            );

        $description =
            $this->cleanDescription(
                $description,
            );

        $sortOrder =
            $this->normaliseSortOrder(
                $sortOrder,
            );

        $slug =
            $this->uniqueSlug(
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
            ): DocumentCategory {
                $category =
                    DocumentCategory::query()
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
                    event: 'documents.category-created',

                    description: 'A document category was created.',

                    actor: $actor,

                    subject: $category,

                    oldValues: [],

                    newValues: [
                        'name' => $name,

                        'slug' => $slug,

                        'description' => $description,

                        'is_active' => $isActive,

                        'sort_order' => $sortOrder,
                    ],
                );

                return $category->refresh();
            },
            3,
        );
    }

    public function update(
        DocumentCategory $category,
        User $actor,
        string $name,
        ?string $description = null,
        int $sortOrder = 0,
        bool $isActive = true,
    ): DocumentCategory {
        Gate::forUser(
            $actor,
        )->authorize(
            'documents.categories.manage',
        );

        if ($category->trashed()) {
            throw ValidationException::withMessages([
                'category' => 'A deleted document category cannot be updated.',
            ]);
        }

        $name =
            $this->cleanName(
                $name,
            );

        $description =
            $this->cleanDescription(
                $description,
            );

        $sortOrder =
            $this->normaliseSortOrder(
                $sortOrder,
            );

        $categoryId =
            (int) $category->getKey();

        $slug =
            $this->uniqueSlug(
                name: $name,
                ignoreId: $categoryId,
            );

        return DB::transaction(
            function () use (
                $categoryId,
                $actor,
                $name,
                $slug,
                $description,
                $sortOrder,
                $isActive,
            ): DocumentCategory {
                $category =
                    DocumentCategory::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $categoryId,
                        );

                if ($category->trashed()) {
                    throw ValidationException::withMessages([
                        'category' => 'A deleted document category cannot be updated.',
                    ]);
                }

                $oldValues = [
                    'name' => $category->getAttribute(
                        'name',
                    ),

                    'slug' => $category->getAttribute(
                        'slug',
                    ),

                    'description' => $category->getAttribute(
                        'description',
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
                    event: 'documents.category-updated',

                    description: 'A document category was updated.',

                    actor: $actor,

                    subject: $category,

                    oldValues: $oldValues,

                    newValues: [
                        'name' => $name,

                        'slug' => $slug,

                        'description' => $description,

                        'is_active' => $isActive,

                        'sort_order' => $sortOrder,
                    ],
                );

                return $category->refresh();
            },
            3,
        );
    }

    public function setActive(
        DocumentCategory $category,
        User $actor,
        bool $isActive,
    ): DocumentCategory {
        Gate::forUser(
            $actor,
        )->authorize(
            'documents.categories.manage',
        );

        if ($category->trashed()) {
            throw ValidationException::withMessages([
                'category' => 'A deleted document category cannot be activated or deactivated.',
            ]);
        }

        $categoryId =
            (int) $category->getKey();

        return DB::transaction(
            function () use (
                $categoryId,
                $actor,
                $isActive,
            ): DocumentCategory {
                $category =
                    DocumentCategory::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $categoryId,
                        );

                if ($category->trashed()) {
                    throw ValidationException::withMessages([
                        'category' => 'A deleted document category cannot be activated or deactivated.',
                    ]);
                }

                $currentStatus =
                    (bool) $category->getAttribute(
                        'is_active',
                    );

                if ($currentStatus === $isActive) {
                    return $category;
                }

                $category->forceFill([
                    'is_active' => $isActive,

                    'updated_by' => $actor->id,
                ])->save();

                app(
                    AuditLogger::class,
                )->log(
                    event: $isActive
                        ? 'documents.category-activated'
                        : 'documents.category-deactivated',

                    description: $isActive
                        ? 'A document category was activated.'
                        : 'A document category was deactivated.',

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
            3,
        );
    }

    private function cleanName(
        string $name,
    ): string {
        $name =
            strip_tags(
                $name,
            );

        $normalised =
            preg_replace(
                '/\s+/u',
                ' ',
                $name,
            );

        $name =
            trim(
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

        $description =
            strip_tags(
                $description,
            );

        $normalised =
            preg_replace(
                '/\s+/u',
                ' ',
                $description,
            );

        $description =
            trim(
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
        $baseSlug =
            Str::slug(
                $name,
            );

        if ($baseSlug === '') {
            $baseSlug =
                'category';
        }

        /*
         * Keep enough space for numeric suffixes while
         * remaining inside the standard 255-character
         * database string column.
         */
        $baseSlug =
            mb_substr(
                $baseSlug,
                0,
                240,
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
                    255 - mb_strlen(
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
        $query =
            DocumentCategory::withTrashed()
                ->where(
                    'slug',
                    $slug,
                );

        if ($ignoreId !== null) {
            $query->whereKeyNot(
                $ignoreId,
            );
        }

        return $query->exists();
    }
}
