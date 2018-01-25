<?php
namespace Dhamma\Controller\V1;

use Core\Controller\AbstractController;
use Engine\UserException;
use Engine\Constants\ErrorCode;
use Core\Helper\Utils as Helper;

/**
 * Dhamma api.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://olli.vn/
 *
 * @RoutePrefix("/v1/dhammas")
 */
class IndexController extends AbstractController
{
    protected $recordPerPage = 50;

    /**
     * Get all
     *
     * @Route("/", methods={"GET"})
     */
    public function listAction()
    {
        die('a');
    }
}
