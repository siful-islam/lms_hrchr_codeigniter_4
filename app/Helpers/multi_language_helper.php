<?php defined('APPPATH') || exit('No direct script access allowed');

/**
 * Native CodeIgniter 4 multi language helper.
 * CI3 dependencies removed:
 * - no get_instance()
 * - no $CI->load
 * - no $db
 * - no $CI->session->userdata()
 */

if (! function_exists('language_db')) {
    function language_db()
    {
        return \Config\Database::connect();
    }
}

if (! function_exists('language_session')) {
    function language_session()
    {
        return session();
    }
}

if (! function_exists('language_key')) {
    function language_key(string $phrase): string
    {
        return strtolower(preg_replace('/\s+/', '_', trim($phrase)));
    }
}

if (! function_exists('language_default_text')) {
    function language_default_text(string $key): string
    {
        return ucfirst(str_replace('_', ' ', $key));
    }
}

if (! function_exists('language_column_exists')) {
    function language_column_exists(string $languageCode): bool
    {
        $db = language_db();
        return in_array($languageCode, $db->getFieldNames('language'), true);
    }
}

if (! function_exists('language_ensure_column')) {
    function language_ensure_column(string $languageCode): void
    {
        $languageCode = strtolower(trim($languageCode));

        if ($languageCode === '' || ! preg_match('/^[a-z0-9_]+$/', $languageCode)) {
            $languageCode = 'english';
        }

        if (language_column_exists($languageCode)) {
            return;
        }

        $forge = \Config\Database::forge();
        $forge->addColumn('language', [
            $languageCode => [
                'type'       => 'LONGTEXT',
                'null'       => true,
                'default'    => null,
                'collation'  => 'utf8_unicode_ci',
            ],
        ]);
    }
}

if (! function_exists('language_setting')) {
    function language_setting(): string
    {
        $db = language_db();

        $row = $db->table('settings')
            ->select('value')
            ->where('key', 'language')
            ->get()
            ->getRowArray();

        return $row['value'] ?? 'english';
    }
}

if (! function_exists('active_language')) {
    function active_language(bool $useSession = true): string
    {
        $session = language_session();

        if ($useSession) {
            $language = $session->get('language');

            if (! $language) {
                $language = language_setting() ?: 'english';
                $session->set('language', $language);
            }

            return strtolower($language);
        }

        return strtolower(language_setting() ?: 'english');
    }
}

if (! function_exists('translate_phrase_from_db')) {
    function translate_phrase_from_db(string $phrase, string $languageCode): string
    {
        $db = language_db();
        $languageCode = strtolower($languageCode ?: 'english');
        $key = language_key($phrase);

        language_ensure_column($languageCode);

        $row = $db->table('language')
            ->where('phrase', $key)
            ->get()
            ->getRowArray();

        $defaultText = language_default_text($key);

        if ($row) {
            if (! empty($row[$languageCode])) {
                return $row[$languageCode];
            }

            $db->table('language')
                ->where('phrase', $key)
                ->update([$languageCode => $defaultText]);

            return $defaultText;
        }

        $db->table('language')->insert([
            'phrase'       => $key,
            $languageCode  => $defaultText,
        ]);

        return $defaultText;
    }
}

if (! function_exists('get_phrase_')) {
    function get_phrase_($phrase = "", $replaces = [])
    {
        if (! is_array($replaces)) {
            $replaces = [$replaces];
        }

        foreach ($replaces as $replace) {
            $phrase = preg_replace('/____/', (string) $replace, (string) $phrase, 1);
        }

        return $phrase;
    }
}

if (! function_exists('get_phrase')) {
    function get_phrase($phrase = '')
    {
        return translate_phrase_from_db((string) $phrase, active_language(true));
    }
}

if (! function_exists('api_phrase')) {
    function api_phrase($phrase = '')
    {
        return translate_phrase_from_db((string) $phrase, active_language(false));
    }
}

if (! function_exists('site_phrase')) {
    function site_phrase($phrase = '')
    {
        return translate_phrase_from_db((string) $phrase, active_language(true));
    }
}

if (! function_exists('openJSONFile')) {
    function openJSONFile($code)
    {
        $db = language_db();
        $code = strtolower((string) $code);

        language_ensure_column($code);

        $rows = $db->table('language')->get()->getResultArray();

        $keyValuePairs = [];
        foreach ($rows as $row) {
            $key = $row['phrase'];
            $keyValuePairs[$key] = ! empty($row[$code])
                ? $row[$code]
                : language_default_text($key);
        }

        return $keyValuePairs;
    }
}

if (! function_exists('saveDefaultJSONFile')) {
    function saveDefaultJSONFile($language_code)
    {
        $language_code = strtolower((string) $language_code);
        $languageDir = APPPATH . 'language' . DIRECTORY_SEPARATOR;

        if (! is_dir($languageDir)) {
            mkdir($languageDir, 0775, true);
        }

        $newLangFile = $languageDir . $language_code . '.json';
        $enLangFile  = $languageDir . 'english.json';

        if (! file_exists($newLangFile)) {
            if (file_exists($enLangFile)) {
                copy($enLangFile, $newLangFile);
            } else {
                file_put_contents($newLangFile, json_encode(new stdClass(), JSON_PRETTY_PRINT));
            }
        }

        language_ensure_column($language_code);
    }
}

if (! function_exists('saveJSONFile')) {
    function saveJSONFile($language_code, $updating_key, $updating_value)
    {
        $db = language_db();

        $language_code = strtolower((string) $language_code);
        $updating_key = language_key((string) $updating_key);
        $updating_value = str_replace("'", '&#39;', (string) $updating_value);

        language_ensure_column($language_code);

        $existing = $db->table('language')
            ->where('phrase', $updating_key)
            ->countAllResults();

        if ($existing > 0) {
            $db->table('language')
                ->where('phrase', $updating_key)
                ->update([$language_code => $updating_value]);
        } else {
            $db->table('language')->insert([
                'phrase'       => $updating_key,
                $language_code => $updating_value,
            ]);
        }
    }
}

if (! function_exists('escapeJsonString')) {
    function escapeJsonString($value)
    {
        $value = str_replace('"', "'", (string) $value);
        $escapers = ["\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c"];
        $replacements = ["\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b"];

        return str_replace($escapers, $replacements, $value);
    }
}

if (! function_exists('getIsoCode')) {
    function getIsoCode($language = "")
    {
        $all_lan_ISO_CODES = '
        {
            "aa": "Afar",
            "ab": "Abkhazian",
            "ae": "Avestan",
            "af": "Afrikaans",
            "ak": "Akan",
            "am": "Amharic",
            "an": "Aragonese",
            "ar": "Arabic",
            "as": "Assamese",
            "av": "Avaric",
            "ay": "Aymara",
            "az": "Azerbaijani",
            "ba": "Bashkir",
            "be": "Belarusian",
            "bg": "Bulgarian",
            "bh": "Bihari languages",
            "bi": "Bislama",
            "bm": "Bambara",
            "bn": "Bengali",
            "bo": "Tibetan",
            "br": "Breton",
            "bs": "Bosnian",
            "ca": "Catalan",
            "ce": "Chechen",
            "ch": "Chamorro",
            "co": "Corsican",
            "cr": "Cree",
            "cs": "Czech",
            "cu": "Church Slavic",
            "cv": "Chuvash",
            "cy": "Welsh",
            "da": "Danish",
            "de": "German",
            "dv": "Maldivian",
            "dz": "Dzongkha",
            "ee": "Ewe",
            "el": "Greek",
            "en": "English",
            "eo": "Esperanto",
            "es": "Spanish",
            "et": "Estonian",
            "eu": "Basque",
            "fa": "Persian",
            "ff": "Fulah",
            "fi": "Finnish",
            "fj": "Fijian",
            "fo": "Faroese",
            "fr": "French",
            "fy": "Western Frisian",
            "ga": "Irish",
            "gd": "Gaelic",
            "gl": "Galician",
            "gn": "Guarani",
            "gu": "Gujarati",
            "gv": "Manx",
            "ha": "Hausa",
            "he": "Hebrew",
            "hi": "Hindi",
            "ho": "Hiri Motu",
            "hr": "Croatian",
            "ht": "Haitian",
            "hu": "Hungarian",
            "hy": "Armenian",
            "hz": "Herero",
            "ia": "Interlingua",
            "id": "Indonesian",
            "ie": "Interlingue",
            "ig": "Igbo",
            "ii": "Sichuan Yi",
            "ik": "Inupiaq",
            "io": "Ido",
            "is": "Icelandic",
            "it": "Italian",
            "iu": "Inuktitut",
            "ja": "Japanese",
            "jv": "Javanese",
            "ka": "Georgian",
            "kg": "Kongo",
            "ki": "Kikuyu",
            "kj": "Kuanyama",
            "kk": "Kazakh",
            "kl": "Kalaallisut",
            "km": "Central Khmer",
            "kn": "Kannada",
            "ko": "Korean",
            "kr": "Kanuri",
            "ks": "Kashmiri",
            "ku": "Kurdish",
            "kv": "Komi",
            "kw": "Cornish",
            "ky": "Kirghiz",
            "la": "Latin",
            "lb": "Luxembourgish",
            "lg": "Ganda",
            "li": "Limburgan",
            "ln": "Lingala",
            "lo": "Lao",
            "lt": "Lithuanian",
            "lu": "Luba-Katanga",
            "lv": "Latvian",
            "mg": "Malagasy",
            "mh": "Marshallese",
            "mi": "Maori",
            "mk": "Macedonian",
            "ml": "Malayalam",
            "mn": "Mongolian",
            "mr": "Marathi",
            "ms": "Malay",
            "mt": "Maltese",
            "my": "Burmese",
            "na": "Nauru",
            "nb": "Norwegian",
            "nd": "North Ndebele",
            "ne": "Nepali",
            "ng": "Ndonga",
            "nl": "Dutch",
            "nn": "Norwegian",
            "no": "Norwegian",
            "nr": "South Ndebele",
            "nv": "Navajo",
            "ny": "Chichewa",
            "oc": "Occitan",
            "oj": "Ojibwa",
            "om": "Oromo",
            "or": "Oriya",
            "os": "Ossetic",
            "pa": "Panjabi",
            "pi": "Pali",
            "pl": "Polish",
            "ps": "Pushto",
            "pt": "Portuguese",
            "qu": "Quechua",
            "rm": "Romansh",
            "rn": "Rundi",
            "ro": "Romanian",
            "ru": "Russian",
            "rw": "Kinyarwanda",
            "sa": "Sanskrit",
            "sc": "Sardinian",
            "sd": "Sindhi",
            "se": "Northern Sami",
            "sg": "Sango",
            "si": "Sinhala",
            "sk": "Slovak",
            "sl": "Slovenian",
            "sm": "Samoan",
            "sn": "Shona",
            "so": "Somali",
            "sq": "Albanian",
            "sr": "Serbian",
            "ss": "Swati",
            "st": "Sotho, Southern",
            "su": "Sundanese",
            "sv": "Swedish",
            "sw": "Swahili",
            "ta": "Tamil",
            "te": "Telugu",
            "tg": "Tajik",
            "th": "Thai",
            "ti": "Tigrinya",
            "tk": "Turkmen",
            "tl": "Tagalog",
            "tn": "Tswana",
            "to": "Tonga",
            "tr": "Turkish",
            "ts": "Tsonga",
            "tt": "Tatar",
            "tw": "Twi",
            "ty": "Tahitian",
            "ug": "Uighur",
            "uk": "Ukrainian",
            "ur": "Urdu",
            "uz": "Uzbek",
            "ve": "Venda",
            "vi": "Vietnamese",
            "vo": "Volapük",
            "wa": "Walloon",
            "wo": "Wolof",
            "xh": "Xhosa",
            "yi": "Yiddish",
            "yo": "Yoruba",
            "za": "Zhuang",
            "zh": "Chinese",
            "zu": "Zulu"
        }';

        $languages_arr = json_decode($all_lan_ISO_CODES, true);
        $lower = array_map('strtolower', $languages_arr);
        $index = array_search(strtolower((string) $language), $lower, true);

        return is_string($index) && array_key_exists($index, $languages_arr) ? $index : null;
    }
}

/* End of file multi_language_helper.php */


