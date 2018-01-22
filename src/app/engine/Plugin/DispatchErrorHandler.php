<?php
namespace Engine\Plugin;

use Phalcon\Events\Event as PhEvent;
use Phalcon\Mvc\Dispatcher as PhDispatcher;
use Phalcon\Mvc\Dispatcher\Exception as PhDispatchException;
use Phalcon\Mvc\User\Plugin as PhUserPlugin;

/**
 * Not found plugin.
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://olli.vn/
 */
class DispatchErrorHandler extends PhUserPlugin
{
    /**
     * Before exception is happening.
     *
     * @param Event            $event      Event object.
     * @param Dispatcher       $dispatcher Dispatcher object.
     * @param PhalconException $exception  Exception object.
     *
     * @throws \Phalcon\Exception
     * @return bool
     */
    public function beforeException(PhEvent $event, PhDispatcher $dispatcher, $exception)
    {
    }
}
