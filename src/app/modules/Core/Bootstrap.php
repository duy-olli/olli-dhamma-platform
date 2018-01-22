<?php
namespace Core;

use Phalcon\DI;
use Phalcon\DiInterface;
use Phalcon\Events\Manager as PhEventsManager;
use Engine\Bootstrap as EnBootstrap;
use Engine\Session\JWT as EnJWT;
use Engine\Constants\AccountType as EnAccountType;
use Firebase\JWT\JWT as FirebaseJWT;
use User\Plugin\Account\Email as UserEmailAccount;
use User\Plugin\Account\Facebook as UserFacebookAccount;
use User\Plugin\Account\Google as UserGoogleAccount;
use User\Plugin\AuthManager as UserAuthManager;

/**
 * Api Bootstrap.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://olli.vn/
 */
class Bootstrap extends EnBootstrap
{
    /**
     * Current module name.
     *
     * @var string
     */
    protected $_moduleName = 'Core';

    /**
     * Bootstrap construction.
     *
     * @param DiInterface $di Dependency injection.
     * @param Manager     $em Events manager object.
     */
    public function __construct(DI $di, PhEventsManager $em)
    {
        parent::__construct($di, $em);

        $di->setShared('auth', function () use ($di) {
            $sessionManager = new EnJWT(new FirebaseJWT());
            $authManager = new UserAuthManager($sessionManager);

            // 1. Instantiate Account Type
            $authEmail = new UserEmailAccount(EnAccountType::EMAIL);
            $authFacebook = new UserFacebookAccount(EnAccountType::FACEBOOK);
            $authGoogle = new UserGoogleAccount(EnAccountType::GOOGLE);

            $authManager->setGenSalt(getenv('AUTH_SALT'));

            return $authManager
                ->addAccount(EnAccountType::EMAIL, $authEmail)
                ->addAccount(EnAccountType::FACEBOOK, $authFacebook)
                ->addAccount(EnAccountType::GOOGLE, $authGoogle)
                ->setExpireTime(getenv('AUTH_EXPIRE'));
        });

        /**
         * Attach this bootstrap for all application initialization events.
         */
        $em->attach('init', $this);
    }

    /**
     * Init some subsystems after engine initialization.
     */
    public function afterEngine()
    {
        $di = $this->getDI();

        $this->getEventsManager()->attach('dispatch', $di->get('core')->translator());
        $this->getEventsManager()->attach('dispatch', $di->get('core')->transformer());
        $this->getEventsManager()->attach('dispatch', $di->get('core')->authentication());
        $this->getEventsManager()->attach('dispatch', $di->get('core')->authorization());
    }
}
