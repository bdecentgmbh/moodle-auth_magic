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
 * Magic authentication login - Create magic links to login user.
 *
 * @package auth_magic
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot."/auth/magic/lib.php");
/**
 * Plugin for no authentication - disabled user.
 */
class auth_plugin_magic extends auth_plugin_base {


    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'magic';
    }

    /**
     * Old syntax of class constructor. Deprecated in PHP7.
     *
     * @deprecated since Moodle 3.1
     */
    public function auth_plugin_magic() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    /**
     * Don't allow login using login form.
     *
     * @param string $username The username
     * @param string $password The password
     */
    public function user_login($username, $password) {
        return false;
    }

    /**
     * No password updates.
     *
     * @param string $user The username
     * @param string $newpassword The password
     */
    public function user_update_password($user, $newpassword) {
        return false;
    }

    /**
     * Don't store local passwords.
     *
     * @return bool True.
     */
    public function prevent_local_passwords() {
        // Just in case, we do not want to loose the passwords.
        return false;
    }

    /**
     * No external data sync.
     *
     * @return bool
     */
    public function is_internal() {
        // We do not know if it was internal or external originally.
        return true;
    }

    /**
     * No changing of password.
     *
     * @return bool
     */
    public function can_change_password() {
        return false;
    }

    /**
     * No password resetting.
     */
    public function can_reset_password() {
        return false;
    }

    /**
     * Returns true if plugin can be manually set.
     *
     * @return bool
     */
    public function can_be_manually_set() {
        return true;
    }

    /**
     * Returns information on how the specified user can change their password.
     * User accounts with authentication type set to magic are disabled accounts.
     * They cannot change their password.
     *
     * @param stdClass $user A user object
     * @return string[] An array of strings with keys subject and message
     */
    public function get_password_change_info(stdClass $user) : array {
        $site = get_site();

        $data = new stdClass();
        $data->firstname = $user->firstname;
        $data->lastname = $user->lastname;
        $data->username = $user->username;
        $data->sitename = format_string($site->fullname);
        $data->admin = generate_email_signoff();

        $message = get_string('emailpasswordchangeinfodisabled', '', $data);
        $subject = get_string('emailpasswordchangeinfosubject', '', format_string($site->fullname));

        return [
            'subject' => $subject,
            'message' => $message
        ];
    }

    /**
     * Create key for a specific user.
     *
     * @param int $userid User ID.
     * @param int $validuntil
     */
    public function create_user_key($userid, $validuntil) {
        $config = $this->get_config_data();
        $iprestriction = null;

        return create_user_key(
            'auth/magic',
            $userid,
            $userid,
            $iprestriction,
            $validuntil
        );
    }

    /**
     * Validates key and returns key data object if valid.
     *
     * @param string $keyvalue User key value.
     *
     * @return object Key object including userid property.
     *
     * @throws \moodle_exception If provided key is not valid.
     */
    public function validate_key($keyvalue) {
        global $DB;

        $options = array(
            'script' => 'auth/magic',
            'value' => $keyvalue
        );
        $message = '';
        $accessauthtoall = 0;
        if (auth_magic_has_pro()) {
            $accessauthtoall = get_config('auth_magic', 'authmethod');
        }
        if (!$key = $DB->get_record('user_private_key', $options)) {
            $message = get_string('invalidkey', 'error');
        } else if (!$user = $DB->get_record('user', array('id' => $key->userid))) {
            $message = get_string('invaliduserid', 'error');
        } else if ($user->deleted || $user->suspended) {
            $message = get_string('invailduser', 'auth_magic');
        } else if ($user->auth != 'magic' && !$accessauthtoall) {
            $message = get_string('invalidrequest', 'error');
        } else if (!empty($key->validuntil) && $key->validuntil < time()) {
            $message = get_string('expiredkey', 'error');
        }
        if (!empty($message)) {
            if ((defined('PHPUNIT_TEST') && PHPUNIT_TEST)) {
                return false;
            }
            redirect(new \moodle_url('/login/index.php'), $message, null, \core\output\notification::NOTIFY_ERROR);
        }
        return $key;
    }

    /**
     * Delete all keys for a specific user.
     *
     * @param int $userid User ID.
     */
    public function delete_keys($userid) {
        delete_user_key('auth/magic', $userid);
    }

    /**
     * Get plugin config data.
     * @return stdClass data
     */
    public function get_config_data() {
        return get_config('auth_magic');
    }

    /**
     * Returns a list of potential IdPs that this authentication plugin supports. Used to provide links on the login page.
     *
     * @param string $wantsurl The relative url fragment the user wants to get to.
     * @return array Array of idps.
     */
    public function loginpage_idp_list($wantsurl) {
        global $PAGE;
        return [
            [
                'url' => new \moodle_url('/auth/magic/login.php'),
                'iconurl' => '',
                'name' => get_string('getmagiclinkviagmail', 'auth_magic'),
            ]
        ];
    }

    /**
     * Hook for overriding behaviour of login page.
     * This method is called from login/index.php page for all enabled auth plugins.
     */
    public function loginpage_hook() {
        global $CFG, $PAGE;
        $PAGE->add_body_class('auth-magic');
        $CFG->authloginviaemail = true;
        $linkbtnpos = '';
        if (auth_magic_has_pro()) {
            $linkbtnpos = get_config('auth_magic', 'loginlinkbtnpostion');
        }
        $params = array(
            'loginhook' => true,
            'strbutton' => get_string('getmagiclinkviagmail', 'auth_magic'),
            'linkbtnpos' => $linkbtnpos
        );
        $PAGE->requires->js_call_amd('auth_magic/authmagic', 'init', array($params));
    }


    /**
     * Create key for users.
     * @param object $user
     * @param bool $checkparent
     * @return void
     */
    public function create_magic_instance($user, $checkparent = true) {
        global $CFG, $DB, $USER;
        $config = $this->get_config_data();
        $loginexpiry = !empty($config->loginexpiry) ? time() + $config->loginexpiry : 0;
        $invitationexpiry = !empty($config->invitationexpiry) ? time() + $config->invitationexpiry : 0;
        $loginuserkey = $this->create_user_key($user->id, $loginexpiry);
        $invitationuserkey = $this->create_user_key($user->id, $invitationexpiry);
        $loginurl = $CFG->wwwroot . '/auth/magic/login.php?key=' . $loginuserkey;
        $invitationurl = $CFG->wwwroot . '/auth/magic/login.php?key=' . $invitationuserkey;
        $parent = 0;
        $parentrole = null;
        if (auth_magic_has_pro()) {
            if ($checkparent) {
                $parentrole = get_config('auth_magic', 'owneraccountrole');
                if ($parentrole) {
                    $parent = $USER->id;
                }
            }
        }

        if (!$DB->record_exists('auth_magic_loginlinks', array('userid' => $user->id))) {
            // Insert record.
            $record = new stdClass;
            $record->userid = $user->id;
            $record->parent = $parent;
            $record->magicauth = ($checkparent) ? 1 : 0;
            $record->parentrole = ($checkparent) ? $parentrole : 0;
            $record->loginuserkey = $loginuserkey;
            $record->invitationuserkey = $invitationuserkey;
            $record->magiclogin = $loginurl;
            $record->magicinvitation = $invitationurl;
            $record->loginexpiry = $loginexpiry;
            $record->invitationexpiry = $invitationexpiry;
            $record->timecreated = time();
            $DB->insert_record('auth_magic_loginlinks', $record);
        }

    }

    /**
     * Update the loginkey.
     * @param core_user $user
     * @param stdClass $keyinstance
     */
    public function update_new_loginkey($user, $keyinstance) {
        global $DB, $CFG;
        $config = $this->get_config_data();
        $loginexpiry = !empty($config->loginexpiry) ? time() + $config->loginexpiry : 0;
        $loginuserkey = $this->create_user_key($user->id, $loginexpiry);
        if (!empty($keyinstance)) {
            $keyinstance->loginuserkey = $loginuserkey;
            $keyinstance->magiclogin = $CFG->wwwroot . '/auth/magic/login.php?key=' . $loginuserkey;
            $keyinstance->loginexpiry = $loginexpiry;
            $keyinstance->timemodified = time();
            $DB->update_record('auth_magic_loginlinks', $keyinstance);
        }
    }

    /**
     * Wheather key is invitation or login.
     * @param mixed $key
     * @return bool
     */
    public function check_userkey_type($key) {
        global $DB;
        $options = array(
            'script' => 'auth/magic',
            'value' => $key
        );
        $accessauthtoall = 0;
        if (auth_magic_has_pro()) {
            $accessauthtoall = get_config('auth_magic', 'authmethod');
        }
        if ($instance = $DB->get_record('auth_magic_loginlinks', array('loginuserkey' => $key))) {
            // Key as login.
            if (!empty($instance->loginexpiry) && $instance->loginexpiry < time()) {
                // Resend login and indicate to the click the expiry key.
                $relateduser = \core_user::get_user($instance->userid);
                if (!$relateduser->suspended && !$relateduser->deleted) {
                    if ($relateduser->auth == 'magic' || $accessauthtoall) {
                        $this->update_new_loginkey($relateduser, $instance);
                        auth_magic_sent_loginlink_touser($relateduser->id, false, true);
                        redirect(new moodle_url('/login/index.php'), get_string('loginexpiryloginlink', 'auth_magic'),
                            null, \core\output\notification::NOTIFY_INFO);
                    }
                }
            }
        } else if ($instance = $DB->get_record('auth_magic_loginlinks', array('invitationuserkey' => $key))) {
            // Key as invitation.
            if (!empty($instance->invitationexpiry) && $instance->invitationexpiry < time()) {
                // Resend login.
                $relateduser = \core_user::get_user($instance->userid);
                if (!$relateduser->suspended && !$relateduser->deleted) {
                    if ($relateduser->auth == 'magic' || $accessauthtoall) {
                        // Exist login is expiry or not.
                        if (!empty($instance->loginexpiry) && $instance->loginexpiry < time()) {
                            $this->update_new_loginkey($relateduser, $instance);
                        }
                        auth_magic_sent_loginlink_touser($relateduser->id);
                        redirect(new moodle_url('/login/index.php'), get_string('invitationexpiryloginlink', 'auth_magic'),
                            null, \core\output\notification::NOTIFY_INFO);
                    }
                }
            }
        } else if ($keyrecord = $DB->get_record('user_private_key', $options)) {
            $relateduser = \core_user::get_user($keyrecord->userid);
            if (!$relateduser->suspended && !$relateduser->deleted) {
                if ($relateduser->auth == 'magic' || $accessauthtoall) {
                    $instance = $DB->get_record('auth_magic_loginlinks', array('userid' => $relateduser->id));
                    if (!empty($instance->loginexpiry) && $instance->loginexpiry < time()) {
                        $this->update_new_loginkey($relateduser, $instance);
                    }
                    auth_magic_sent_loginlink_touser($relateduser->id, false, true);
                    redirect(new moodle_url('/login/index.php'), get_string('loginexpiryloginlink', 'auth_magic'),
                            null, \core\output\notification::NOTIFY_INFO);
                }
            }
        }
        return true;
    }

}
