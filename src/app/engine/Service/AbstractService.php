<?php
namespace Engine\Service;

use Engine\Behavior\DIBehavior;
use Engine\Interfaces\ServiceInterface;
use Phalcon\DI;
use Phalcon\DiInterface;

/**
 * Abstract Service.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://olli.vn/
 */
abstract class AbstractService implements ServiceInterface
{
    use DIBehavior {
        DIBehavior::__construct as protected __DIConstruct;
    }

    /**
     * Service arguments.
     *
     * @var array
     */
    private $_arguments;

    /**
     * Create Service.
     *
     * @param DiInterface $di        Dependency Service.
     * @param array       $arguments Service arguments.
     */
    public function __construct(DiInterface $di, $arguments)
    {
        $this->__DIConstruct($di);
        $this->_arguments = $arguments;
    }

    /**
     * Get Service call arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->_arguments;
    }
}
