<?php
namespace Engine;

use Phalcon\Mvc\Dispatcher as PhDispatcher;

/**
 * Application dispatcher.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://olli.vn/
 */
class Dispatcher extends PhDispatcher
{
    /**
     * Dispatch.
     * Override it to use own logic.
     *
     * @throws \Exception
     * @return object
     */
    public function dispatch()
    {
        try {
            $parts = explode('_', $this->_handlerName);
            $finalHandlerName = '';

            foreach ($parts as $part) {
                $finalHandlerName .= ucfirst($part);
            }
            $this->_handlerName = $finalHandlerName;
            $this->_actionName = strtolower($this->_actionName);

            return parent::dispatch();
        } catch (\Exception $e) {
        }

        return parent::dispatch();
    }
}
