<?php
namespace Core\Service;

use Phalcon\Acl\Adapter\Memory as PhAclMemory;
use Phalcon\Acl\Resource;
use Phalcon\Acl\Role;
use Phalcon\Events\Event as PhEvent;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Acl as PhAcl;
use Engine\UserException;
use Engine\Constants\ErrorCode;
use Engine\Service\Locator as EnServiceLocator;

/**
 * Api Access Control List .
 *
 * @category  Core
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://olli.vn/
 */
class Authorization extends EnServiceLocator
{
    const
        /**
         * Acl cache key.
         */
        CACHE_KEY_ACL = 'acl.cache';

    /**
     * Acl adapter.
     *
     * @var AclMemory
     */
    protected $_acl;

    /**
     * Get acl system.
     *
     * @return AclMemory
     */
    public function _getAcl($config)
    {
        $permission = $config->permission->groups->toArray();

        // remove default group info
        unset($permission['default']);
        unset($permission['defaultOauth']);

        if (!$this->_acl) {
            $cacheData = $this->getDI()->get('cacheData');
            $acl = $cacheData->get(self::CACHE_KEY_ACL);
            if ($acl === null) {
                $acl = new PhAclMemory();
                $acl->setDefaultAction(PhAcl::DENY);

                $groupList = array_keys($permission);
                foreach ($groupList as $groupConst => $groupValue) {
                    // Add Role
                    $acl->addRole(new Role((string) $groupValue));

                    if (isset($permission[$groupValue]) && is_array($permission[$groupValue]) == true) {
                        foreach ($permission[$groupValue] as $group => $controller) {
                            foreach ($controller as $action) {
                                $actionArr = explode('|', $action);
                                $resource = strtolower($group) . '|' . $actionArr[0];

                                // Add Resource
                                $acl->addResource($resource, $actionArr[1]);

                                // Grant role to resource
                                $acl->allow($groupValue, $resource, $actionArr[1]);
                            }
                        }
                    }
                }

                $cacheData->save(self::CACHE_KEY_ACL, $acl, 2592000); // 30 days cache.
            }

            $this->_acl = $acl;
        }

        return $this->_acl;
    }

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
        $router = $this->getDI()->get('router');
        $config = $this->getDI()->get('config');
        $authManager = $this->getDI()->get('auth');

        // Get endpoint url
        $endpoint = $router->getRewriteUri();

        // Check permission for any resource not in pulic endpoint
        if (!in_array($endpoint, $config->permission->publicEndpoint->toArray())) {
            preg_match('/[V]{1}[0-9]+/', $dispatcher->getNamespaceName(), $handlerVersion);
            $current_resource = $dispatcher->getModuleName()
                . '|'
                . strtolower($handlerVersion[0])
                . ':'
                . strtolower($dispatcher->getControllerName());
            $current_action = $dispatcher->getActionName();

            // Get role from jwt authToken
            $group = $authManager->loggedIn() ? $authManager->getUser()->groupid : $config->permission->groups->default;

            // Get ACL
            $acl = $this->_getAcl($config);

            // Check access
            $allowed = $acl->isAllowed($group, $current_resource, $current_action);

            if ($allowed != PhAcl::ALLOW) {
                if ($authManager->loggedIn()) {
                    throw new UserException(ErrorCode::AUTH_FORBIDDEN);
                } else {
                    throw new UserException(ErrorCode::AUTH_UNAUTHORIZED);
                }

                // Path not allowed!
                return false;
            }
        }

        return !$event->isStopped();
    }
}
