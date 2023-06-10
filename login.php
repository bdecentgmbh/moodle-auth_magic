<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Login page for auth_magic.
 *
 * @package    auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_mockblock\search\area;

require_once(__DIR__.'/../../config.php');
require_once($CFG->dirroot."/auth/magic/lib.php");

if (auth_magic_has_pro()) {
    require_once($CFG->dirroot."/local/magic/lib.php");
}

if (!is_enabled_auth('magic')) {
    throw new moodle_exception(get_string('pluginisdisabled', 'auth_magic'));
}
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/auth/magic/login.php');
$magiclogin = optional_param('magiclogin', 0, PARAM_INT);
$signupuser = optional_param('register', false, PARAM_BOOL);

// Ajax using sent login link via email.
if ($magiclogin) {
    $errormsg = '';
    $uservalue = optional_param('uservalue', '', PARAM_RAW_TRIMMED);
    $email = $uservalue;
    // Pro plugin feature.
    if (auth_magic_has_pro()) {
        // If the Pro plugin feature for login link via email or use username.
        $loginoption = get_config('auth_magic', 'loginoption');
        if ($user = $DB->get_record('user', array('email' => $uservalue))) {
            $email = $user->email;
            $usermessage = get_string('sentlinktouser', 'auth_magic');
        }
        if ($loginoption == true) {
            if ($user = $DB->get_record('user', array('username' => $uservalue))) {
                $email = $user->email;
                $usermessage = get_string('sentlinktousername', 'auth_magic');
            }
        }
    }

    if (!validate_email($email)) {
        $errormsg = get_string('invalidemail');
    } else {
        // Make a case-insensitive query for the given email address.
        $select = $DB->sql_equal('email', ':email', false) . ' AND mnethostid = :mnethostid';
        $params = array(
            'email' => $email,
            'mnethostid' => $CFG->mnet_localhost_id,
        );
        // If there are other user(s) that already have the same email, show an error.
        if (!$DB->record_exists_select('user', $select, $params)) {
            if (auth_magic_has_pro() && get_config('local_magic', 'autocreateusers')) {
                // Signup new user using magic.
                local_magic_user_signup_request($email);
            } else {
                $errormsg = get_string('emailnotexists', 'auth_magic');
            }
        }
    }

    if (!empty($errormsg)) {
        $SESSION->loginerrormsg = $errormsg;
    } else {
        $user = $DB->get_record('user', array('email' => $email));
        if (!$user->deleted  && !$user->suspended) {
            $accessauthtoall = 0;
            if (auth_magic_has_pro()) {
                $accessauthtoall = get_config('auth_magic', 'authmethod');
            }
            if ($user->auth == 'magic' || $accessauthtoall) {
                $otherauth = ($user->auth != 'magic') ? true : false;
                if (auth_magic_sent_loginlink_touser($user->id, $otherauth)) {
                    // Pro plugin feature.
                    if (auth_magic_has_pro()) {
                        // Check the login option is enabled.
                        redirect(new moodle_url('/login/index.php'),  $usermessage,
                            null, \core\output\notification::NOTIFY_SUCCESS);
                    }
                    redirect(new moodle_url('/login/index.php'), get_string('sentlinktouser', 'auth_magic'),
                        null, \core\output\notification::NOTIFY_SUCCESS);
                }
            } else {
                // Doesn't access the user for another auth method.
                auth_magic_requiredmail_magic_authentication($user->id);
                redirect(new moodle_url('/login/index.php'), get_string('sentlinktouser', 'auth_magic'),
                    null, \core\output\notification::NOTIFY_SUCCESS);
            }
        }
    }
    redirect(new moodle_url('/login/index.php'));
}

$auth = get_auth_plugin('magic');
$keyvalue = required_param('key', PARAM_ALPHANUM);
// Pro plugin feature.
if (auth_magic_has_pro()) {
    // If pass the url parameter the page should be redirect that url.
    $url = optional_param('url', '', PARAM_RAW);
    if (!empty($url)) {
        $SESSION->wantsurl = new moodle_url($url);
        $redirecturl = $SESSION->wantsurl;
    } else {
        $redirecturl = $CFG->wwwroot;
    }
}

if (isset($SESSION->wantsurl)) {
    $redirecturl = $SESSION->wantsurl;
} else {
    $redirecturl = $CFG->wwwroot;
}

// If user want to signup.
if ($signupuser && auth_magic_has_pro()) {
    return local_magic_signup_user($keyvalue);
}

// Check key is expired or not.
$auth->check_userkey_type($keyvalue);

try {
    $key = $auth->validate_key($keyvalue);
} catch (moodle_exception $exception) {
    // If user is logged in and key is not valid, we'd like to logout a user.
    if (isloggedin()) {
        require_logout();
    }
    throw new moodle_exception($exception->errorcode);
}

if (isloggedin()) {
    if ($USER->id != $key->userid) {
        // Logout the current user if it's different to one that associated to the valid key.
        require_logout();
        redirect(new moodle_url($PAGE->url, array('key' => $key->value)));
    } else {
        // Don't process further if the user is already logged in.
        redirect($redirecturl);
    }
}

$user = get_complete_user_data('id', $key->userid);
complete_user_login($user);

redirect($redirecturl);
