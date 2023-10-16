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
if (!is_enabled_auth('magic')) {
    throw new moodle_exception(get_string('pluginisdisabled', 'auth_magic'));
}
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/auth/magic/login.php');
$magiclogin = optional_param('magiclogin', 0, PARAM_INT);

// Ajax using sent login link via email.
if ($magiclogin) {
    $errormsg = '';
    $uservalue = optional_param('uservalue', '', PARAM_NOTAGS);
    $email = $uservalue;
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

    if (!validate_email($email)) {
        $errormsg = get_string('invalidemail');
    } else {
        // Make a case-insensitive query for the given email address.
        $select = $DB->sql_equal('email', ':email', false) . ' AND mnethostid = :mnethostid';
        $params = array(
            'email' => $email,
            'mnethostid' => $CFG->mnet_localhost_id,
        );
        if (!$DB->record_exists_select('user', $select, $params)) {
            $errormsg = get_string('emailnotexists', 'auth_magic');
        }
    }

    if (!empty($errormsg)) {
        $SESSION->loginerrormsg = $errormsg;
    } else if (!isloggedin()) {
        $user = $DB->get_record('user', array('email' => $email));
        if (!$user->deleted  && !$user->suspended) {
            $accessauthtoall = get_config('auth_magic', 'authmethod');
            if (empty($CFG->rememberusername)) {
                // No permanent cookies, delete old one if exists.
                set_moodle_cookie('');
            } else {
                set_moodle_cookie($user->username);
            }
            if ($user->auth == 'magic' || $accessauthtoall) {
                $otherauth = ($user->auth != 'magic') ? true : false;
                if (auth_magic_sent_loginlink_touser($user->id, $otherauth)) {
                    // Check the login option is enabled.
                    redirect(get_login_url(),  $usermessage,
                        null, \core\output\notification::NOTIFY_SUCCESS);
                }
            } else {
                // Doesn't access the user for another auth method.
                auth_magic_requiredmail_magic_authentication($user->id);
                redirect(get_login_url(),  $usermessage,
                null, \core\output\notification::NOTIFY_SUCCESS);
            }
        }
    }
    redirect(get_login_url());
}

$auth = get_auth_plugin('magic');
$keyvalue = required_param('key', PARAM_ALPHANUM);

if (isset($SESSION->wantsurl)) {
    $redirecturl = $SESSION->wantsurl;
} else {
    $redirecturl = $CFG->wwwroot;
}

// Check key is expired or not.
$auth->check_userkey_type($keyvalue);

$key = validate_user_key($keyvalue, 'auth/magic', null);
if (get_config('auth_magic', 'loginkeytype') == 'once') {
    delete_user_key('auth/magic', $key->userid);
    $DB->delete_records('auth_magic_loginlinks', array('userid' => $key->userid));
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

$user = core_user::get_user($key->userid, '*', MUST_EXIST);
core_user::require_active_user($user, true, true);
// Do the user log-in.
if (!$user = get_complete_user_data('id', $user->id)) {
    throw new moodle_exception('cannotfinduser', '', '', $user->id);
}

complete_user_login($user);

\core\session\manager::apply_concurrent_login_limit($user->id, session_id());

redirect($redirecturl);