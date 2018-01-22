<?php
namespace Dashboard\Model;

use Core\Helper\Utils as Helper;

/**
 * Log Model.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://olli.vn/
 */
class Log
{
    public $date;
    public $logger;
    public $level;
    public $message;
    public $context;

    public function getLevelStyle(): string
    {
        $class = '';
        switch ($this->level) {
            case 'ERROR':
                $class = 'danger';
                break;
            case 'INFO':
                $class = 'primary';
                break;
            case 'DEBUG':
                $class = 'warning';
                break;
        }

        return $class;
    }
}
