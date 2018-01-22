<?php
namespace Core\Service;

use Phalcon\Events\Event as PhEvent;
use Phalcon\Mvc\Dispatcher;
use Engine\Service\Locator as EnServiceLocator;

class Transformer extends EnServiceLocator
{
    public function beforeExecuteRoute(PhEvent $event, Dispatcher $dispatcher)
    {
        $include = $this->getDI()->get('request')->getQuery('include');

        if (!is_null($include)) {
            $this->getDI()->get('transformer')->parseIncludes($include);
        }
    }
}
