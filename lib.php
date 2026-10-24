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
 *
 * The link is built from the key, so that it follows changes of the site URL.
 *
 * @param int $userid
 * @return string url, empty if the user has no login key
 */
function auth_magic_get_user_login_link($userid) {
    global $DB;
    $key = $DB->get_field('auth_magic_loginlinks', 'loginuserkey', ['userid' => $userid]);
    return $key ? auth_magic_link_url($key)->out(false) : '';
}

/**
 * URL of the magic link for a key.
 *
 * @param string $key
 * @return moodle_url
 */
function auth_magic_link_url(string $key): moodle_url {
    return new moodle_url('/auth/magic/login.php', ['key' => $key]);
}


/**
 * Email a message to a user.
 *
 * Like the password reset of core, this always uses email: whoever asks for a magic link isn't
 * logged in, so other message channels don't reach them, and links shouldn't be kept as
 * notifications.
 *
 * @param stdClass $userto
 * @param string $subject
 * @param string $messageplain
 * @param string $messagehtml
 * @return bool Whether the email was sent
 */
function auth_magic_messagetouser($userto, $subject, $messageplain, $messagehtml) {
    return email_to_user($userto, core_user::get_noreply_user(), $subject, $messageplain, $messagehtml);
}

/**
 * Sent the login link to the user.
 *
 * A login key that has expired or is gone is replaced first, so that the link works.
 *
 * @param int $userid
 * @param bool $otherauth Whether the user has another login method than magic
 * @param bool $expired Whether the user tried an expired link, which changes the message
 * @return bool message status
 */
function auth_magic_sent_loginlink_touser($userid, $otherauth = false, $expired = false) {
    global $DB;
    $site = get_site();
    $user = \core_user::get_user($userid, '*', MUST_EXIST);
    $auth = get_auth_plugin('magic');
    $auth->create_magic_instance($user, !$otherauth);

    $instance = $DB->get_record('auth_magic_loginlinks', ['userid' => $user->id], '*', MUST_EXIST);
    $keyexists = $DB->record_exists('user_private_key', [
        'script' => 'auth/magic',
        'userid' => $user->id,
        'value' => $instance->loginuserkey,
    ]);
    if (!$keyexists || (!empty($instance->loginexpiry) && $instance->loginexpiry < time())) {
        $auth->update_new_loginkey($user, $instance);
    }

    $subject = get_string('loginsubject', 'auth_magic', format_string($site->fullname));
    $data = new stdClass();
    $data->sitename = format_string($site->fullname);
    $data->admin = generate_email_signoff();
    $data->fullname = fullname($user);
    $data->link = auth_magic_get_user_login_link($user->id);
    $data->expiry = auth_magic_get_user_magic_link_expires($user->id, 'loginexpiry');
    // The message strings contain HTML, the plain text part is made from it.
    $message = get_string($expired ? 'expiredloginlinkmsg' : 'loginlinkmessage', 'auth_magic', $data);
    $messagehtml = text_to_html($message, false, false, true);
    $messageplain = html_to_text($messagehtml);
    $user->mailformat = 1;  // Always send HTML version as well.
    return auth_magic_messagetouser($user, $subject, $messageplain, $messagehtml);
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
    return $expiry ? auth_magic_expirytime_convert_datestring($expiry) : get_string('never');
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
    $messagehtml = text_to_html(get_string('preventmagicauthmessage', 'auth_magic', $data), false, false, true);
    $messageplain = html_to_text($messagehtml);
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
 * Return to the login page with a message, or to the home page if the user is logged in.
 *
 * The login page shows the message in its form, like its own messages, so that it fits the login
 * layout of every theme. A page notification would sit next to the form in some of them.
 *
 * @param string $message
 * @param string $type A \core\output\notification type, error or info
 */
function auth_magic_redirect_with_message(string $message, string $type = \core\output\notification::NOTIFY_INFO): void {
    global $SESSION;
    if (isloggedin() && !isguestuser()) {
        redirect(new moodle_url('/'), $message, null, $type);
    }
    if ($type === \core\output\notification::NOTIFY_ERROR) {
        $SESSION->loginerrormsg = $message;
    } else {
        $SESSION->logininfomsg = $message;
    }
    redirect(get_login_url());
}

/**
 * Remove the magic links of accounts with other login methods when the plugin goes back to magic only.
 *
 * Called when the supported authentication method setting changes.
 */
function auth_magic_authmethod_updated(): void {
    global $DB;
    if (get_config('auth_magic', 'authmethod')) {
        return;
    }
    $select = 'userid IN (SELECT id FROM {user} WHERE auth <> :auth)';
    $DB->delete_records_select('user_private_key', "script = :script AND $select", ['script' => 'auth/magic', 'auth' => 'magic']);
    $DB->delete_records_select('auth_magic_loginlinks', $select, ['auth' => 'magic']);
}

/**
 * Answer a request for a magic link made for an existing account.
 *
 * The account gets a magic link, or a reminder to use its own login method if it may not use
 * magic links. Suspended and unconfirmed accounts, the guest account and accounts that can't log
 * in interactively get nothing, and nobody gets more than one email per request interval.
 *
 * @param stdClass $user
 */
function auth_magic_process_link_request(stdClass $user): void {
    if (!empty($user->deleted) || !empty($user->suspended) || empty($user->confirmed) || isguestuser($user)) {
        return;
    }
    if (in_array($user->auth, ['nologin', 'webservice'])) {
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
