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

// This is a login page, so it can't require a login.
// phpcs:ignore moodle.Files.RequireLogin.Missing
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . "/auth/magic/lib.php");
if (!is_enabled_auth('magic')) {
    throw new moodle_exception('pluginisdisabled', 'auth_magic');
}
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/auth/magic/login.php');
$magiclogin = optional_param('magiclogin', 0, PARAM_INT);

// A request for a magic link, posted from the login page.
if ($magiclogin) {
    // Like the login form, only accept requests that carry the login token of the session.
    if (!\core\session\manager::validate_login_token(optional_param('logintoken', '', PARAM_ALPHANUM))) {
        $SESSION->loginerrormsg = get_string('invalidlogin');
        redirect(get_login_url());
    }

    $uservalue = trim(optional_param('uservalue', '', PARAM_NOTAGS));
    $isemail = validate_email($uservalue);
    if (!$isemail && ($uservalue === '' || !get_config('auth_magic', 'loginoption'))) {
        $SESSION->loginerrormsg = get_string('invalidemail');
        redirect(get_login_url());
    }

    $user = auth_magic_get_email_user($uservalue);
    if (!$user && empty($CFG->protectusernames)) {
        // The site allows telling visitors that there is no such account.
        $SESSION->loginerrormsg = get_string($isemail ? 'emailnotfound' : 'usernamenotfound');
        redirect(get_login_url());
    }
    if ($user && (!isloggedin() || isguestuser())) {
        auth_magic_process_link_request($user);
    }
    // The same answer for every account, so that it doesn't tell whether the account exists.
    redirect(
        get_login_url(),
        get_string($isemail ? 'sentlinktouser' : 'sentlinktousername', 'auth_magic'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
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
    $DB->delete_records('auth_magic_loginlinks', ['userid' => $key->userid]);
}
if (isloggedin() && !isguestuser()) {
    if ($USER->id != $key->userid) {
        // Logout the current user if it's different to one that associated to the valid key.
        require_logout();
        redirect(new moodle_url($PAGE->url, ['key' => $key->value]));
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

// Remember the username for the login form, as a password login does.
if (empty($CFG->nolastloggedin)) {
    set_moodle_cookie(empty($CFG->rememberusername) ? '' : $USER->username);
}

redirect($redirecturl);
