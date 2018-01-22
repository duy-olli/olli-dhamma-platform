<?php
namespace Engine\Interfaces;

use Phalcon\DI;
use Phalcon\DiInterface;
use Phalcon\Events\Manager as PhEventsManager;

/**
 * Bootstrap interface.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://olli.vn/
 */
interface BootstrapInterface
{
    /**
     * Create Bootstrap.
     *
     * @param DiInterface $di Dependency injection.
     * @param Manager     $em Events manager.
     */
    public function __construct(DI $di, PhEventsManager $em);

    /**
     * Register module services.
     *
     * @return void
     */
    public function registerServices();
}
