<?php

namespace Engine\Constants;

/**
 * Define constants.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://olli.vn/
 */
class ErrorCode
{
    // General
    const GENERAL_NOTFOUND = 1001;
    const GENERAL_SERVER_ERROR = 1002;

    // Data
    const DATA_DUPLICATE = 2001;
    const DATA_NOTFOUND = 2002;
    const DATA_INVALID = 2004;
    const DATA_FAIL = 2005;

    const DATA_FIND_FAIL = 2010;
    const DATA_CREATE_FAIL = 2020;
    const DATA_UPDATE_FAIL = 2030;
    const DATA_DELETE_FAIL = 2040;
    const DATA_REJECTED = 2060;
    const DATA_NOTALLOWED = 2070;
    const DATA_BULK_FAILED = 2080;
    const DATA_COUNT_MISSING = 2090;
    const DATA_ANSWER_COUNT_MISSING = 2091;

    // File upload
    const FILE_UPLOAD_ERR_MIN_SIZE = 5001;
    const FILE_UPLOAD_ERR_MAX_SIZE = 5002;
    const FILE_UPLOAD_ERR_ALLOWED_FORMAT = 5003;
    const FILE_UPLOAD_ERR = 5004;
    const FILE_DELETE_ERR = 5005;

    // Authentication
    const AUTH_NOEMAIL = 3007;
    const AUTH_INVALIDTYPE = 3008;
    const AUTH_BADLOGIN = 3009;
    const AUTH_UNAUTHORIZED = 3010;
    const AUTH_NOPASSWORD = 3011;
    const AUTH_NOTOKEN = 3012;
    const AUTH_FORBIDDEN = 3020;
    const AUTH_EXPIRED = 3030;
    const AUTH_PASSWORD_CHANGED = 3040;
    const AUTH_ACCOUNT_ALREADY_EXISTED = 3041;
    const AUTH_ACCOUNT_DIDNOT_VERIFIED = 3042;
    const AUTH_ACCOUNT_NEW = 3043;
    const AUTH_ACCOUNT_NEED_UPDATE_PASSWORD = 3044;

    // Google
    const GOOGLE_NODATA = 4001;
    const GOOGLE_BADLOGIN = 4002;

    // User management
    const USER_NOTACTIVE = 4003;
    const USER_NOTFOUND = 4004;
    const USER_REGISTERFAIL = 4005;
    const USER_MODFAIL = 4006;
    const USER_CREATE_FAIL = 4007;
    const USER_REG_NOFULLNAME = 4008;
    const USER_REG_NOEMAIL = 4009;
    const USER_REG_NOPASSWORD = 4010;
    const USER_REG_UNUNIQUEEMAIL = 4011;
    const USER_REG_INVALIDEMAIL = 4012;
    const USER_PASSWORD_NOT_MATCH = 4013;
    const USER_SEND_ACTIVATE_MAIL_FAIL = 4014;
    const USER_REGISTER_NO_CAPTCHA = 4015;
    const USER_REGISTER_CAPTCHA_FAIL = 4016;
    const USER_OLD_PASSWORD_NOT_MATCH = 4017;
    const USER_SEND_FORGOT_MAIL_FAIL = 4018;
    const USER_EVENT_PLAY_TIMES_END_REACHED = 4019;
    const USER_SEND_MAIL_FAIL = 4020;

    // PDO
    const PDO_DUPLICATE_ENTRY = 2300;

    // QUEUE
    const QUEUE_PUT_FAIL = 2400;
}
