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

use core_user\output\myprofile\tree;

/**
 * Get user login link
 * @param int $userid
 * @return string url.
 */
function auth_magic_get_user_login_link($userid) {
    global $DB;
    return $DB->get_field('auth_magic_loginlinks', 'magiclogin', array('userid' => $userid), 'loginurl');
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
    $eventdata->name = 'instantmessage';
    $eventdata->component = 'moodle';
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
        $instance = $DB->get_record('auth_magic_loginlinks', array('userid' => $user->id));
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
    $expiry = $DB->get_field('auth_magic_loginlinks', $type,  array('userid' => $userid));
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
 * Get magic email user.
 * @param string $email
 * @return stdclass user
 */
function auth_magic_get_email_user($email) {
    global $DB;
    $user = $DB->get_record('user', ['email' => $email]);
    if (!$user && get_config('auth_magic', 'loginoption')) {
        $user = $DB->get_record('user', array('username' => $email));
    }
    return !empty($user) ? $user : null;
}
