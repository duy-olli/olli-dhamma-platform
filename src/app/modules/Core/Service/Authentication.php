<?php
namespace Core\Service;

use Phalcon\Events\Event as PhEvent;
use Phalcon\Mvc\Dispatcher;
use Engine\Service\Locator as EnServiceLocator;

/**
 * Request authentication api.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://olli.vn/
 */
class Authentication extends EnServiceLocator
{
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
        $request = $this->getDI()->get('request');
        $authManager = $this->getDI()->get('auth');

        $token = $request->getToken();

        if ($token) {
            $authManager->authenticateToken($token);
        }
    }
}
