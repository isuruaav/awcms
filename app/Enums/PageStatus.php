<?php

namespace App\Enums;

enum PageStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted for Review',
            self::Approved => 'Approved',
            self::Published => 'Published',
            self::Archived => 'Archived',
        };
    }

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [
                self::Submitted,
                self::Archived,
            ],

            self::Submitted => [
                self::Draft,
                self::Approved,
                self::Archived,
            ],

            self::Approved => [
                self::Draft,
                self::Published,
                self::Archived,
            ],

            self::Published => [
                self::Draft,
                self::Archived,
            ],

            self::Archived => [
                self::Draft,
            ],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array(
            $status,
            $this->allowedTransitions(),
            true,
        );
    }
}
