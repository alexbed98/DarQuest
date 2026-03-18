<?php

class Validation {

    public static function stringIsSize(string $string, int $min, int $max = PHP_INT_MAX): bool {

        $length = strlen($string);

        return $length >= $min && $length <= $max;

    }

    public static function passwordIsValid(string $password, string $pattern, int $minSize = 8): bool {

        return strlen($password) >= $minSize && preg_match($pattern, $password);

    }

}
