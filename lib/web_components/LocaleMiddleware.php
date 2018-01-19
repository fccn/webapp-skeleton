<?php
/*
* Slim invokable class middleware for multilanguage support with Fccn\Lib\Locale class
*
* Initializes Locale and ensures a PSR-7 cookie with current locale is defined on the response
*
* Requires locale settings defined in SiteConfig
*/

declare(strict_types=1);

namespace Fccn\WebComponents;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Fccn\Lib\SiteConfig as SiteConfig;

class LocaleMiddleware
{
    protected $locale_param_name;
    protected $locale_cookie_name;
    protected $locale_cookie_path;
    protected $locale_cookie_expire;
    protected $req_attr_name;

    /*
    * Middleware constructor, initializes the locale
    *
    */
    public function __construct()
    {
        $this->locale_param_name = SiteConfig::getInstance()->get("locale_param_name");
        $this->locale_cookie_name = SiteConfig::getInstance()->get("locale_cookie_name");
        $this->locale_cookie_path = SiteConfig::getInstance()->get("locale_cookie_path");
        $this->locale_cookie_expire = $this->calculateCookieExpire(3600 * 24 * 30); // 30 days
        $this->req_attr_name = SiteConfig::getInstance()->get("request_attribute_name");
    }

    /**
     * Locale middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(Request $req, Response $resp, callable $next)
    {
        $locale = $this->getLocale($req);
        $locale->__init();
        $current_locale = $locale->getCurrent();
        $req = $req->withAttribute($this->req_attr_name, $current_locale);
        $resp = $resp->withHeader(
            'Set-Cookie',
            "{$this->locale_cookie_name}=$current_locale; Path={$this->locale_cookie_path}; Expires={$this->locale_cookie_expire}"
        );
        return $next($req, $resp);
    }

    protected function getLocale(Request $req)
    {
        //try to read from param first
        $locale_label = $this->localeFromParam($req);
        if (empty($locale_label)) {
            //then read from cookie
            $locale_label = $this->localeFromCookie($req);
        }
        if (empty($locale_label)) {
            //then read from header
            $locale_label = $this->localeFromHeader($req);
        }
        return new Locale($locale_label);
    }

    /* obtain locale from URL parameter */
    protected function localeFromParam(Request $req)
    {
        $params = $req->getQueryParams();
        if (isset($params[$this->locale_param_name])) {
            $value = $params[$this->locale_param_name];
            return Locale::getLocaleFromLabel($value);
        }
        return false;
    }

    /* obtain locale from cookie */
    protected function localeFromCookie(Request $req)
    {
        $cookies = $req->getCookieParams();
        if (isset($cookies[$this->locale_cookie_name])) {
            $value = $cookies[$this->locale_cookie_name];
            return Locale::getLocaleFromLabel($value);
        }
        return false;
    }

    /* obtain locale from header */
    protected function localeFromHeader(Request $req)
    {
        $values = $this->parse($req->getHeaderLine('Accept-Language'));
        usort($values, [$this, 'sort']);
        foreach ($values as $value) {
            $value = Locale::getLocaleFromLabel($value['locale']);
            if (!empty($value)) {
                return $value;
            }
        }
        // search language if a full locale is not found
        foreach ($values as $value) {
            $value = Locale::getLocaleFromLabel($value['language']);
            if (!empty($value)) {
                return $value;
            }
        }
        return Locale::getDefaultLocale();
    }

    //----- Helper functions ----

    /**
     * calculates cookie expiration date from seconds
     * @param int $secs cookie expiration in seconds from now
     */
    protected function calculateCookieExpire($secs)
    {
        return gmdate('D, d M Y H:i:s T', time() + $secs);
    }

    protected function parse(string $header)
    {
        // the value may contain multiple languages separated by commas,
        // possibly as locales (ex: en_US) with quality (ex: en_US;q=0.5)
        $values = [];
        foreach (explode(',', $header) as $lang) {
            @list($locale, $quality) = explode(';', $lang, 2);
            $val = $this->parseLocale($locale);
            $val['quality'] = $this->parseQuality($quality ?? '');
            $values[] = $val;
        }
        return $values;
    }

    protected function parseLocale(string $locale): array
    {
        // Locale format: language[_territory[.encoding[@modifier]]]
        //
        // Language and territory should be separated by an underscore
        // although sometimes a hyphen is used. The language code should
        // be lowercase. Territory should be uppercase. Take this into
        // account but normalize the returned string as lowercase,
        // underscore, uppercase.
        //
        // The possible codeset and modifier is discarded since the header
        // *should* really list languages (not locales) in the first place
        // and the chances of needing to present content at that level of
        // granularity are pretty slim.
        $lang = '([[:alpha:]]{2})';
        $terr = '([[:alpha:]]{2})';
        $code = '([-\\w]+)';
        $mod  = '([-\\w]+)';
        $regex = "/$lang(?:[-_]$terr(?:\\.$code(?:@$mod)?)?)?/";
        preg_match_all($regex, $locale, $m);
        $locale = $language = strtolower($m[1][0]);
        if (!empty($m[2][0])) {
            $locale .= '_' . strtoupper($m[2][0]);
        }
        return [
            'locale' => $locale,
            'language' => $language
        ];
    }

    protected function parseQuality(string $quality): float
    {
        // If no quality is given then return 0.00001 as a sufficiently
        // small value for sorting purposes.
        @list(, $value) = explode('=', $quality, 2);
        return (float)($value ?: 0.0001);
    }
    
    protected function sort(array $a, array $b): int
    {
        // Sort order is determined first by quality (higher values are
        // placed first) then by order of their apperance in the header.
        if ($a['quality'] < $b['quality']) {
            return 1;
        }
        if ($a['quality'] == $b['quality']) {
            return 0;
        }
        return -1;
    }
}
