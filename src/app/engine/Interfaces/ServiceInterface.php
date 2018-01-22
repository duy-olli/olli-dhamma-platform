<?php
namespace Engine\Interfaces;

use Phalcon\DiInterface;

/**
 * Service interface.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://olli.vn/
 */
interface ServiceInterface
{
    /**
     * Create api.
     *
     * @param DiInterface $di        Dependency Service.
     * @param array       $arguments Api arguments.
     */
    public function __construct(DiInterface $di, $arguments);
}
