<?php

namespace App\Support;

class UzbekCyrillic
{
    private const PRESERVE_TOKENS = [
        'Yubor Market',
        'YUBOR',
        'MARKET',
        'Super Retina XDR',
        'Dynamic Island',
        'Super AMOLED',
        'Full No Frost',
        'No Frost',
        'A16 Bionic',
        'Toy Land',
        'Pro Max',
        'Galaxy',
        'Samsung',
        'iPhone',
        'Apple',
        'Artel',
        'Exynos',
        'Android',
        'Telegram',
        'WhatsApp',
        'Email',
        'USB-C',
        'Wi-Fi',
        'microSD',
        'eSIM',
        'nano',
        'IP67',
        'MPC-5',
        'mAh',
        'EMU',
        'SBP',
        'MIR',
        'SMS',
        'NFC',
        'EAC',
        'UZS',
        'RUB',
        'GB',
        'TB',
        'MB',
        'MP',
        'Hz',
        'Vt',
        'dB',
        'HD',
        '5G',
        '4K',
        'A55',
        'VK',
        'VS-',
    ];

    private const WORD_OVERRIDES = [
        'protsessor' => 'процессор',
        'Protsessor' => 'Процессор',
        'aksiya' => 'акция',
        'Aksiya' => 'Акция',
        'aksiyalar' => 'акциялар',
        'Aksiyalar' => 'Акциялар',
        'kuryer' => 'курьер',
        'Kuryer' => 'Курьер',
        'kuryerda' => 'курьерда',
        'kuryerga' => 'курьерга',
        'Kuryerga' => 'Курьерга',
        'sentyabr' => 'сентябрь',
        'oktyabr' => 'октябрь',
        'noyabr' => 'ноябрь',
        'dekabr' => 'декабрь',
        'kompyuter' => 'компьютер',
        'kompyuterda' => 'компьютерда',
    ];

    public static function transliterate(string $text): string
    {
        if ($text === '' || ! preg_match('/[A-Za-z]/', $text)) {
            return $text;
        }

        // 1) Protect brand / tech tokens that should remain in Latin
        $placeholders = [];
        $counter = 0;

        $sortedTokens = self::PRESERVE_TOKENS;
        usort($sortedTokens, fn (string $a, string $b) => strlen($b) <=> strlen($a));

        $pattern = '/(?<![A-Za-z])('.implode('|', array_map(fn ($t) => preg_quote($t, '/'), $sortedTokens)).')(?![A-Za-z])/u';
        $text = preg_replace_callback($pattern, function (array $m) use (&$placeholders, &$counter) {
            $key = "\x01".$counter++."\x02";
            $placeholders[$key] = $m[0];

            return $key;
        }, $text) ?? $text;

        // 2) Apply exact word overrides
        foreach (self::WORD_OVERRIDES as $lat => $cyrl) {
            $text = preg_replace('/(?<![A-Za-z])'.preg_quote($lat, '/').'(?![A-Za-z])/u', $cyrl, $text) ?? $text;
        }

        // 3) Normalize apostrophes for o‘ and g‘
        $apos = "['‘’ʼ`]";
        $text = preg_replace("/O{$apos}/u", 'Ў', $text) ?? $text;
        $text = preg_replace("/o{$apos}/u", 'ў', $text) ?? $text;
        $text = preg_replace("/G{$apos}/u", 'Ғ', $text) ?? $text;
        $text = preg_replace("/g{$apos}/u", 'ғ', $text) ?? $text;

        // 4) Handle Yo‘, yo‘ BEFORE Yo, yo (e.g., yo‘l -> йўл, Yo‘lda -> Йўлда)
        $text = str_replace(['Yoў', 'YOў', 'yoў', 'Yў', 'yў'], ['Йў', 'ЙЎ', 'йў', 'Йў', 'йў'], $text);

        // 5) Digraphs
        $text = str_replace(
            ['SH', 'Sh', 'sh', 'CH', 'Ch', 'ch', 'YO', 'Yo', 'yo', 'YA', 'Ya', 'ya', 'YU', 'Yu', 'yu'],
            ['Ш', 'Ш', 'ш', 'Ч', 'Ч', 'ч', 'Ё', 'Ё', 'ё', 'Я', 'Я', 'я', 'Ю', 'Ю', 'ю'],
            $text
        );

        // 6) Ye / ye at word start or after vowel/apostrophe
        $text = preg_replace('/(^|[\s(\["«—\-]|(?<=[aeiouўаеёиоуўэюяAEIOUЎАЕЁИОУЎЭЮЯ]))Ye/u', '$1Е', $text) ?? $text;
        $text = preg_replace('/(^|[\s(\["«—\-]|(?<=[aeiouўаеёиоуўэюяAEIOUЎАЕЁИОУЎЭЮЯ]))ye/u', '$1е', $text) ?? $text;
        $text = str_replace('YE', 'Е', $text);

        // 7) E / e at word start or after vowel/apostrophe -> Э / э
        $text = preg_replace('/(?<![A-Za-zА-Яа-яЎўҚқҒғҲҳ])E/u', 'Э', $text) ?? $text;
        $text = preg_replace('/(?<![A-Za-zА-Яа-яЎўҚқҒғҲҳ])e/u', 'э', $text) ?? $text;
        $text = preg_replace('/(?<=[aeiouўаеёиоуўэюяAEIOUЎАЕЁИОУЎЭЮЯʼ’\'])e/u', 'э', $text) ?? $text;

        // 8) Tutuq belgisi (apostrophe between Uzbek letters) -> ъ (except after placeholder)
        $text = preg_replace("/(?<=[A-Za-zА-Яа-яЎўҚқҒғҲҳ]){$apos}(?=[A-Za-zА-Яа-яЎўҚқҒғҲҳ])/u", 'ъ', $text) ?? $text;

        // 9) Single-letter Latin -> Cyrillic mapping
        $map = [
            'A' => 'А', 'a' => 'а',
            'B' => 'Б', 'b' => 'б',
            'D' => 'Д', 'd' => 'д',
            'E' => 'Е', 'e' => 'е',
            'F' => 'Ф', 'f' => 'ф',
            'G' => 'Г', 'g' => 'г',
            'H' => 'Ҳ', 'h' => 'ҳ',
            'I' => 'И', 'i' => 'и',
            'J' => 'Ж', 'j' => 'ж',
            'K' => 'К', 'k' => 'к',
            'L' => 'Л', 'l' => 'л',
            'M' => 'М', 'm' => 'м',
            'N' => 'Н', 'n' => 'н',
            'O' => 'О', 'o' => 'о',
            'P' => 'П', 'p' => 'п',
            'Q' => 'Қ', 'q' => 'қ',
            'R' => 'Р', 'r' => 'р',
            'S' => 'С', 's' => 'с',
            'T' => 'Т', 't' => 'т',
            'U' => 'У', 'u' => 'у',
            'V' => 'В', 'v' => 'в',
            'X' => 'Х', 'x' => 'х',
            'Y' => 'Й', 'y' => 'й',
            'Z' => 'З', 'z' => 'з',
        ];
        $text = strtr($text, $map);

        // 10) Restore protected brand/tech tokens
        if (! empty($placeholders)) {
            $text = strtr($text, $placeholders);
        }

        return $text;
    }

    public static function transliterateHtml(string $html): string
    {
        if ($html === '') {
            return $html;
        }

        // Protect <script>...</script>, <style>...</style>, and data-no-transliterate blocks
        $protected = [];
        $idx = 0;

        $html = preg_replace_callback(
            '/<(script|style)\b[^>]*>.*?<\/\1>|<([a-z0-9]+)\b[^>]*\bdata-no-transliterate\b[^>]*>.*?<\/\2>/is',
            function (array $m) use (&$protected, &$idx) {
                $key = "\x03".$idx++."\x04";
                $protected[$key] = $m[0];

                return $key;
            },
            $html
        ) ?? $html;

        // Split by HTML tags and transliterate text segments + selected user-facing attributes
        $parts = preg_split('/(<[^>]+>)/s', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (! is_array($parts)) {
            return strtr($html, $protected);
        }

        $attrPattern = '/\b(placeholder|title|aria-label|alt|data-share-title|data-share-text|data-message|data-delivery-text)="([^"]*)"/u';

        foreach ($parts as $i => $part) {
            if ($part === '') {
                continue;
            }

            if ($part[0] === '<') {
                $parts[$i] = preg_replace_callback($attrPattern, function (array $m) {
                    return $m[1].'="'.self::transliterate($m[2]).'"';
                }, $part) ?? $part;
            } else {
                $parts[$i] = self::transliterate($part);
            }
        }

        $result = implode('', $parts);

        return ! empty($protected) ? strtr($result, $protected) : $result;
    }
}
