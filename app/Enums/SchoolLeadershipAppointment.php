<?php

namespace App\Enums;

enum SchoolLeadershipAppointment: string
{
    case Commandant = 'commandant';
    case ChiefInstructor = 'chief_instructor';
    case Adjutant = 'adjutant';
    case WarrantOfficer = 'warrant_officer';

    public function label(): string
    {
        return $this->labelEn();
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::Commandant => 'The Commandant',
            self::ChiefInstructor => 'The Chief Instructor',
            self::Adjutant => 'Adjutant',
            self::WarrantOfficer => 'Warrant Officer School of Signals',
        };
    }

    public function labelSi(): string
    {
        return match ($this) {
            self::Commandant => 'සේනාවිධායක',
            self::ChiefInstructor => 'ප්‍රධාන උපදේශක',
            self::Adjutant => 'අජුටන්ට්',
            self::WarrantOfficer => 'සංඥා පාසලේ වෝරන්ට් නිලධාරී',
        };
    }

    public function labelForLocale(string $locale): string
    {
        return $locale === 'si'
            ? $this->labelSi()
            : $this->labelEn();
    }

    public function adminLabel(): string
    {
        return sprintf(
            '%s — %s',
            $this->labelEn(),
            $this->labelSi(),
        );
    }

    public function requiresCommissionedRank(): bool
    {
        return $this !== self::WarrantOfficer;
    }

    public function homeOrder(): int
    {
        return match ($this) {
            self::Commandant => 1,
            self::ChiefInstructor => 2,
            self::Adjutant => 3,
            self::WarrantOfficer => 4,
        };
    }
}
