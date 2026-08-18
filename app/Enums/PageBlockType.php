<?php

namespace App\Enums;

enum PageBlockType: string
{
    case Heading = 'heading';

    case Text = 'text';

    case Image = 'image';

    case Button = 'button';

    case TwoColumns = 'two_columns';

    case Callout = 'callout';

    case Divider = 'divider';

    case Spacer = 'spacer';

    public function label(): string
    {
        return match ($this) {
            self::Heading => 'Heading',
            self::Text => 'Text',
            self::Image => 'Image',
            self::Button => 'Button',
            self::TwoColumns => 'Two Columns',
            self::Callout => 'Callout',
            self::Divider => 'Divider',
            self::Spacer => 'Spacer',
        };
    }
}
