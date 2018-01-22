<?php
namespace Engine\Interfaces;

use Phalcon\DI;

/**
 * Command interface.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://olli.vn/
 */
interface CommandInterface
{
    /**
     * Get command name.
     *
     * @return string
     */
    public function getName();

    /**
     * Prints help on the usage of the command.
     *
     * @return void
     */
    public function getHelp();

    /**
     * Dispatch command. Find out action and exec it with parameters.
     *
     * @return mixed
     */
    public function dispatch();
}
