<?php

/**
 * Calculator – Statische Hilfsklasse für einfache mathematische Operationen.
 * Wird u. a. von der calc.php-API oder dem JCL-Interpreter verwendet.
 */
class Calculator {

    /**
     * Addiert zwei Gleitkommazahlen.
     *
     * @param float $a Erster Summand
     * @param float $b Zweiter Summand
     * @return float Ergebnis der Addition
     */
    public static function add(float $a, float $b): float {
        return $a + $b;
    }

    /**
     * Subtrahiert zwei Gleitkommazahlen.
     *
     * @param float $a Minuend
     * @param float $b Subtrahend
     * @return float Ergebnis der Subtraktion
     */
    public static function sub(float $a, float $b): float {
        return $a - $b;
    }

    /**
     * Multipliziert zwei Gleitkommazahlen.
     *
     * @param float $a Erster Faktor
     * @param float $b Zweiter Faktor
     * @return float Produkt beider Zahlen
     */
    public static function mul(float $a, float $b): float {
        return $a * $b;
    }

    /**
     * Dividiert zwei Gleitkommazahlen.
     *
     * @param float $a Dividend
     * @param float $b Divisor
     * @return float|string Quotient oder Fehlermeldung bei Division durch 0
     */
    public static function div(float $a, float $b): string|float {
        return $b != 0 ? $a / $b : "Division durch 0!";
    }
}
