<?php

namespace App\Helpers;

class NumberToWords
{
    private static $units = [
        '', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
        'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'
    ];

    private static $tens = [
        '', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante', 'quatre-vingt', 'quatre-vingt'
    ];

    public static function convert($number)
    {
        $number = (int) $number;
        
        if ($number == 0) {
            return 'zéro';
        }

        if ($number < 0) {
            return 'moins ' . self::convert(abs($number));
        }

        $words = '';

        // Milliards
        if ($number >= 1000000000) {
            $billions = (int)($number / 1000000000);
            $words .= ($billions == 1 ? 'un milliard' : self::convert($billions) . ' milliards');
            $number %= 1000000000;
            if ($number > 0) $words .= ' ';
        }

        // Millions
        if ($number >= 1000000) {
            $millions = (int)($number / 1000000);
            $words .= ($millions == 1 ? 'un million' : self::convert($millions) . ' millions');
            $number %= 1000000;
            if ($number > 0) $words .= ' ';
        }

        // Mille
        if ($number >= 1000) {
            $thousands = (int)($number / 1000);
            if ($thousands == 1) {
                $words .= 'mille';
            } else {
                $words .= self::convertHundreds($thousands) . ' mille';
            }
            $number %= 1000;
            if ($number > 0) $words .= ' ';
        }

        // Centaines et moins
        if ($number > 0) {
            $words .= self::convertHundreds($number);
        }

        return ucfirst(trim($words));
    }

    private static function convertHundreds($number)
    {
        $words = '';

        // Centaines
        if ($number >= 100) {
            $hundreds = (int)($number / 100);
            if ($hundreds == 1) {
                $words .= 'cent';
            } else {
                $words .= self::$units[$hundreds] . ' cent';
                // Accord de "cents" si pas suivi d'autre nombre
                if ($number % 100 == 0) {
                    $words .= 's';
                }
            }
            $number %= 100;
            if ($number > 0) $words .= ' ';
        }

        // Dizaines et unités
        if ($number > 0) {
            if ($number < 20) {
                $words .= self::$units[$number];
            } else {
                $ten = (int)($number / 10);
                $unit = $number % 10;

                if ($ten == 7 || $ten == 9) {
                    // 70-79 et 90-99
                    $words .= self::$tens[$ten];
                    if ($unit == 1 && $ten == 7) {
                        $words .= '-et-onze';
                    } else {
                        $words .= '-' . self::$units[10 + $unit];
                    }
                } else {
                    $words .= self::$tens[$ten];
                    if ($unit == 1 && $ten != 8) {
                        $words .= '-et-un';
                    } elseif ($unit > 0) {
                        $words .= '-' . self::$units[$unit];
                    } elseif ($ten == 8) {
                        $words .= 's'; // quatre-vingts
                    }
                }
            }
        }

        return $words;
    }
}
