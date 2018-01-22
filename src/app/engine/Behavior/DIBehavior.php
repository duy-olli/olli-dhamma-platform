<?php
namespace Engine\Behavior;

use Phalcon\DI;
use Phalcon\DiInterface;

/**
 * Dependency container trait.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://olli.vn/
 *
 * @method \Phalcon\Mvc\Model\Transaction\Manager getTransactions()
 * @method \Engine\Asset\Manager getAssets()
 * @method \Phalcon\Mvc\Url getUrl()
 * @method \Phalcon\Logger\Adapter getLogger($file = 'main', $format = null)
 * @method \Phalcon\Http\Request getRequest()
 * @method \Phalcon\Http\Response getResponse()
 * @method \Phalcon\Annotations\Adapter getAnnotations()
 * @method \Phalcon\Mvc\Router getRouter()
 * @method \Phalcon\Mvc\View getView()
 * @method \Phalcon\Db\Adapter\Pdo\Mysql getDb()
 * @method \Phalcon\Mvc\Model\Manager getModelsManager()
 * @method \Phalcon\Config getConfig()
 * @method \Phalcon\Translate\Adapter getI18n()
 * @method \Phalcon\Events\Manager getEventsManager()
 * @method \Phalcon\Session\Adapter getSession()
 */
trait DIBehavior
{
    /**
     * Dependency injection container.
     *
     * @var DIBehaviour|DI
     */
    private $_di;

    /**
     * Create object.
     *
     * @param DiInterface|DIBehaviour $di Dependency injection container.
     */
    public function __construct($di = null)
    {
        if ($di == null) {
            $di = DI::getDefault();
        }
        $this->setDI($di);
    }

    /**
     * Set DI.
     *
     * @param DiInterface $di Dependency injection container.
     *
     * @return void
     */
    public function setDI($di)
    {
        $this->_di = $di;
    }

    /**
     * Get DI.
     *
     * @return DIBehaviour|DI
     */
    public function getDI()
    {
        return $this->_di;
    }
}
