<?php

namespace App\Enum;

enum Gender: string
{
    case MAN = 'man';

    case WOMAN = 'woman';

    public static function getLabel(Gender $gender)
    {
        return match ($gender) {
            self::MAN => 'Hommes',
            self::WOMAN => 'Femmes',
        };
    }

    public static function getChoiceLabel(): \Closure
    {
        return fn ($choice) => self::getLabel($choice);
    }
}
