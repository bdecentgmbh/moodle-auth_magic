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
 * Authentication Plugin: Magic Authentication lib functions.
 *
 *
 * @package     auth_magic
 * @copyright   2023 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Get user login link
 * @param int $userid
 * @return string url.
 */
function auth_magic_get_user_login_link($userid) {
    global $DB;
    return $DB->get_field('auth_magic_loginlinks', 'magiclogin', ['userid' => $userid]);
}


/**
 * Send message to user using message api.
 *
 * @param  mixed $userto
 * @param  mixed $subject
 * @param  mixed $messageplain
 * @param  mixed $messagehtml
 * @param  mixed $courseid
 * @return bool message status
 */
function auth_magic_messagetouser($userto, $subject, $messageplain, $messagehtml, $courseid = null) {
    $eventdata = new \core\message\message();
    $eventname = (PHPUNIT_TEST) ? 'instantmessage' : 'notification';
    $eventcomponent = (PHPUNIT_TEST) ? 'moodle' : 'auth_magic';
    $eventdata->name = $eventname;
    $eventdata->component = $eventcomponent;
    $eventdata->courseid = empty($courseid) ? SITEID : $courseid;
    $eventdata->userfrom = core_user::get_support_user();
    $eventdata->userto = $userto;
    $eventdata->subject = $subject;
    $eventdata->fullmessage = $messageplain;
    $eventdata->fullmessageformat = FORMAT_HTML;
    $eventdata->fullmessagehtml = $messagehtml;
    $eventdata->smallmessage = $subject;

    if (message_send($eventdata)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Sent the login link to the user.
 * @param int $userid
 * @param bool $otherauth
 * @param bool $expired
 * @return bool message status
 */
function auth_magic_sent_loginlink_touser($userid, $otherauth = false, $expired = false) {
    global $DB;
    $site = get_site();
    $user = \core_user::get_user($userid);
    $auth = get_auth_plugin('magic');
    if ($otherauth) {
        $auth->create_magic_instance($user, false);
    }
    $loginlink = auth_magic_get_user_login_link($userid);
    $subject = get_string('loginsubject', 'auth_magic', format_string($site->fullname));
    $data = new stdClass();
    $data->sitename = format_string($site->fullname);
    $data->admin = generate_email_signoff();
    $data->fullname = fullname($user);
    if (empty($loginlink)) {
        $auth->create_magic_instance($user, false);
        $loginlink = auth_magic_get_user_login_link($user->id);
    }
    $data->link = $loginlink;
    $data->expiry = auth_magic_get_user_magic_link_expires($userid, 'loginexpiry');
    if ($expired) {
        $messageplain = get_string('expiredloginlinkmsg', 'auth_magic', $data);
    } else {
        // Check link is expiry and more type.
        $instance = $DB->get_record('auth_magic_loginlinks', ['userid' => $user->id]);
        if ($instance->loginexpiry < time()) {
            $auth->update_new_loginkey($user, $instance);
            auth_magic_sent_loginlink_touser($user->id, $otherauth);
            return;
        }
        $messageplain = get_string('loginlinkmessage', 'auth_magic', $data);
    }
    $messagehtml = text_to_html($messageplain, false, false, true);
    $user->mailformat = 1;  // Always send HTML version as well.
    auth_magic_messagetouser($user, $subject, $messageplain, $messagehtml);
    return true;
}


/**
 * Get user login link expires.
 * @param int $userid
 * @param string $type
 * @return string time.
 */
function auth_magic_get_user_magic_link_expires($userid, $type) {
    global $DB;
    $expiry = $DB->get_field('auth_magic_loginlinks', $type, ['userid' => $userid]);
    return $expiry ? auth_magic_expirytime_convert_datestring($expiry) : '';
}

/**
 * Convert expirytime to date.
 * @param int $expiry
 * @return string value.
 */
function auth_magic_expirytime_convert_datestring($expiry) {
    if ($expiry && $expiry > time()) {
        $t = $expiry - time();
        $hours = floor($t / 3600);
        $minutes = floor(($t % 3600) / 60);
        $seconds = $t % 60;
        $strhours = get_string('hours');
        $strmins = get_string('minutes');
        $strseconds = get_string('seconds');
        return sprintf("%02d $strhours %02d $strmins %02d $strseconds", $hours, $minutes, $seconds);
    }
    return get_string('currentlylinkexpiry', 'auth_magic');
}

/**
 * Sent the information for non magic auth users.
 * @param int $userid
 * @return void
 */
function auth_magic_requiredmail_magic_authentication($userid) {
    $site = get_site();
    $user = \core_user::get_user($userid);
    $forgothtml = html_writer::link(new moodle_url('/login/forgot_password.php'), get_string('forgotten'));
    $subject = get_string('loginsubject', 'auth_magic', format_string($site->fullname));
    $data = new stdClass();
    $data->sitename = format_string($site->fullname);
    $data->admin = generate_email_signoff();
    $data->fullname = fullname($user);
    $data->forgothtml = $forgothtml;
    $messageplain = get_string('preventmagicauthmessage', 'auth_magic', $data);
    $messagehtml = text_to_html($messageplain, false, false, true);
    $user->mailformat = 1;
    return auth_magic_messagetouser($user, $subject, $messageplain, $messagehtml);
}

/**
 * Find the local account for an email address, or for a username if the plugin allows usernames.
 *
 * Emails are matched case-insensitively, like the core login. An email that several accounts share
 * matches none of them.
 *
 * @param string $email Email address or username
 * @return stdClass|null user
 */
function auth_magic_get_email_user($email) {
    global $CFG, $DB;

    $value = trim($email);
    if ($value === '') {
        return null;
    }
    $params = ['mnethostid' => $CFG->mnet_localhost_id];
    $users = [];
    if (validate_email($value)) {
        $select = $DB->sql_equal('email', ':email', false) . ' AND mnethostid = :mnethostid AND deleted = 0';
        $users = $DB->get_records_select('user', $select, $params + ['email' => $value], 'id', '*', 0, 2);
    }
    if (!$users && get_config('auth_magic', 'loginoption')) {
        $select = 'username = :username AND mnethostid = :mnethostid AND deleted = 0';
        $params['username'] = core_text::strtolower($value);
        $users = $DB->get_records_select('user', $select, $params, 'id', '*', 0, 2);
    }
    return count($users) === 1 ? reset($users) : null;
}

/**
 * Whether the user may be sent another email that anybody can trigger, such as a requested link.
 *
 * A user gets at most one such email within the request interval of the plugin. If one may be
 * sent now, the time is recorded.
 *
 * @param int $userid
 * @return bool
 */
function auth_magic_may_send_link_email(int $userid): bool {
    $interval = (int) get_config('auth_magic', 'requestinterval');
    if ($interval <= 0) {
        return true;
    }
    $now = time();
    if ((int) get_user_preferences('auth_magic_lastlinkemail', 0, $userid) > $now - $interval) {
        return false;
    }
    set_user_preference('auth_magic_lastlinkemail', $now, $userid);
    return true;
}

/**
 * Answer a request for a magic link made for an existing account.
 *
 * The account gets a magic link, or a reminder to use its own login method if it may not use
 * magic links. Suspended and unconfirmed accounts get nothing, and nobody gets more than one
 * email per request interval.
 *
 * @param stdClass $user
 */
function auth_magic_process_link_request(stdClass $user): void {
    if (!empty($user->deleted) || !empty($user->suspended) || empty($user->confirmed) || $user->auth === 'nologin') {
        return;
    }
    if (!auth_magic_may_send_link_email($user->id)) {
        return;
    }
    $auth = get_auth_plugin('magic');
    if ($auth->can_login_with_link($user)) {
        auth_magic_sent_loginlink_touser($user->id, $user->auth !== 'magic');
    } else {
        auth_magic_requiredmail_magic_authentication($user->id);
    }
}
