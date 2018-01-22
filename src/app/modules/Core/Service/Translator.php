<?php
namespace Core\Service;

use Engine\Service\Locator as EnServiceLocator;
use Phalcon\Translate\Adapter\NativeArray as PhTranslateArray;
use Phalcon\Events\Event as PhEvent;
use Phalcon\Mvc\Dispatcher;
use Yosymfony\Toml\Toml;

/**
 * Core translate service.
 *
 * @category  Core
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://olli.vn/
 */
class Translator extends EnServiceLocator
{
    const DEFAULT_LANG_PACK = 'Core';
    const CONFIG_CACHE_DIR = '/app/storage/cache/data/';

    /**
     * This action is executed before execute any action in the application.
     *
     * @param PhalconEvent $event      Event object.
     * @param Dispatcher   $dispatcher Dispatcher object.
     *
     * @return mixed
     */
    public function beforeDispatch(PhEvent $event, Dispatcher $dispatcher)
    {
        $di = $this->getDI();

        $cookies = $this->getDI()->get('cookies');
        $config = $this->getDI()->get('config');

        $languageCode = 'en-us';
        $locale = 'en_US';
        if ($cookies->has('locale')) {
            $cookieLang = (string) $cookies->get('locale')->getValue();

            switch ($cookieLang) {
                case 'en':
                    $languageCode = 'en-us';
                    $locale = 'en_US';
                    break;
                case 'vi':
                    $languageCode = 'vi-vn';
                    $locale = 'vi_VN';
                    break;
            }
        }

        if (!in_array($languageCode, $di->get('config')->default->supportedLocales->toArray())) {
            $languageCode = 'en-us';
            $locale = 'en_US';
        }

        // Change locale to support date
        \Moment\Moment::setLocale($locale);
        \Moment\Moment::setDefaultTimezone($config->default->timezone);
        date_default_timezone_set($config->default->timezone);

        $messages = [];
        $cacheFile = ROOT_PATH . self::CONFIG_CACHE_DIR
            . 'language.'
            . strtolower($dispatcher->getModuleName())
            . '.'
            . strtolower($dispatcher->getControllerName())
            . '.'
            . $languageCode . '.php';

        if (file_exists($cacheFile)) {
            $content = (array) include($cacheFile);

            $translate = new PhTranslateArray([
                'content' => $content
            ]);
        } else {
            $directory = $di->get('registry')->directories->modules . ucfirst($dispatcher->getModuleName()) . '/Locales/'
                . $languageCode . '/'
                . strtolower($dispatcher->getControllerName());
            $extension = '.toml';

            if (file_exists($directory . $extension)) {
                $messages = Toml::Parse($directory . $extension);
            }

            // Load default language package
            $defaultLangPath =  $di->get('registry')->directories->modules
                . self::DEFAULT_LANG_PACK
                . '/Locales/'
                . $languageCode
                . '/default.toml';
            $default = Toml::Parse($defaultLangPath);

            $content = array_merge($messages, $default);

            // cache this file
            file_put_contents($cacheFile, $this->_toConfigurationString($content));

            // added Default and Messages variables to translation service
            $translate = new PhTranslateArray([
                'content' => $content
            ]);
        }

        $di->set('lang', $translate);

        return !$event->isStopped();
    }

    /**
     * Save language messages to file.
     *
     * @param array|null $data Messages data.
     *
     * @return void
     */
    protected function _toConfigurationString($data = null)
    {
        $configText = var_export($data, true);

        // Fix pathes. This related to windows directory separator.
        $configText = str_replace('\\\\', DS, $configText);

        $configText = str_replace("'" . ROOT_PATH, "ROOT_PATH . '", $configText);
        $headerText = '<?php
/**
* WARNING
*
* Manual changes to this file may cause a malfunction of the system.
* Be careful when changing settings!
*
*/

return ';
        return $headerText . $configText . ';';
    }
}
