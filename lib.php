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
    $eventdata->name = 'auth_magic';
    $eventdata->component = 'auth_magic';
    $eventdata->modulename = 'moodle';
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
    $site = get_site();
    $user = \core_user::get_user($userid);
    if ($otherauth) {
        $auth = get_auth_plugin('magic');
        $auth->create_magic_instance($user, false);
    }
    $loginlink = auth_magic_get_user_login_link($userid);
    $subject = get_string('loginsubject', 'auth_magic', format_string($site->fullname));
    $data = new stdClass();
    $data->sitename = format_string($site->fullname);
    $data->admin = generate_email_signoff();
    $data->fullname = fullname($user);
    $data->link = $loginlink;
    if ($expired) {
        $messageplain = get_string('expiredloginlinkmsg', 'auth_magic', $data);
    } else {
        $messageplain = get_string('loginlinkmessage', 'auth_magic', $data);
    }
    $messagehtml = text_to_html($messageplain, false, false, true);
    $user->mailformat = 1;  // Always send HTML version as well.
    auth_magic_messagetouser($user, $subject, $messageplain, $messagehtml);
    return true;

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
 * Check if Magic Auth Pro is installed.
 *
 * @return bool
 */
function auth_magic_has_pro() {
    if (array_key_exists('magic', core_component::get_plugin_list('local'))) {
        return true;
    }
    return false;
}
