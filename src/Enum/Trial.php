<?php

namespace App\Enum;

enum Trial: string
{
    #case D_5K = '252';

    case D_10K = '261';

    case D_21K = '271';

    case D_42K = '295';

    case D_100K = '299';

    public static function getLabel(Trial $trial): string
    {
        return match ($trial) {
            #self::D_5K => '5k',
            self::D_10K => '10k',
            self::D_21K => 'Semi-Marathon',
            self::D_42K => 'Marathon',
            self::D_100K => '100k',
        };
    }

    public static function getBreakpoints(Trial $trial) : array
    {
        return match ($trial) {
            #self::D_5K => [15, 16, 17, 18, 19, 20, 25, 30, 35, 40, 45],
            self::D_10K => [27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 45, 50, 55, 60, 65, 70, 80, 90],
            self::D_21K => [60, 65, 70, 75, 80, 85, 90, 100, 110, 120, 130, 140, 150, 165],
            self::D_42K => [125, 130, 135, 140, 145, 150, 155, 160, 165, 170, 175, 180, 195, 210, 225, 240, 255, 270, 285, 300, 315, 330, 345],
            self::D_100K => [380, 400, 420, 440, 460, 480, 500, 520, 540, 560, 580, 600, 700, 800, 900, 1000, 1100, 1200],
        };
    }

    public static function getChoiceLabel(): \Closure
    {
        return fn ($choice) => self::getLabel($choice);
    }
}
