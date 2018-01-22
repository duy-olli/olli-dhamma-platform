<?php
namespace Engine\Behavior\Model;

use Phalcon\Mvc\Model\Behavior;
use Phalcon\Mvc\Model\BehaviorInterface;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\ModelInterface;

/**
 * Timestamp behavior.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://olli.vn/
 */
class Timestampable extends Behavior implements BehaviorInterface
{
    public function __construct($options = null)
    {
    }

    public function notify($eventType, ModelInterface $model)
    {
        switch ($eventType) {
            case 'beforeCreate':
                $model->datecreated = time();
                break;
            case 'beforeUpdate':
                $model->datemodified = time();
                break;
        }
    }

    /**
     * @throws \Exception
     */
    public function missingMethod(ModelInterface $model, $method, $arguments = null)
    {
        if (!method_exists($this, $method)) {
            return null;
        }

        if (!$this->db) {
            if ($model->getDi()->has('db')) {
                $this->db = $model->getDi()->get('db');
            } else {
                throw new \Exception('Undefined database handler.');
            }
        }

        $this->setOwner($model);
        $result = call_user_func_array(array($this, $method), $arguments);
        if ($result === null) {
            return '';
        }

        return $result;
    }
}
