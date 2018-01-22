<?php
namespace Engine\Service;

use Phalcon\DI;
use Engine\Behavior\DIBehavior;

/**
 * Service container.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://olli.vn/
 */
class Locator
{
    use DIBehavior {
        DIBehavior::__construct as protected __DIConstruct;
    }

    /**
     * Current module name.
     *
     * @var string
     */
    protected $_moduleName;

    /**
     * Create Service container.
     *
     * @param string $moduleName Module naming.
     * @param DI     $di         Dependency Service.
     */
    public function __construct($moduleName, $di)
    {
        $this->_moduleName = $moduleName;
        $this->__DIConstruct($di);
    }

    /**
     * Get Service from container.
     *
     * @param string $name      Service name.
     * @param array  $arguments Service params.
     *
     * @return mixed
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        $ServiceClassName = sprintf('%s\Service\%s', ucfirst($this->_moduleName), ucfirst($name));
        $di = $this->getDI();

        if (!$di->has($ServiceClassName)) {
            if (!class_exists($ServiceClassName)) {
                throw new \Exception(sprintf('Can not find Service with name "%s".', $name));
            }

            $Service = new $ServiceClassName($this->getDI(), $arguments);
            $di->set($ServiceClassName, $Service, true);
            return $Service;
        }

        return $di->get($ServiceClassName);
    }
}
