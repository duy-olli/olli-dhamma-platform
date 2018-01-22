<?php
namespace Engine\Console\Command;

use Engine\Console\AbstractCommand;
use Engine\Interfaces\CommandInterface;
use Engine\Console\ConsoleUtil;
use Phalcon\DI;

/**
 * Test command.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://olli.vn/
 *
 * @CommandName(['test'])
 * @CommandDescription('Test command controller.')
 */
class Test extends AbstractCommand implements CommandInterface
{
    /**
     * Test action with params.
     *
     * @param string|null $param1 Param1 - string. Example: "string".
     * @param bool        $param2 Param2 is flag.
     *
     * @return void
     */
    public function testAction($param1 = null, $param2 = null)
    {
        print ConsoleUtil::success('Test command success - param1: '. $param1 .' - param2: '. $param2 .'.') . PHP_EOL;
    }
}
