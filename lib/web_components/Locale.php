<?php

/*
* Holds information about the current locale
* Provides functionalities to handle locale data
*/


namespace Fccn\WebComponents;

use Fccn\Lib\SiteConfig as SiteConfig;

class Locale
{
    private $current_lang;

    public static function getLocaleFromLabel($label, $default=false)
    {
        foreach (SiteConfig::getInstance()->get("locales") as $locale) {
            if (strtoupper($label) == strtoupper($locale["label"])) {
                return $locale["locale"];
            }
        }
        if ($default) {
            return self::getDefaultLocale();
        }
        return false;
    }

    public static function getLabelFromLocale($localeX, $default=false)
    {
        foreach (SiteConfig::getInstance()->get("locales") as $locale) {
            if (strtoupper($localeX) == strtoupper($locale["locale"])) {
                return $locale["label"];
            }
        }
        if ($default) {
            return self::getLabelFromLocale(self::getDefaultLocale());
        }
        return false;
    }

    public static function getDefaultLocale()
    {
        return SiteConfig::getInstance()->get("defaultLocaleLabel");
    }

    public function __construct($label = false)
    {
        if ($label) {
            $this->current_lang = self::getLocaleFromLabel($label, true);
        } else {
            //fallback to default
            $this->current_lang = self::getLocaleFromLabel(self::getDefaultLocale());
        }
    }

    /*
    * Initializes the locale and configures Gettext
    */
    public function __init()
    {
        $this->request = $request;
        $this->response = $response;
        $locale = $this->current_lang.".utf8";
        FileLogger::debug('Locale::init() - current locale is '.$locale);
        // Set language to Current Language
        $results = putenv('LANG=' . $locale);
        if (!$results) {
            FileLogger::error("Locale::init() - putenv failed");
        }
        $results = setlocale(LC_MESSAGES, $this->current_lang);
        if (!$results) {
            FileLogger::error("Locale::init() - setlocale failed: locale function is not available on this platform, or the given local does not exist in this environment");
        }

        // Specify the location of the translation tables
        $results = bindtextdomain(SiteConfig::getInstance()->get("locale_textdomain"), SiteConfig::getInstance()->get("locale_path"));
        FileLogger::debug("Locale::init() - new text domain is set: $results");
        $results = bind_textdomain_codeset(SiteConfig::getInstance()->get("locale_textdomain"), 'UTF-8');
        FileLogger::debug("Locale::init() - new text domain codeset is: $results");

        // Choose domain
        $results = textdomain(SiteConfig::getInstance()->get("locale_textdomain"));
        FileLogger::debug("Locale::init() - current message domain is set: $results");
    }

    /*
    * returns the current locale
    */
    public function getCurrent()
    {
        return $this->current_lang;
    }
}
