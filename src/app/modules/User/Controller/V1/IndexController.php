<?php
namespace User\Controller\V1;

use Core\Controller\AbstractController;
use Engine\UserException;
use Engine\Constants\ErrorCode;
use User\Model\User as UserModel;
use User\Model\UserConfirmationCode as UserConfirmationCodeModel;
use User\Transformer\User as UserTransformer;
use Core\Helper\Utils as Helper;
use Event\Model\RelUserEvent as RelUserEventModel;
use Event\Model\Event as EventModel;

/**
 * User api.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://olli.vn/
 *
 * @RoutePrefix("/v1/users")
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
        $page = (int) $this->request->getQuery('page', null, 1);
        $formData = [];
        $hasMore = true;

        // Search keyword in specified field model
        $searchKeywordInData = [
            'email',
            'fullname',
            'mobilenumber'
        ];
        $page = (int) $this->request->getQuery('page', null, 1);
        $orderBy = (string) $this->request->getQuery('orderby', null, 'id');
        $orderType = (string) $this->request->getQuery('ordertype', null, 'desc');
        $keyword = (string) $this->request->getQuery('keyword', null, '');

        // optional Filter
        $status = (int) $this->request->getQuery('status', null, 0);
        $verifytype = (int) $this->request->getQuery('verifytype', null, 0);
        $groupid = (string) $this->request->getQuery('groupid', null, '');

        $formData['columns'] = '*';
        $formData['conditions'] = [
            'keyword' => $keyword,
            'searchKeywordIn' => $searchKeywordInData,
            'filterBy' => [
                'status' => $status,
                'verifytype' => $verifytype,
                'groupid' => $groupid
            ]
        ];
        $formData['orderBy'] = $orderBy;
        $formData['orderType'] = $orderType;

        $myUsers = UserModel::paginate($formData, $this->recordPerPage, $page);

        if ($myUsers->total_pages > 0) {
            if ($page == $myUsers->total_pages) {
                $hasMore = false;
            }

            return $this->createCollection(
                $myUsers->items,
                new UserTransformer,
                'records',
                [
                    'meta' => [
                        'recordPerPage' => $this->recordPerPage,
                        'hasMore' => $hasMore,
                        'totalItems' => $myUsers->total_items,
                        'orderBy' => $orderBy,
                        'orderType' => $orderType,
                        'page' => $page
                    ]
                ]
            );
        } else {
            return $this->respondWithArray([], 'records');
        }
    }

    /**
     * Get single
     *
     * @Route("/{id:[0-9]+}", methods={"GET"})
     */
    public function getAction(int $id = 0)
    {
        $myUser = UserModel::findFirst([
            'id = :id:',
            'bind' => ['id' => (int) $id]
        ]);

        if (!$myUser) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        return $this->createItem(
            $myUser,
            new UserTransformer,
            'response'
        );
    }

    /**
     * Update single
     *
     * @Route("/{id:[0-9]+}", methods={"PUT"})
     */
    public function updateAction(int $id = 0)
    {
        $formData = (array) $this->request->getJsonRawBody();

        $myUser = UserModel::findFirst([
            'id = :id:',
            'bind' => ['id' => (int) $id]
        ]);

        if (!$myUser) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        $myUser->fullname = (string) $formData['fullname'];
        $myUser->groupid = (string) $formData['groupid'];
        $myUser->status = (int) $formData['status'];
        $myUser->verifytype = (int) $formData['verifytype'];

        if (!$myUser->update()) {
            throw new UserException(ErrorCode::USER_UPDATE_FAIL);
        }

        return $this->createItem(
            $myUser,
            new UserTransformer,
            'response'
        );
    }

    /**
     * Login user action.
     *
     * @Route("/login/{account:[a-z]{1,10}}", methods={"POST"})
     */
    public function loginAction($account)
    {
        $formData = (array) $this->request->getJsonRawBody();
        $email = trim($formData['email']);
        $password = $formData['password'];

        if (strlen($email) == 0) {
            throw new UserException(ErrorCode::AUTH_NOEMAIL);
        };

        if (count($password) == 0) {
            throw new UserException(ErrorCode::AUTH_NOPASSWORD);
        };

        $this->auth->login($account, $email, $password);

        // Generate jwt authToken for valid user.
        $tokenResponse = $this->auth->getTokenResponse();

        return $this->respondWithArray($tokenResponse, 'response');
    }

    /**
     * Register user.
     *
     * @Route("/register", methods={"POST"})
     */
    public function registerAction()
    {
        $formData = (array) $this->request->getJsonRawBody();

        if (!self::checkUniqueEmail($formData)) {
            throw new UserException(ErrorCode::USER_REG_UNUNIQUEEMAIL);
        }

        if (!self::comparePassword($formData)) {
            throw new UserException(ErrorCode::USER_PASSWORD_NOT_MATCH);
        }

        // send activate email
        $mailContent = $this->file->read('mail/default/confirmation_code.html');
        $confirmationCode = mt_rand(100000, 999999);

        $authActivateUrl = getenv('AUTH_ACTIVATE_URL') . '?e=' . trim($formData['email']) . '&c=' . $confirmationCode;
        $mailContent = str_replace('[CLIENT.BASEURL]', $this->url->getBaseUri(), $mailContent);
        $mailContent = str_replace('[CLIENT.URL]', $authActivateUrl, $mailContent);
        $mailContent = str_replace('[CLIENT.CONFIRMATION_CODE]', $confirmationCode, $mailContent);

        $mailSended = Helper::sendMailgun(
            trim($formData['email']),
            $confirmationCode . ' is activate code',
            $mailContent,
            [
                'type' => 'register',
                'code' => $confirmationCode
            ]
        );

        if (!$mailSended) {
            throw new UserException(ErrorCode::USER_SEND_ACTIVATE_MAIL_FAIL);
        }

        $myUser = new UserModel();
        $myUser->email = (string) trim($formData['email']);
        $myUser->password = (string) $this->security->hash($formData['password']);
        $myUser->status = (int) UserModel::STATUS_ENABLE;
        $myUser->groupid = (string) $this->config->permission->groups->defaultOauth;
        $myUser->verifytype = (int) UserModel::VERIFY_TYPE_EMAIL;
        $myUser->isverified = (int) UserModel::IS_NOT_VERIFIED;
        $myUser->oauthprovider = 'email';
        $myUser->datelastchangepassword = (int) time();

        if (!$myUser->create()) {
            throw new UserException(ErrorCode::USER_CREATE_FAIL);
        }

        return $this->createItem(
            $myUser,
            new UserTransformer,
            'response'
        );
    }

    /**
     * Webhook delivered register user to add confirmation code to database.
     *
     * @Route("/register/delivered", methods={"POST"})
     */
    public function registerdeliveredAction()
    {
        $event = (string) $this->request->getPost('event', null, '');
        $recipient = (string) $this->request->getPost('recipient', null, '');
        $type = (string) $this->request->getPost('type', null, '');
        $code = (int) $this->request->getPost('code', null, 0);
        $signature = (string) $this->request->getPost('signature', null, '');
        $timestamp = (string) $this->request->getPost('timestamp', null, '');
        $token = (string) $this->request->getPost('token', null, '');

        // generate signature based in api key to verify webhook
        $mySignature = hash_hmac('sha256', $timestamp . $token, getenv('MAILGUN_API_KEY'));

        if ($signature === $mySignature) {
            if ($event == 'delivered') {
                // $this->logger->error('delivered', [$type]);
                switch ($type) {
                    case 'register':
                        $myUser = UserModel::findFirst([
                            'email = :email: AND isverified = :isverified: AND status = :status:',
                            'bind' => [
                                'email' => (string) trim($recipient),
                                'isverified' => (int) UserModel::IS_NOT_VERIFIED,
                                'status' => (int) UserModel::STATUS_ENABLE
                            ]
                        ]);

                        if ($myUser) {
                            $myUserConfirmationCode = new UserConfirmationCodeModel();
                            $myUserConfirmationCode->assign([
                                'code' => $code,
                                'uid' => $myUser->id,
                                'valid' => UserConfirmationCodeModel::IS_VALID,
                                'type' => UserConfirmationCodeModel::TYPE_ACTIVATE
                            ]);

                            if (!$myUserConfirmationCode->create()) {
                                throw new UserException(ErrorCode::DATA_CREATE_FAIL);
                            }
                        }
                        break;
                    case 'forgot':
                        $myUser = UserModel::findFirst([
                            'email = :email: AND isverified = :isverified: AND status = :status:',
                            'bind' => [
                                'email' => (string) trim($recipient),
                                'isverified' => (int) UserModel::IS_VERIFIED,
                                'status' => (int) UserModel::STATUS_ENABLE
                            ]
                        ]);

                        if ($myUser) {
                            // Remove all forgot code of this user
                            $myOldCodes = UserConfirmationCodeModel::find([
                                'uid = :uid: AND type = :type:',
                                'bind' => [
                                    'uid' => $myUser->id,
                                    'type' => UserConfirmationCodeModel::TYPE_FORGOT
                                ]
                            ]);

                            if (count($myOldCodes) > 0) {
                                foreach ($myOldCodes as $oldCode) {
                                    $oldCode->delete();
                                }
                            }

                            $myUserForgotCode = new UserConfirmationCodeModel();
                            $myUserForgotCode->assign([
                                'code' => $code,
                                'uid' => $myUser->id,
                                'valid' => UserConfirmationCodeModel::IS_VALID,
                                'type' => UserConfirmationCodeModel::TYPE_FORGOT
                            ]);

                            if (!$myUserForgotCode->create()) {
                                throw new UserException(ErrorCode::DATA_CREATE_FAIL);
                            }
                        }
                        break;
                    case 'update_password':
                        $myUser = UserModel::findFirst([
                            'email = :email: AND isverified = :isverified: AND status = :status:',
                            'bind' => [
                                'email' => (string) trim($recipient),
                                'isverified' => (int) UserModel::IS_VERIFIED,
                                'status' => (int) UserModel::STATUS_ENABLE
                            ]
                        ]);

                        if ($myUser) {
                            // Remove all forgot code of this user
                            $myOldCodes = UserConfirmationCodeModel::find([
                                'uid = :uid: AND type = :type:',
                                'bind' => [
                                    'uid' => $myUser->id,
                                    'type' => UserConfirmationCodeModel::TYPE_UPDATE
                                ]
                            ]);

                            if (count($myOldCodes) > 0) {
                                foreach ($myOldCodes as $oldCode) {
                                    $oldCode->delete();
                                }
                            }

                            $myUserUpdatePasswordCode = new UserConfirmationCodeModel();
                            $myUserUpdatePasswordCode->assign([
                                'code' => $code,
                                'uid' => $myUser->id,
                                'valid' => UserConfirmationCodeModel::IS_VALID,
                                'type' => UserConfirmationCodeModel::TYPE_UPDATE
                            ]);

                            if (!$myUserUpdatePasswordCode->create()) {
                                throw new UserException(ErrorCode::DATA_CREATE_FAIL);
                            }
                        }
                        break;
                }
            }
        }

        return $this->respondWithOK();
    }

    /**
     * activate user.
     *
     * @Route("/activate", methods={"POST"})
     */
    public function activateAction()
    {
        $formData = (array) $this->request->getJsonRawBody();

        $myUser = UserModel::findFirst([
            'email = :email: AND isverified = :isverified: AND status = :status:',
            'bind' => [
                'email' => (string) trim($formData['email']),
                'isverified' => (int) UserModel::IS_NOT_VERIFIED,
                'status' => (int) UserModel::STATUS_ENABLE
            ]
        ]);

        if (!$myUser) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        $myUserConfirmationCode = UserConfirmationCodeModel::findFirst([
            'uid = :uid: AND code = :code: AND valid = :valid: AND type = :type:',
            'bind' => [
                'uid' => (int) $myUser->id,
                'code' => (int) trim($formData['code']),
                'valid' => UserConfirmationCodeModel::IS_VALID,
                'type' => UserConfirmationCodeModel::TYPE_ACTIVATE
            ]
        ]);

        if (!$myUserConfirmationCode) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        $myUserConfirmationCode->valid = UserConfirmationCodeModel::IS_INVALID;
        if (!$myUserConfirmationCode->update()) {
            throw new UserException(ErrorCode::DATA_UPDATE_FAIL);
        }

        $myUser->isverified = UserModel::IS_VERIFIED;
        if (!$myUser->update()) {
            throw new UserException(ErrorCode::DATA_UPDATE_FAIL);
        }

        // Add points
        $myEvents = EventModel::find([
            'status = :status:',
            'bind' => [
                'status' => EventModel::STATUS_ENABLE
            ]
        ]);

        if (count($myEvents) > 0) {
            foreach ($myEvents as $event) {
                $myRelUserEvent = new RelUserEventModel();
                $myRelUserEvent->assign([
                    'uid' => (int) $myUser->id,
                    'eid' => (int) $event->id,
                    'count' => (int) $event->maxaccumulation
                ]);

                if (!$myRelUserEvent->create()) {
                    throw new UserException(ErrorCode::DATA_CREATE_FAIL);
                }
            }
        }

        $this->auth->setIssuer('email');
        $this->auth->setUser($myUser);

        // Generate jwt authToken for activate user.
        $tokenResponse = $this->auth->getTokenResponse();

        return $this->respondWithArray($tokenResponse, 'response');
    }

    /**
     * Create user.
     *
     * @Route("/", methods={"POST"})
     */
    public function createAction()
    {
        $formData = (array) $this->request->getJsonRawBody();

        if (!self::checkUniqueEmail($formData)) {
            throw new UserException(ErrorCode::USER_REG_UNUNIQUEEMAIL);
        }

        // if (!self::checkValidGroup($formData)) {
        //     throw new UserException(ErrorCode::USER_REG_INVALID_GROUP);
        // }

        $myUser = new UserModel();
        $myUser->email = (string) $formData['email'];
        $myUser->fullname = (string) $formData['fullname'];
        $myUser->groupid = (string) $formData['groupid'];
        $myUser->password = (string) $this->security->hash($formData['password']);
        $myUser->status = (int) $formData['status'];
        $myUser->verifytype = (int) $formData['verifytype'];
        $myUser->isverified = UserModel::IS_NOT_VERIFIED;

        if (!$myUser->create()) {
            throw new UserException(ErrorCode::USER_CREATE_FAIL);
        }

        return $this->createItem(
            $myUser,
            new UserTransformer,
            'response'
        );
    }

    /**
     * Bulk action
     *
     * @Route("/bulk", methods={"POST"})
     */
    public function bulkAction()
    {
        $formData = (array) $this->request->getJsonRawBody();

        if (count($formData['itemSelected']) > 0 && $formData['actionSelected'] != '') {
            switch ($formData['actionSelected']) {
                case 'delete':
                    // Start a transaction
                    $this->db->begin();
                    foreach ($formData['itemSelected'] as $item) {
                        $myUser = UserModel::findFirst([
                            'id = :id:',
                            'bind' => ['id' => (int) $item->id]
                        ])->delete();
                        // If fail stop a transaction
                        if ($myUser == false) {
                            $this->db->rollback();
                            return;
                        }
                    }
                    // Commit a transaction
                    if ($this->db->commit() == false) {
                        throw new UserException(ErrorCode::DATA_BULK_FAILED);
                    }

                    break;
                case 'enable':
                    $this->db->begin();
                    foreach ($formData['itemSelected'] as $item) {
                        $myUser = UserModel::findFirst([
                            'id = :id:',
                            'bind' => ['id' => (int) $item->id]
                        ]);
                        $myUser->status = UserModel::STATUS_ENABLE;

                        if (!$myUser->update()) {
                            $this->db->rollback();
                            return;
                        }
                    }

                    if ($this->db->commit() == false) {
                        throw new UserException(ErrorCode::DATA_BULK_FAILED);
                    }

                    break;
                case 'disable':
                    $this->db->begin();
                    foreach ($formData['itemSelected'] as $item) {
                        $myUser = UserModel::findFirst([
                            'id = :id:',
                            'bind' => ['id' => (int) $item->id]
                        ]);
                        $myUser->status = UserModel::STATUS_DISABLE;

                        if (!$myUser->update()) {
                            $this->db->rollback();
                            return;
                        }
                    }

                    if ($this->db->commit() == false) {
                        throw new UserException(ErrorCode::DATA_BULK_FAILED);
                    }

                    break;
            }
        }

        return $this->respondWithOK();
    }

    /**
     * Delete
     *
     * @Route("/", methods={"DELETE"})
     */
    public function deleteAction()
    {
        $formData = (array) $this->request->getJsonRawBody();

        $myUser = UserModel::findFirst([
            'id = :id:',
            'bind' => [
                'id' => (int) $formData['id']
            ]
        ]);

        if (!$myUser) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        if (!$myUser->delete()) {
            throw new UserException(ErrorCode::DATA_DELETE_FAIL);
        }

        return $this->createItem(
            $myUser,
            new UserTransformer,
            'response'
        );
    }

    /**
     * Change user password.
     *
     * @return void
     *
     * @Route("/changepassword", methods={"PUT"})
     */
    public function changepasswordAction()
    {
        $formData = (array) $this->request->getJsonRawBody();

        $myUser = UserModel::findFirst([
            'id = :id: AND status = :status: AND isverified = :isverified:',
            'bind' => [
                'id' => (int) $this->auth->getUser()->id,
                'status' => UserModel::STATUS_ENABLE,
                'isverified' => UserModel::IS_VERIFIED
            ]
        ]);

        if (!$myUser) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        if (!$myUser->validatePassword($formData['oldpassword'])) {
            throw new UserException(ErrorCode::USER_OLD_PASSWORD_NOT_MATCH);
        }

        if ($formData['newpassword'] != $formData['repeatnewpassword']) {
            throw new UserException(ErrorCode::USER_PASSWORD_NOT_MATCH);
        }

        $myUser->password = $this->security->hash($formData['newpassword']);
        $myUser->datelastchangepassword = time();
        if (!$myUser->update()) {
            throw new UserException(ErrorCode::DATA_UPDATE_FAIL);
        }

        $this->auth->setIssuer('email');
        $this->auth->setUser($myUser);

        // Generate jwt authToken for activate user.
        $tokenResponse = $this->auth->getTokenResponse();

        return $this->respondWithArray($tokenResponse, 'response');
    }

    /**
     * Check user already changed password for each visit site.
     *
     * @return void
     *
     * @Route("/checkchangepassword", methods={"POST"})
     */
    public function checkchangepasswordAction()
    {
        $id = (int) $this->auth->getUser()->id;

        $myUser = UserModel::findFirst([
            'id = :id: AND status = :status: AND isverified = :isverified:',
            'bind' => [
                'id' => (int) $id,
                'status' => UserModel::STATUS_ENABLE,
                'isverified' => UserModel::IS_VERIFIED
            ]
        ]);

        if (!$myUser) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        if ((int) $myUser->datelastchangepassword !== (int) $this->auth->getUser()->datelastchangepassword) {
            throw new UserException(ErrorCode::AUTH_PASSWORD_CHANGED);
        }

        return $this->respondWithOK();
    }

    // Function to query duplicate email
    private static function checkUniqueEmail($formData)
    {
        $pass = true;

        $myUser = UserModel::findFirst([
            'email = :email:',
            'bind' => [
                'email' => $formData['email']
            ]
        ]);

        if ($myUser) {
            $pass = false;
        }

        return $pass;
    }

    // Function to compare password match
    private static function comparePassword($formData)
    {
        $pass = true;

        if ($formData['password'] != $formData['repeatpassword']) {
            $pass = false;
        }

        return $pass;
    }

    /**
     * Return select source support for create/edit/index filter page form
     *
     * @Route("/formsource", methods={"GET"})
     */
    public function formsourceAction()
    {
        return $this->respondWithArray([
            'groupList' => UserModel::getGroupList(),
            'statusList' => UserModel::getStatusList(),
            'verifyList' => UserModel::getVerifyList(),
        ], 'records');
    }

    /**
     * Check email existed on system
     *
     * @Route("/checkemail", methods={"POST"})
     */
    public function checkemailAction()
    {
        $formData = (array) $this->request->getJsonRawBody();

        $myUser = UserModel::findFirst([
            'email = :email: AND status = :status:',
            'bind' => [
                'email' => (string) trim($formData['email']),
                'status' => (int) UserModel::STATUS_ENABLE
            ]
        ]);

        if (!$myUser) {
            throw new UserException(ErrorCode::AUTH_ACCOUNT_NEW);
        }

        if (($myUser->oauthprovider == 'facebook' || $myUser->oauthprovider == 'google')
            && $myUser->datelastchangepassword == 0
        ) {
            throw new UserException(ErrorCode::AUTH_ACCOUNT_NEED_UPDATE_PASSWORD);
        }

        if ($myUser->isverified == UserModel::IS_VERIFIED) {
            throw new UserException(ErrorCode::AUTH_ACCOUNT_ALREADY_EXISTED);
        }

        if ($myUser->isverified == UserModel::IS_NOT_VERIFIED) {
            throw new UserException(ErrorCode::AUTH_ACCOUNT_DIDNOT_VERIFIED);
        }

        return $this->respondWithOK();
    }

    /**
     * Forgot password.
     *
     * @Route("/forgotpassword", methods={"POST"})
     */
    public function forgotpasswordAction()
    {
        $formData = (array) $this->request->getJsonRawBody();

        $myUser = UserModel::findFirst([
            'email = :email: AND status = :status:',
            'bind' => [
                'email' => (string) trim($formData['email']),
                'status' => (int) UserModel::STATUS_ENABLE
            ]
        ]);

        if (!$myUser) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        // send forgot email
        $mailContent = $this->file->read('mail/default/forgot_code.html');
        $forgotCode = mt_rand(100000, 999999);

        $authForgotUrl = getenv('AUTH_FORGOT_URL') . '?e=' . trim($formData['email']) . '&c=' . $forgotCode;
        $mailContent = str_replace('[CLIENT.BASEURL]', $this->url->getBaseUri(), $mailContent);
        $mailContent = str_replace('[CLIENT.URL]', $authForgotUrl, $mailContent);
        $mailContent = str_replace('[CLIENT.FORGOT_CODE]', $forgotCode, $mailContent);

        $mailSended = Helper::sendMailgun(
            trim($formData['email']),
            $forgotCode . ' is code to verify your account',
            $mailContent,
            [
                'type' => 'forgot',
                'code' => $forgotCode
            ]
        );

        if (!$mailSended) {
            throw new UserException(ErrorCode::USER_SEND_ACTIVATE_MAIL_FAIL);
        }

        return $this->respondWithOK();
    }

    /**
     * Check forgot code valid.
     *
     * @Route("/checkforgot", methods={"POST"})
     */
    public function checkforgotAction()
    {
        $formData = (array) $this->request->getJsonRawBody();

        $myUser = UserModel::findFirst([
            'email = :email: AND status = :status: AND isverified = :isverified: ',
            'bind' => [
                'email' => (string) trim($formData['email']),
                'status' => (int) UserModel::STATUS_ENABLE,
                'isverified' => (int) UserModel::IS_VERIFIED
            ]
        ]);

        if (!$myUser) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        $myUserForgotCode = UserConfirmationCodeModel::findFirst([
            'uid = :uid: AND type = :type: AND valid = :isvalid: AND code = :code:',
            'bind' => [
                'uid' => $myUser->id,
                'type' => UserConfirmationCodeModel::TYPE_FORGOT,
                'isvalid' => UserConfirmationCodeModel::IS_VALID,
                'code' => (int) $formData['code']
            ]
        ]);

        if (!$myUserForgotCode) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        return $this->respondWithOK();
    }

    /**
     * Reset user password.
     *
     * @Route("/resetpassword", methods={"PUT"})
     */
    public function resetpasswordAction()
    {
        $formData = (array) $this->request->getJsonRawBody();

        if (!self::comparePassword($formData)) {
            throw new UserException(ErrorCode::USER_PASSWORD_NOT_MATCH);
        }

        // Get user
        $myUser = UserModel::findFirst([
            'email = :email: AND status = :status: AND isverified = :isverified: ',
            'bind' => [
                'email' => (string) trim($formData['email']),
                'status' => (int) UserModel::STATUS_ENABLE,
                'isverified' => (int) UserModel::IS_VERIFIED
            ]
        ]);

        if (!$myUser) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        // Check user with valid code
        $myUserForgotCode = UserConfirmationCodeModel::findFirst([
            'uid = :uid: AND type = :type: AND valid = :isvalid: AND code = :code:',
            'bind' => [
                'uid' => $myUser->id,
                'type' => UserConfirmationCodeModel::TYPE_FORGOT,
                'isvalid' => UserConfirmationCodeModel::IS_VALID,
                'code' => (int) $formData['code']
            ]
        ]);

        if (!$myUserForgotCode) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        $myUser->password = (string) $this->security->hash($formData['password']);
        $myUser->datelastchangepassword = (int) time();

        if (!$myUser->update()) {
            throw new UserException(ErrorCode::USER_UPDATE_FAIL);
        }

        $myUserForgotCode->valid = UserConfirmationCodeModel::IS_INVALID;
        $myUserForgotCode->update();

        return $this->createItem(
            $myUser,
            new UserTransformer,
            'response'
        );
    }

    /**
     * Send code to email using to update password code.
     * Using when user logged with Facebook/Google first.
     *
     * @Route("/updatepasswordcode", methods={"POST"})
     */
    public function updatepasswordcodeAction()
    {
        $formData = (array) $this->request->getJsonRawBody();

        $myUser = UserModel::findFirst([
            'email = :email: AND status = :status: AND isverified = :isverified:',
            'bind' => [
                'email' => (string) trim($formData['email']),
                'status' => (int) UserModel::STATUS_ENABLE,
                'isverified' => (int) UserModel::IS_VERIFIED
            ]
        ]);

        if (!$myUser) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        // send update password code to email
        $mailContent = $this->file->read('mail/default/update_password_code.html');
        $updatePasswordCode = mt_rand(100000, 999999);

        $authUpdatePasswordUrl = getenv('AUTH_UPDATE_PASSWORD_URL') . '?e=' . trim($formData['email']) . '&c=' . $updatePasswordCode;
        $mailContent = str_replace('[CLIENT.BASEURL]', $this->url->getBaseUri(), $mailContent);
        $mailContent = str_replace('[CLIENT.URL]', $authUpdatePasswordUrl, $mailContent);
        $mailContent = str_replace('[CLIENT.UPDATE_PASSWORD_CODE]', $updatePasswordCode, $mailContent);

        $mailSended = Helper::sendMailgun(
            trim($formData['email']),
            $updatePasswordCode . ' is code to create your password',
            $mailContent,
            [
                'type' => 'update_password',
                'code' => $updatePasswordCode
            ]
        );

        return $this->respondWithOK();
    }

    /**
     * Check update password code valid.
     *
     * @Route("/checkupdatepassword", methods={"POST"})
     */
    public function checkupdatepasswordAction()
    {
        $formData = (array) $this->request->getJsonRawBody();

        $myUser = UserModel::findFirst([
            'email = :email: AND status = :status: AND isverified = :isverified: ',
            'bind' => [
                'email' => (string) trim($formData['email']),
                'status' => (int) UserModel::STATUS_ENABLE,
                'isverified' => (int) UserModel::IS_VERIFIED
            ]
        ]);

        if (!$myUser) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        $myUserUpdatePasswordCode = UserConfirmationCodeModel::findFirst([
            'uid = :uid: AND type = :type: AND valid = :isvalid: AND code = :code:',
            'bind' => [
                'uid' => $myUser->id,
                'type' => UserConfirmationCodeModel::TYPE_UPDATE,
                'isvalid' => UserConfirmationCodeModel::IS_VALID,
                'code' => (int) $formData['code']
            ]
        ]);

        if (!$myUserUpdatePasswordCode) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        return $this->respondWithOK();
    }

    /**
     * Update user password.
     *
     * @Route("/updatepassword", methods={"PUT"})
     */
    public function updatepasswordAction()
    {
        $formData = (array) $this->request->getJsonRawBody();

        if (!self::comparePassword($formData)) {
            throw new UserException(ErrorCode::USER_PASSWORD_NOT_MATCH);
        }

        // Get user
        $myUser = UserModel::findFirst([
            'email = :email: AND status = :status: AND isverified = :isverified: ',
            'bind' => [
                'email' => (string) trim($formData['email']),
                'status' => (int) UserModel::STATUS_ENABLE,
                'isverified' => (int) UserModel::IS_VERIFIED
            ]
        ]);

        if (!$myUser) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        // Check user with valid code
        $myUserUpdateCode = UserConfirmationCodeModel::findFirst([
            'uid = :uid: AND type = :type: AND valid = :isvalid: AND code = :code:',
            'bind' => [
                'uid' => $myUser->id,
                'type' => UserConfirmationCodeModel::TYPE_UPDATE,
                'isvalid' => UserConfirmationCodeModel::IS_VALID,
                'code' => (int) $formData['code']
            ]
        ]);

        if (!$myUserUpdateCode) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        $myUser->password = (string) $this->security->hash($formData['password']);
        $myUser->datelastchangepassword = (int) time();

        if (!$myUser->update()) {
            throw new UserException(ErrorCode::USER_UPDATE_FAIL);
        }

        $myUserUpdateCode->valid = UserConfirmationCodeModel::IS_INVALID;
        $myUserUpdateCode->update();

        return $this->createItem(
            $myUser,
            new UserTransformer,
            'response'
        );
    }
}
