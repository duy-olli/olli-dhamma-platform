<?php
namespace User\Model;

use Engine\Db\AbstractModel;
use Engine\Behavior\Model\Imageable;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

/**
 * User Confirmation Code Model.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://olli.vn/
 *
 * @Source('fly_user_confirmation_code');
 * @Behavior('\Engine\Behavior\Model\Timestampable');
 */
class UserConfirmationCode extends AbstractModel
{
    /**
    * @Column(type="integer", nullable=true, column="u_id")
    */
    public $uid;

    /**
    * @Primary
    * @Identity
    * @Column(type="integer", nullable=false, column="ucc_id")
    */
    public $id;

    /**
    * @Column(type="integer", nullable=true, column="ucc_code")
    */
    public $code;

    /**
    * @Column(type="integer", nullable=true, column="ucc_valid")
    */
    public $valid;

    /**
    * @Column(type="integer", nullable=true, column="ucc_type")
    */
    public $type;

    /**
    * @Column(type="integer", nullable=true, column="ucc_date_created")
    */
    public $datecreated;

    /**
    * @Column(type="integer", nullable=true, column="ucc_date_modified")
    */
    public $datemodified;

    const IS_VALID = 1;
    const IS_INVALID = 3;
    const TYPE_ACTIVATE = 1;
    const TYPE_FORGOT = 3;
    const TYPE_UPDATE = 5;
}
