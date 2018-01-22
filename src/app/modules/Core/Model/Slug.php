<?php
namespace Core\Model;

use Engine\Db\AbstractModel;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

/**
 * Slug Model.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://olli.vn/
 *
 * @Source('fly_slug');
 * @Behavior('\Engine\Behavior\Model\Timestampable');
 */
class Slug extends AbstractModel
{
    /**
    * @Primary
    * @Identity
    * @Column(type="integer", nullable=false, column="s_id")
    */
    public $id;

    /**
    * @Column(type="string", nullable=true, column="s_name")
    */
    public $name;

    /**
    * @Column(type="string", nullable=true, column="s_model")
    */
    public $model;

    /**
    * @Column(type="string", nullable=true, column="s_object_id")
    */
    public $objectid;

    /**
    * @Column(type="integer", nullable=true, column="s_date_created")
    */
    public $datecreated;

    /**
    * @Column(type="integer", nullable=true, column="s_date_modified")
    */
    public $datemodified;

    /**
     * Initialize model
     */
    public function initialize()
    {
    }

    /**
     * Form field validation
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add('name', new PresenceOf([
            'message' => 'message-name-notempty'
        ]));

        $validator->add('name', new Uniqueness([
            'message' => 'message-name-must-unique'
        ]));

        return $this->validate($validator);
    }
}
