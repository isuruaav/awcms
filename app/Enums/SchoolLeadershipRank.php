<?php

namespace App\Enums;

enum SchoolLeadershipRank: string
{
    case FieldMarshal = 'field_marshal';
    case General = 'general';
    case LieutenantGeneral = 'lieutenant_general';
    case MajorGeneral = 'major_general';
    case Brigadier = 'brigadier';
    case Colonel = 'colonel';
    case LieutenantColonel = 'lieutenant_colonel';
    case Major = 'major';
    case Captain = 'captain';
    case Lieutenant = 'lieutenant';
    case SecondLieutenant = 'second_lieutenant';

    case WarrantOfficerClassOne = 'warrant_officer_class_i';
    case WarrantOfficerClassTwo = 'warrant_officer_class_ii';
    case StaffSergeant = 'staff_sergeant';
    case Sergeant = 'sergeant';
    case Corporal = 'corporal';
    case LanceCorporal = 'lance_corporal';
    case Private = 'private';

    public function label(): string
    {
        return $this->labelEn();
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::FieldMarshal => 'Field Marshal',
            self::General => 'General',
            self::LieutenantGeneral => 'Lieutenant General',
            self::MajorGeneral => 'Major General',
            self::Brigadier => 'Brigadier',
            self::Colonel => 'Colonel',
            self::LieutenantColonel => 'Lieutenant Colonel',
            self::Major => 'Major',
            self::Captain => 'Captain',
            self::Lieutenant => 'Lieutenant',
            self::SecondLieutenant => '2nd Lieutenant',

            self::WarrantOfficerClassOne => 'Warrant Officer Class I',
            self::WarrantOfficerClassTwo => 'Warrant Officer Class II',
            self::StaffSergeant => 'Staff Sergeant',
            self::Sergeant => 'Sergeant',
            self::Corporal => 'Corporal',
            self::LanceCorporal => 'Lance Corporal',
            self::Private => 'Private',
        };
    }

    public function labelSi(): string
    {
        return match ($this) {
            self::FieldMarshal => 'ෆීල්ඩ් මාර්ෂල්',
            self::General => 'ජෙනරාල්',
            self::LieutenantGeneral => 'ලුතිනන් ජෙනරාල්',
            self::MajorGeneral => 'මේජර් ජෙනරාල්',
            self::Brigadier => 'බ්‍රිගේඩියර්',
            self::Colonel => 'කර්නල්',
            self::LieutenantColonel => 'ලුතිනන් කර්නල්',
            self::Major => 'මේජර්',
            self::Captain => 'කැප්ටන්',
            self::Lieutenant => 'ලුතිනන්',
            self::SecondLieutenant => 'දෙවන ලුතිනන්',

            self::WarrantOfficerClassOne => 'වෝරන්ට් නිලධාරී පන්තිය I',
            self::WarrantOfficerClassTwo => 'වෝරන්ට් නිලධාරී පන්තිය II',
            self::StaffSergeant => 'ස්ටාෆ් සැජන්ට්',
            self::Sergeant => 'සැජන්ට්',
            self::Corporal => 'කෝප්‍රල්',
            self::LanceCorporal => 'ලෑන්ස් කෝප්‍රල්',
            self::Private => 'සෙබළ',
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

    public function isCommissioned(): bool
    {
        return match ($this) {
            self::FieldMarshal,
            self::General,
            self::LieutenantGeneral,
            self::MajorGeneral,
            self::Brigadier,
            self::Colonel,
            self::LieutenantColonel,
            self::Major,
            self::Captain,
            self::Lieutenant,
            self::SecondLieutenant => true,

            default => false,
        };
    }
}
