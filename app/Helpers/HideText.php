<?php

namespace App\Helpers;

class HideText {
    public static function hide($text)
    {
        $words = explode(' ', $text);
        $hiddenWords = array_map(function($word) {
            $firstChar = substr($word, 0, 1);
            $hiddenLength = max(0, strlen($word) - 1);
            return $firstChar . str_repeat('*', $hiddenLength);
        }, $words);

        return implode(' ', $hiddenWords);
    }
}
