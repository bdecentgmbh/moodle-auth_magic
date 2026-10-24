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
require_once($CFG->dirroot . '/login/lib.php');
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
        auth_magic_redirect_with_message(get_string('invalidlogin'), \core\output\notification::NOTIFY_ERROR);
    }

    $uservalue = trim(optional_param('uservalue', '', PARAM_NOTAGS));
    $isemail = validate_email($uservalue);
    if (!$isemail && ($uservalue === '' || !get_config('auth_magic', 'loginoption'))) {
        auth_magic_redirect_with_message(get_string('invalidemail'), \core\output\notification::NOTIFY_ERROR);
    }

    $user = auth_magic_get_email_user($uservalue);
    if (!$user && empty($CFG->protectusernames)) {
        // The site allows telling visitors that there is no such account.
        $notfound = get_string($isemail ? 'emailnotfound' : 'usernamenotfound');
        auth_magic_redirect_with_message($notfound, \core\output\notification::NOTIFY_ERROR);
    }
    if ($user && (!isloggedin() || isguestuser())) {
        auth_magic_process_link_request($user);
    }
    // The same answer for every account, so that it doesn't tell whether the account exists.
    auth_magic_redirect_with_message(get_string($isemail ? 'sentlinktouser' : 'sentlinktousername', 'auth_magic'));
}

$auth = get_auth_plugin('magic');
$keyvalue = optional_param('key', '', PARAM_ALPHANUM);

if ($keyvalue === '') {
    if (isloggedin() && !isguestuser()) {
        // Nothing to do here, for example after a password login that returns to this page.
        redirect(new moodle_url('/'));
    }
    // The button of the login page leads here when its script can't post the username, for
    // example with JavaScript off or in a theme with a different login form. Ask for it here.
    $usernames = get_config('auth_magic', 'loginoption');
    $PAGE->set_pagelayout('login');
    $PAGE->set_title(get_string('requestlinkheading', 'auth_magic'));
    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('auth_magic/request_link', [
        'actionurl' => $PAGE->url->out(false),
        'logintoken' => \core\session\manager::get_login_token(),
        'intro' => get_string($usernames ? 'requestlinkintrousername' : 'requestlinkintro', 'auth_magic'),
        'label' => get_string($usernames ? 'usernameemail' : 'email'),
        'loginurl' => get_login_url(),
    ]);
    echo $OUTPUT->footer();
    die;
}

// A link that has expired sends a new one and returns to the login page.
$auth->check_userkey_type($keyvalue);

try {
    $key = validate_user_key($keyvalue, 'auth/magic', null);
} catch (moodle_exception $e) {
    // Used up, replaced by a newer link or expired.
    auth_magic_redirect_with_message(get_string('invalidlink', 'auth_magic'), \core\output\notification::NOTIFY_ERROR);
}

$user = core_user::get_user($key->userid, '*', MUST_EXIST);
core_user::require_active_user($user, true, true);

if (!$auth->can_login_with_link($user)) {
    // The link was created before the site stopped allowing magic links for this account.
    $auth->delete_magic_instance($user->id);
    auth_magic_redirect_with_message(get_string('linknotallowed', 'auth_magic'), \core\output\notification::NOTIFY_ERROR);
}

if (isloggedin() && !isguestuser()) {
    if ($USER->id == $user->id) {
        redirect(core_login_get_return_url());
    }
    // Somebody else is logged in. Switching accounts needs their confirmation, so that a link
    // from somebody else can't log them into another account unnoticed.
    if (!optional_param('confirm', 0, PARAM_BOOL) || !confirm_sesskey(optional_param('sesskey', '', PARAM_RAW))) {
        $PAGE->set_title(get_string('switchaccount', 'auth_magic'));
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(
            get_string('switchaccountconfirm', 'auth_magic', (object) [
                'current' => fullname($USER),
                'target' => fullname($user),
            ]),
            new moodle_url($PAGE->url, ['key' => $keyvalue, 'confirm' => 1, 'sesskey' => sesskey()]),
            new moodle_url('/')
        );
        echo $OUTPUT->footer();
        die;
    }
    require_logout();
    redirect(new moodle_url($PAGE->url, ['key' => $keyvalue]));
}

if (get_config('auth_magic', 'loginkeytype') == 'once') {
    // A single-use link is used up by the login it makes.
    $auth->delete_magic_instance($user->id);
}

$user = get_complete_user_data('id', $user->id, null, true);
complete_user_login($user);

\core\session\manager::apply_concurrent_login_limit($user->id, session_id());

// Remember the username for the login form, as a password login does.
if (empty($CFG->nolastloggedin)) {
    set_moodle_cookie(empty($CFG->rememberusername) ? '' : $USER->username);
}

redirect(core_login_get_return_url());
