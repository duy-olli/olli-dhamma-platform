<?php

namespace Engine;

use Phalcon\DI;
use Phalcon\Exception as PhException;

use Monolog\Logger as MoLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

/**
 * Default system exception.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://olli.vn/
 */
class Exception extends \Exception
{
    /**
     * Create exception.
     *
     * @param int        $code     Exception code.
     * @param string     $message  Exception message.
     * @param array      $args     Message arguments (for placeholders, sprintf).
     * @param \Exception $previous Previous exception.
     */
    public function __construct($code = 0, $args = [], $message = "", \Exception $previous = null)
    {
        parent::__construct(vsprintf($message, $args), $code, $previous);
    }

    /**
     * Log exception.
     *
     * @param \Exception $e Exception object.
     *
     * @return string
     */
    public static function log($e, $context)
    {
        // check if is fatal error
        if ($e->getMessage() != '') {
            $config = DI::getDefault()->get('config');

            $formatter = new LineFormatter($config->default->logger->format, "j/n/Y g:i a");
            $stream = new StreamHandler(ROOT_PATH . $config->default->logger->path .  date('Y-m-d') . '.log', MoLogger::DEBUG);
            $stream->setFormatter($formatter);

            $logger = new MoLogger($context);
            $logger->pushHandler($stream);

            $logger->error($e->getMessage(), [
               'file' => $e->getFile(),
               'line' => $e->getLine()
           ]);
        }
    }
}
