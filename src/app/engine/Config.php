<?php
namespace Engine;

use Phalcon\Config as PhConfig;
use \Dotenv\Dotenv;
use Yosymfony\Toml\Toml;

/**
 * Application config.
 *
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://ketchai.com/
 */
class Config extends PhConfig
{
    const CONFIG_CACHE_PATH = '/app/storage/cache/data/config.php';

    /**
     * Current config stage.
     * @var string
     */
    private $_currentStage;

    /**
     * Create configuration object.
     *
     * @param array|null  $arrayConfig Configuration data.
     * @param string|null $stage       Configuration stage.
     */
    public function __construct(array $arrayConfig, string $stage)
    {
        $this->_currentStage = $stage;
        parent::__construct($arrayConfig);
    }

    /**
     * Load configuration according to selected stage.
     *
     * @param string $stage Configuration stage.
     *
     * @return Config
     */
    public static function factory(): PhConfig
    {
        if (file_exists(self::CONFIG_CACHE_PATH) && getenv('STAGE') == 'prod') {
            $config = new Config(
                include_once(self::CONFIG_CACHE_PATH),
                getenv('STAGE')
            );
        } else {
            $config = self::_getConfiguration(getenv('STAGE'));
            $config->refreshCache();
        }

        return $config;
    }

    /**
     * Load configuration from all files.
     *
     * @param string $stage Configuration stage.
     *
     * @throws Exception
     * @return Config
     */
    protected static function _getConfiguration(string $stage): PhConfig
    {
        $config = new Config([], $stage);
        $configDirectory = ROOT_PATH . '/app/config';

        $configFiles = glob($configDirectory .'/*.toml');

        foreach ($configFiles as $file) {
            $data = Toml::Parse($file);
            $config->offsetSet(basename($file, '.toml'), $data);
        }

        // load secure env config
        $dotenv = new Dotenv($configDirectory, '.env.' . $stage);
        $dotenv->load();

        return $config;
    }

    /**
     * Save config file into cached config file.
     *
     * @return void
     */
    public function refreshCache()
    {
        file_put_contents(ROOT_PATH . self::CONFIG_CACHE_PATH, $this->_toConfigurationString());
    }

    /**
     * Save application config to file.
     *
     * @param array|null $data Configuration data.
     *
     * @return void
     */
    protected function _toConfigurationString($data = null)
    {
        if (!$data) {
            $data = $this->toArray();
        }

        $configText = var_export($data, true);

        // Fix pathes. This related to windows directory separator.
        $configText = str_replace('\\\\', DS, $configText);

        $configText = str_replace("'" . ROOT_PATH, "ROOT_PATH . '", $configText);
        $headerText = '<?php
/**
* WARNING
*
* Manual changes to this file may cause a malfunction of the system.
* Be careful when changing settings!
*
*/

return ';

        return $headerText . $configText . ';';
    }
}
