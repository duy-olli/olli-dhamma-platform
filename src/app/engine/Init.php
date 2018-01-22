<?php
namespace Engine;

use Phalcon\DI;
use Phalcon\Loader as PhLoader;
use Phalcon\Mvc\Url as PhUrl;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
use Phalcon\Mvc\Model\Manager as PhModelsManager;
use Phalcon\Mvc\Model\MetaData\Strategy\Annotations as PhStrategyAnnotations;
use Phalcon\Mvc\Model\MetaData\Memory as PhMetadataMemory;
use Phalcon\Mvc\Router\Annotations as PhRouterAnnotations;
use Phalcon\Mvc\Router as PhRouter;
use Phalcon\Annotations\Adapter\Memory as PhAnnotationsMemory;
use Phalcon\Events\Manager as PhEventsManager;
use Phalcon\Cache\Frontend\Data as PhCacheData;
use Phalcon\Cache\Frontend\Output as PhCacheOutput;
use Phalcon\Db\Adapter\Pdo\Mysql as PhMysql;
use Phalcon\Security as PhSecurity;
use Phalcon\Http\Response\Cookies as PhCookies;
use Phalcon\Queue\Beanstalk as PhBeanstalk;
use Engine\Config as EnConfig;
use Engine\Service\Locator as EnServiceLocator;
use Engine\Cache\Dummy as EnCacheDummy;
use Engine\Cache\System as EnCacheSystem;
use Engine\Db\Model\Annotations\Initializer as ModelAnnotationsInitializer;
use Engine\Db\Dialect\MysqlExtended as EnMysqlExtended;
use Engine\Http\Request as EnRequest;
use Engine\Http\Response as EnResponse;
use Engine\Http\Response\Manager as EnResponseManager;
use Engine\FractalSerializer as EnFractalSerializer;
use Engine\Exception as EnException;
use Monolog\Logger as MoLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Formatter\LineFormatter;
use League\Flysystem\Adapter\Local as FlyLocalAdapter;
use League\Flysystem\Filesystem as FlySystem;
use League\Fractal\Manager as FractalManager;
use Zend\ServiceManager\ServiceManager as SphinxServiceManager;
use Zend\ServiceManager\Config as SphinxConfig;

/**
 * Application initialization trait.
 *
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://ketchai.com/
 */
trait Init
{
    /**
     * Init logger.
     *
     * @param DI     $di     Dependency Injection.
     * @param Config $config Config object.
     *
     * @return void
     */
    protected function _initLogger(DI $di, EnConfig $config)
    {
        $di->set('logger', function () use ($config) {
            $formatter = new LineFormatter($config->default->logger->format, "j/n/Y g:i a");
            $stream = new StreamHandler(ROOT_PATH . $config->default->logger->path . date('Y-m-d') . '.log', MoLogger::DEBUG);
            $stream->setFormatter($formatter);

            $logger = new MoLogger('human');
            $logger->pushHandler($stream);

            return $logger;
        }, false);
    }

    /**
     * Init loader.
     *
     * @param DI            $di            Dependency Injection.
     * @param Config        $config        Config object.
     * @param EventsManager $eventsManager Event manager.
     *
     * @return Loader
     */
    protected function _initLoader(DI $di, EnConfig $config, PhEventsManager $eventsManager): PhLoader
    {
        /**
         * Add all required namespaces and modules from registry.
         *
         * @var [type]
         */
        $registry = $di->get('registry');
        $namespaces = [];
        $bootstraps = [];

        foreach ($registry->modules as $module) {
            $moduleName = ucfirst($module);
            $namespaces[$moduleName] = $registry->directories->modules . $moduleName;
            $bootstraps[$module] = $moduleName . '\Bootstrap';
        }

        $namespaces['Engine'] = $registry->directories->engine;

        $loader = new PhLoader();
        $loader->registerNamespaces($namespaces);
        $loader->registerDirs([
            ROOT_PATH . '/libs'
        ]);
        $loader->setEventsManager($eventsManager);
        $loader->register();
        $this->registerModules($bootstraps);

        $di->set('loader', $loader);

        return $loader;
    }

    /**
     * Init HTTP Request.
     *
     * @param DI     $di     Dependency Injection.
     *
     * @return void
     */
    public function _initRequest(DI $di)
    {
        $di->set('request', function () {
            return new EnRequest();
        });
    }

    /**
     * Init HTTP Response.
     *
     * @param DI     $di     Dependency Injection.
     * @param Config $config Config object.
     *
     * @return void
     */
    public function _initResponse(DI $di, EnConfig $config)
    {
        $di->set('response', function () use ($config) {
            $responseManager = new EnResponseManager($config->code->toArray());
            $response = new EnResponse();

            return $response->setManager($responseManager);
        }, true);
    }

    /**
     * Init environment.
     *
     * @param DI     $di     Dependency Injection.
     * @param Config $config Config object.
     *
     * @return Url
     */
    protected function _initEngine(DI $di, EnConfig $config)
    {
        if (getenv('DEBUG') == 'true') {
            ini_set('display_errors', true);
            error_reporting(E_ERROR | E_WARNING | E_PARSE | E_ALL);
            // For develop environments
            ini_set('xdebug.var_display_max_depth', 5);
            ini_set('xdebug.var_display_max_data', 10000);
            ini_set('xdebug.var_display_max_children', 256);
        } else {
            ini_set('display_errors', false);
        }

        set_error_handler(
            function ($errorCode, $errorMessage, $errorFile, $errorLine) {
                throw new \ErrorException($errorMessage, $errorCode, 1, $errorFile, $errorLine);
            }
        );

        if ($di->get('app')->isConsole()) {
            set_exception_handler(
                function ($e) use ($di) {
                    /**
                     * Write to log
                     */
                    EnException::log($e, 'cli');

                    echo '[ERROR] ' . $e->getMessage();

                    return true;
                }
            );
        } else {
            set_exception_handler(
                function ($e) use ($di) {
                    /**
                     * Write to log
                     */
                    EnException::log($e, 'api');

                    $di->get('response')->sendException($e);

                    return true;
                }
            );
        }

        /**
         * The URL component is used to generate all kind of urls in the
         * application
         */
        $url = new PhUrl();
        $hostName = getenv('BASE_HOST');

        $url->setBaseUri($di->get('request')->getScheme() . '://' . $hostName . '/');
        $di->set('url', $url);

        foreach ($di->get('registry')->modules as $module) {
            $di->setShared(strtolower($module), function () use ($module, $di) {
                return new EnServiceLocator($module, $di);
            });
        }

        $di->setShared('transactions', function () {
            return new TxManager();
        });

        $di->setShared('cookies', function () {
            $cookies = new PhCookies();
            $cookies->useEncryption(false);

            return $cookies;
        });
    }

    /**
     * Init annotations.
     *
     * @param DI     $di     Dependency Injection.
     * @param Config $config Config object.
     *
     * @return void
     */
    protected function _initAnnotations(DI $di, EnConfig $config)
    {
        $di->set(
            'annotations',
            function () use ($config) {
                if (getenv('STAGE') === 'dev') {
                    $adapter = new PhAnnotationsMemory();
                } else {
                    $annotationsAdapter = '\Phalcon\Annotations\Adapter\Files';
                    $adapter = new $annotationsAdapter([
                        'annotationsDir' => ROOT_PATH . $config->default->annotations->annotationsDir
                    ]);
                }

                return $adapter;
            },
            true
        );
    }

    /**
     * Init MySQL database.
     *
     * @param DI            $di            Dependency Injection.
     * @param Config        $config        Config object.
     * @param EventsManager $eventsManager Event manager.
     *
     * @return Pdo
     */
    protected function _initDb(DI $di, EnConfig $config, PhEventsManager $eventsManager): PhMysql
    {
        /** @var Pdo $connection */
        $connection = new \Phalcon\Db\Adapter\Pdo\Mysql([
            'host' => getenv('DB_HOST'),
            'port' => getenv('DB_PORT'),
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'dbname' => getenv('DB_NAME'),
            'dialectClass' => EnMysqlExtended::class,
            'charset' => 'utf8'
        ]);

        $di->set('db', $connection);
        $di->set(
            'modelsManager',
            function () use ($config, $eventsManager) {
                $modelsManager = new PhModelsManager();
                $modelsManager->setEventsManager($eventsManager);

                // Attach a listener to models-manager
                $eventsManager->attach('modelsManager', new ModelAnnotationsInitializer());

                return $modelsManager;
            },
            true
        );

        /**
         * If the configuration specify the use of metadata adapter use it or use memory otherwise.
         */
        $di->set(
            'modelsMetadata',
            function () use ($config) {
                if (getenv('STAGE') === 'dev') {
                    $metaData = new PhMetadataMemory();
                } else {
                    $metadataAdapter = '\Phalcon\Mvc\Model\Metadata\Files';
                    $metaData = new $metadataAdapter([
                        'metaDataDir' => ROOT_PATH . $config->default->metadata->metaDataDir
                    ]);
                }

                $metaData->setStrategy(new PhStrategyAnnotations());

                return $metaData;
            },
            true
        );

        return $connection;
    }

    /**
     * Init Backend cache.
     *
     * @param DI     $di     Dependency Injection.
     * @param Config $config Config object.
     *
     * @return void
     */
    protected function _initCache(DI $di, EnConfig $config)
    {
        if (getenv('STAGE') === 'dev') {
            // Create a dummy cache for system.
            // System will work correctly and the data will be always current for all adapters.
            $dummyCache = new EnCacheDummy(null);
            $di->set('viewCache', $dummyCache);
            $di->set('cacheOutput', $dummyCache);
            $di->set('cacheData', $dummyCache);
            $di->set('modelsCache', $dummyCache);
        } else {
            // Get the parameters.
            $cacheDataAdapter = '\Phalcon\Cache\Backend\\' . $config->default->cache->adapter;
            $frontEndOptions = ['lifetime' => $config->default->cache->lifetime];
            $backEndOptions = $config->default->cache->client->toArray();
            $frontOutputCache = new PhCacheOutput($frontEndOptions);
            $frontDataCache = new PhCacheData($frontEndOptions);

            $cacheOutputAdapter = new $cacheDataAdapter($frontOutputCache, $backEndOptions);
            $di->set('viewCache', $cacheOutputAdapter, true);
            $di->set('cacheOutput', $cacheOutputAdapter, true);

            $cacheDataAdapter = new $cacheDataAdapter($frontDataCache, $backEndOptions);
            $di->set('cacheData', $cacheDataAdapter, true);
            $di->set('modelsCache', $cacheDataAdapter, true);
        }
    }

    /**
     * Init router.
     *
     * @param DI     $di     Dependency Injection.
     * @param Config $config Config object.
     *
     * @return Router
     */
    protected function _initRouter(DI $di, Enconfig $config): PhRouter
    {
        $cacheData = $di->get('cacheData');
        $router = $cacheData->get(EnCacheSystem::CACHE_KEY_ROUTER_DATA);

        if ($router == null) {
            $saveToCache = ($router === null);

            // Load all controllers of all modules for routing system.
            $modules = $di->get('registry')->modules;

            // Use the annotations router.
            $router = new PhRouterAnnotations(false);

            /**
             * Load all route from router file
             */
            foreach ($modules as $module) {
                $moduleName = ucfirst($module);

                // Get all file names.
                $moduleDir = opendir($di->get('registry')->directories->modules . $moduleName . '/Controller');
                while ($file = readdir($moduleDir)) {
                    if (preg_match('/^[V]{1}[0-9]+/', $file)) {
                        $versionDir = array_diff(
                            scandir($di->get('registry')->directories->modules . $moduleName . '/Controller/' . $file),
                            array('..', '.')
                        );

                        foreach ($versionDir as $versionController) {
                            $controllerVersion = $moduleName . '\Controller\\' . $file . '\\' . str_replace('Controller.php', '', $versionController);
                            $router->addModuleResource(strtolower($module), $controllerVersion);
                        }
                    }

                    if ($file == "." || $file == ".." || strpos($file, 'Controller.php') === false) {
                        continue;
                    }
                }
                closedir($moduleDir);
            }

            if ($saveToCache) {
                $cacheData->save(EnCacheSystem::CACHE_KEY_ROUTER_DATA, $router, 2592000); // 30 days cache
            }
        }

        $router->removeExtraSlashes(true);
        $di->set('router', $router);

        return $router;
    }

    /**
     * Init security.
     *
     * @param DI     $di     Dependency Injection.
     *
     * @return void
     */
    protected function _initSecurity(DI $di)
    {
        $di->set('security', function () {
            $security = new PhSecurity();
            $security->setWorkFactor(10);

            return $security;
        });
    }

    /**
     * Init response transformer.
     *
     * @param DI $di Dependency Injection.
     *
     * @return void
     */
    protected function _initTransformer(DI $di)
    {
        $di->setShared('transformer', function () {
            $fractal = new FractalManager();
            $fractal->setSerializer(new EnFractalSerializer());

            return $fractal;
        });
    }

    /**
     * Init file manager.
     *
     * @param DI $di Dependency Injection.
     *
     * @return void
     */
    protected function _initFile(DI $di)
    {
        $di->setShared('file', function () {
            $cache = null;
            $filesystem = new FlySystem(
                new FlyLocalAdapter(ROOT_PATH),
                $cache
            );

            return $filesystem;
        });
    }

    /**
     * Init Beanstalkd Queue.
     *
     * @param DI     $di     Dependency Injection.
     * @param Config $config Config object.
     *
     * @return void
     */
    protected function _initQueue(DI $di, EnConfig $config)
    {
        $di->set(
            'queue',
            function () use ($config) {
                $beanstalk = new PhBeanstalk([
                    'host' => getenv('QUEUE_HOST'),
                    'port' => getenv('QUEUE_PORT'),
                    'prefix' => $config->default->prefix,
                    'persistent' => true
                ]);

                return $beanstalk;
            },
            true
        );
    }

    /**
     * Init Sphinx Search Egnine.
     *
     * @param DI     $di     Dependency Injection.
     * @param Config $config Config object.
     *
     * @return void
     */
    protected function _initSphinx(DI $di, EnConfig $config)
    {
        $di->set(
            'sphinx',
            function () use ($config) {
                $serviceManagerConfig = new SphinxConfig([
                    'factories' => [
                        'SphinxSearch\Db\Adapter\Adapter' => 'SphinxSearch\Db\Adapter\AdapterServiceFactory'
                    ],
                    'aliases' => [
                        'sphinxql' => 'SphinxSearch\Db\Adapter\Adapter'
                    ]
                ]);
                $serviceManager = new SphinxServiceManager();
                $serviceManagerConfig->configureServiceManager($serviceManager);
                $serviceManager->setService('Config', [
                    'sphinxql' => [
                        'driver'    => 'pdo_mysql',
                        'hostname'  => getenv('SPHINX_HOST'),
                        'port'      => getenv('SPHINX_PORT'),
                        'charset'   => 'UTF8'
                    ]
                ]);

                return $serviceManager;
            },
            true
        );
    }

    /**
     * Init slack.
     *
     * @param DI     $di     Dependency Injection.
     * @param Config $config Config object.
     *
     * @return void
     */
    protected function _initSlack(DI $di, EnConfig $config)
    {
        $di->set('slack', function () use ($config) {
            $webhookUrl = getenv('SLACK_WEBHOOK_URL');
            $channel = '#web-logger';
            $username = 'monkey-bot';
            $iconEmoji = ':monkey_face:';

            $formatter = new LineFormatter($config->default->logger->format, "j/n/Y g:i a");
            $stream = new SlackWebhookHandler($webhookUrl, $channel, $username, true, $iconEmoji, false, false, MoLogger::DEBUG, true, array());
            $stream->setFormatter($formatter);

            $logger = new MoLogger('bot');
            $logger->pushHandler($stream);

            return $logger;
        }, false);
    }
}
