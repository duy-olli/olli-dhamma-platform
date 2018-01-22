<?php
namespace Engine;

use Engine\Console\ConsoleUtil;
use Phalcon\Mvc\Application as PhApp;
use Phalcon\DI\FactoryDefault as PhDi;
use Phalcon\Registry as PhRegistry;
use Phalcon\Events\Manager as PhEventsManager;

/**
 * Console class.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://olli.vn/
 */
class Cli extends PhApp
{
    use Init;

    protected $_config;

    /**
     * Loaders for different modes.
     *
     * @var array
     */
    private $_loaders = [
        'engine',
        'annotations',
        'db',
        'cache',
        'router',
        'security',
        'file',
        'queue',
        'sphinx',
        'slack'
    ];

    /**
     * Defined engine commands.
     *
     * @var AbstractCommand[]
     */
    private $_commands = [];

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
        /**
         * Set application main objects.
         */
        $di = $this->_dependencyInjector;

        // set app service to check app is in cli mode or not
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

        // Init commands.
        $this->_initCommands();
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
     * Init commands.
     *
     * @return void
     */
    protected function _initCommands()
    {
        // Get engine commands.
        $this->_getCommandsFrom(
            $this->getDI()->get('registry')->directories->engine . '/Console/Command',
            'Engine\Console\Command\\'
        );

        // Get modules commands.
        foreach ($this->getDI()->get('registry')->modules as $module) {
            $module = ucfirst($module);
            $path = $this->getDI()->get('registry')->directories->modules . $module . '/Command';
            $namespace = $module . '\Command\\';
            $this->_getCommandsFrom($path, $namespace);
        }
    }

    /**
     * Get commands located in directory.
     *
     * @param string $commandsLocation  Commands location path.
     * @param string $commandsNamespace Commands namespace.
     *
     * @return void
     */
    protected function _getCommandsFrom($commandsLocation, $commandsNamespace)
    {
        if (!file_exists($commandsLocation)) {
            return;
        }

        // Get all file names.
        $files = scandir($commandsLocation);

        // Iterate files.
        foreach ($files as $file) {
            if ($file == "." || $file == "..") {
                continue;
            }

            $commandClass = $commandsNamespace . str_replace('.php', '', $file);
            $this->_commands[] = new $commandClass($this->getDI());
        }
    }

    /**
     * Handle all data and output result.
     *
     * @throws Exception
     * @return mixed
     */
    public function getOutput()
    {
        print ConsoleUtil::infoLine(
            'Commands Manager',
            false,
            1
        );

        // Not arguments?
        if (!isset($_SERVER['argv'][1])) {
            $this->printAvailableCommands();
            die();
        }

        // Check if 'help' command was used.
        if ($this->_helpIsRequired()) {
            return;
        }

        // Try to dispatch the command.
        if ($cmd = $this->_getRequiredCommand()) {
            return $cmd->dispatch();
        }

        // Check for alternatives.
        $available = [];
        foreach ($this->_commands as $command) {
            $providedCommands = $command->getCommands();
            foreach ($providedCommands as $command) {
                $soundex = soundex($command);
                if (!isset($available[$soundex])) {
                    $available[$soundex] = [];
                }
                $available[$soundex][] = $command;
            }
        }

        // Show exception with/without alternatives.
        $soundex = soundex($_SERVER['argv'][1]);
        if (isset($available[$soundex])) {
            print ConsoleUtil::warningLine(
                'Command "' . $_SERVER['argv'][1] .
                '" not found. Did you mean: ' . join(' or ', $available[$soundex]) . '?'
            );
            $this->printAvailableCommands();
        } else {
            print ConsoleUtil::warningLine('Command "' . $_SERVER['argv'][1] . '" not found.');
            $this->printAvailableCommands();
        }
    }

    /**
     * Output available commands.
     *
     * @return void
     */
    public function printAvailableCommands()
    {
        print ConsoleUtil::headLine('Available commands:');
        foreach ($this->_commands as $command) {
            print ConsoleUtil::commandLine(join(', ', $command->getCommands()), $command->getDescription());
        }
        print PHP_EOL;
    }

    /**
     * Get required command.
     *
     * @param string|null $input Input from console.
     *
     * @return AbstractCommand|null
     */
    protected function _getRequiredCommand($input = null)
    {
        if (!$input) {
            $input = $_SERVER['argv'][1];
        }

        foreach ($this->_commands as $command) {
            $providedCommands = $command->getCommands();
            if (in_array($input, $providedCommands)) {
                return $command;
            }
        }

        return null;
    }

    /**
     * Check help system.
     *
     * @return bool
     */
    protected function _helpIsRequired()
    {
        if ($_SERVER['argv'][1] != 'help') {
            return false;
        }

        if (empty($_SERVER['argv'][2])) {
            $this->printAvailableCommands();
            return true;
        }

        $command = $this->_getRequiredCommand($_SERVER['argv'][2]);
        if (!$command) {
            print ConsoleUtil::warningLine('Command "' . $_SERVER['argv'][2] . '" not found.');
            return true;
        }

        $command->getHelp((!empty($_SERVER['argv'][3]) ? $_SERVER['argv'][3] : null));
        return true;
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
