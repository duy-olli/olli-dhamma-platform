<?php
namespace Engine;

use Phalcon\Mvc\Application as PhApp;
use Phalcon\DI\FactoryDefault as PhDi;
use Phalcon\Registry as PhRegistry;
use Phalcon\Events\Manager as PhEventsManager;
use Phalcon\Http\Response as PhResponse;

/**
 * Api application class.
 *
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://ketchai.com/
 */
class Api extends PhApp
{
    use Init;

    protected $_config;

    /**
     * Loaders for different modes.
     *
     * @var array
     */
    private $_loaders = [
        'request',
        'response',
        'engine',
        'annotations',
        'db',
        'cache',
        'router',
        'security',
        'transformer',
        'file',
        'queue',
        'sphinx',
        'slack'
    ];

    public function __construct()
    {
        // Create default DI
        $di = new PhDi();

        // Get config
        $this->_config = Config::factory();

        // Store config in the DI container.
        $di->set('config', $this->_config, true);

        /**
         * Adding modules to registry to load.
         * Module namespace - directory will be load from here.
         */
        $registry = new PhRegistry();
        $registry->modules = $this->_config->default->modules;

        $registry->directories = (object) [
            'engine' => ROOT_PATH . '/app/engine/',
            'modules' => ROOT_PATH . '/app/modules/'
        ];
        $di->set('registry', $registry);

        parent::__construct($di);
    }

    /**
     * Runs the application, performing all initializations.
     *
     * @param string $mode Mode name.
     *
     * @return void
     */
    public function run()
    {
        // Set application main objects.
        $di = $this->_dependencyInjector;

        // set app service
        $di->setShared('app', $this);

        $eventsManager = new PhEventsManager();
        $this->setEventsManager($eventsManager);

        /**
         * Init base systems first.
         */
        $this->_initLogger($di, $this->_config, $eventsManager);
        $this->_initLoader($di, $this->_config, $eventsManager);

        /**
         * Init services and engine system.
         */
        foreach ($this->_loaders as $service) {
            $serviceName = ucfirst($service);
            $eventsManager->fire('init:before' . $serviceName, null);
            $result = $this->{'_init' . $serviceName}($di, $this->_config, $eventsManager);
            $eventsManager->fire('init:after' . $serviceName, $result);
        }

        $di->setShared('eventsManager', $eventsManager);
    }

    /**
     * Get application output.
     *
     * @return string
     */
    public function getOutput()
    {
        $this->useImplicitView(false);
        $this->handle()->getContent();
    }

    /**
     * Init modules and register them. (Call from _initLoader)
     *
     * @param array $modules Modules bootstrap classes.
     * @param null  $merge   Merge with existing.
     *
     * @return $this
     */
    public function registerModules(array $modules, $merge = null): PhApp
    {
        $bootstraps = [];
        $di = $this->getDI();

        foreach ($modules as $moduleName => $moduleClass) {
            if (isset($this->_modules[$moduleName])) {
                continue;
            }

            $bootstrap = new $moduleClass($di, $this->getEventsManager());
            $bootstraps[$moduleName] = function () use ($bootstrap, $di) {
                $bootstrap->registerServices();

                return $bootstrap;
            };
        }

        return parent::registerModules($bootstraps, $merge);
    }

    /**
     * Check if application is used from console.
     *
     * @return bool
     */
    public function isConsole()
    {
        return (php_sapi_name() === 'cli');
    }
}
