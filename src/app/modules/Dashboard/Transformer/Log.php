<?php
namespace Dashboard\Transformer;

use League\Fractal\TransformerAbstract;
use Phalcon\Di;
use Dashboard\Model\Log as LogModel;

/**
 * Log Transformer.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://olli.vn/
 */
class Log extends TransformerAbstract
{
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
    ];

    /**
     * Turn this resource object into a generic array
     *
     * @return array
     */
    public function transform(LogModel $log)
    {
        return [
            'date' => (string) $log->date,
            'logger' => (string) $log->logger,
            'level' =>  [
                'label' => (string) $log->level,
                'value' => (string) $log->level,
                'style' => (string) $log->getLevelStyle()
            ],
            'message' => (string) $log->message,
            'context' => (object) $log->context
        ];
    }
}
