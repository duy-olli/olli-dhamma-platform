<?php
namespace Engine;

use Phalcon\DI;
use Phalcon\DiInterface;
use Phalcon\Tag as PhTag;

/**
 * Helper class.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://olli.vn/
 */
abstract class Helper extends PhTag
{
    /**
     * Helper constructor is protected.
     *
     * @param DiInterface $di Dependency injection.
     */
    protected function __construct($di)
    {
        $this->setDI($di);
    }

    /**
     * Get helper instance.
     *
     * @param DiInterface|string $nameOrDI Helper name.
     * @param string             $module   Module name.
     *
     * @return mixed
     * @throws Exception
     */
    public static function getInstance($nameOrDI, $module = 'engine')
    {
        if ($nameOrDI instanceof DiInterface) {
            $di = $nameOrDI;
            $helperClassName = get_called_class();
        } else {
            $di = DI::getDefault();
            $nameOrDI = ucfirst($nameOrDI);
            $module = ucfirst($module);
            $helperClassName = sprintf('%s\Helper\%s', $module, $nameOrDI);
        }

        if (!$di->has($helperClassName)) {
            /** @var Helper $helperClassName */
            if (!class_exists($helperClassName)) {
                throw new Exception(
                    sprintf('Can not find Helper with name "%s". Searched in module: %s', $nameOrDI, $module)
                );
            }

            $helper = new $helperClassName($di);
            $di->set($helperClassName, $helper, true);
            return $helper;
        }

        return $di->get($helperClassName);
    }
}
