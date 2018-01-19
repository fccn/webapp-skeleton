<?php
/*
* Slim invokable controller to switch the page language using cookies
* The selected language must be defined in the url path in the form - <site_url>/<path>/{lang}
* i.e. mysite.pt/setlang/pt, sets language to pt
*
* Requires locales defined in SiteConfig
*/
namespace Fccn\WebComponents;

use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Psr\Container\ContainerInterface;

class SwitchLanguageAction
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        \Fccn\Lib\FileLogger::debug("SwitchLanguageAction - initialization");
    }

    #sets a cookie with locale information
    public function __invoke($request, $response, $args)
    {
        #get language from url
        $route = $request->getAttribute('route');
        $lang = $route->getArgument('lang');
        \Fccn\Lib\FileLogger::debug("SwitchLanguageAction - got language $lang");
        #TODO if lang empty get from request attribute
        foreach (\Fccn\Lib\SiteConfig::getInstance()->get("locales") as $locale) {
            \Fccn\Lib\FileLogger::debug("SwitchLanguageAction - checking locale: ".print_r($locale, true));
            if (strtoupper($lang) == strtoupper($locale["label"])) {
                $response = $this->setLocale($locale['locale'], $response);
                break;
            }
        }
        #define redirect
        $redirect_url = \Fccn\Lib\SiteConfig::getInstance()->get("base_path") . "/";
        if ($request->hasHeader('HTTP_REFERER')) {
            $header = $request->getHeader('HTTP_REFERER');
            if (is_array($header) && !empty($header) && isset($header[0])) {
                \Fccn\Lib\FileLogger::debug("SwitchLanguageAction - redirecting to referrer $header[0]");
                $redirect_url = $header[0];
            }
        }
        \Fccn\Lib\FileLogger::debug("SwitchLanguageAction - returning response: ".print_r($response, true));
        return $response->withRedirect($redirect_url, 301);
    }

    /*
    * Sets a cookie with information about current locale
    */
    private function setLocale($curr_lang, $response)
    {
        \Fccn\Lib\FileLogger::debug("SwitchLanguageAction - switching to $curr_lang via cookie");
        $cookie_name = \Fccn\Lib\SiteConfig::getInstance()->get("locale_cookie_name");
        //$_COOKIE[$cookie_name] = $curr_lang;
        $response = FigResponseCookies::set(
            $response,
            SetCookie::create($cookie_name)->withValue($curr_lang)->rememberForever()
        );
        return $response;
    }
}
