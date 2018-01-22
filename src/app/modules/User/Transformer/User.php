<?php
namespace User\Transformer;

use League\Fractal\TransformerAbstract;
use User\Model\User as UserModel;
use Phalcon\Di;

/**
 * User Transformer.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://olli.vn/
 */
class User extends TransformerAbstract
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
    public function transform(UserModel $user)
    {
        $humandatecreated = new \Moment\Moment($user->datecreated);

        return [
            'id' => (string) $user->id,
            'fullname' => (string) $user->fullname,
            'screenname' => (string) $user->screenname,
            'email' => (string) $user->email,
            'groupid' => (string) $user->groupid,
            'status' =>  [
                'label' => (string) $user->getStatusName(),
                'value' => (string) $user->status,
                'style' => (string) $user->getStatusStyle()
            ],
            'verify' => [
                'label' => (string) $user->getVerifyName(),
                'value' => (string) $user->isverified,
                'style' => (string) $user->getVerifyStyle()
            ],
            'verifytype' => [
                'label' => (string) $user->getVerifyTypeName(),
                'value' => (string) $user->verifytype
            ],
            'avatar' => (string) $user->getAvatarJson(),
            'mobilenumber' => (string) $user->mobilenumber,
            'datecreated' => (string) $user->datecreated,
            'humandatecreated' => (string) $humandatecreated->format('l, d M Y H:i')
        ];
    }
}
