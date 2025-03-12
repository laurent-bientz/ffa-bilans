<?php

namespace App\Enum;

enum Trial: string
{
    case D_5K = '252';

    case D_10K = '261';

    case D_21K = '271';

    case D_42K = '295';

    case D_100K = '299';

    public static function getLabel(Trial $trial): string
    {
        return match ($trial) {
            self::D_5K => '5kms',
            self::D_10K => '10kms',
            self::D_21K => 'Semi-Marathon',
            self::D_42K => 'Marathon',
            self::D_100K => '100kms',
        };
    }

    public static function getIncrement(Trial $trial) : int
    {
        return match ($trial) {
            self::D_5K => 0.5 * 60,
            self::D_10K => 1 * 60,
            self::D_21K => 5 * 60,
            self::D_42K => 10 * 60,
            self::D_100K => 20 * 60,
        };
    }

    public static function getChoiceLabel(): \Closure
    {
        return fn ($choice) => self::getLabel($choice);
    }
}
