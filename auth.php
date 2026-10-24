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
 * @package    auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot . "/auth/magic/lib.php");

/**
 * Magic authentication login.
 */
class auth_plugin_magic extends auth_plugin_base {
    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'magic';
    }

    /**
     * Don't allow login using login form.
     *
     * @param string $username The username
     * @param string $password The password
     */
    public function user_login($username, $password) {
        global $DB, $CFG;
        if (!get_config('auth_magic', 'supportpassword')) {
            return false;
        }
        if (!$user = $DB->get_record('user', ['username' => $username, 'mnethostid' => $CFG->mnet_localhost_id])) {
            return false;
        }
        if (!validate_internal_user_password($user, $password)) {
            return false;
        }
        return true;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool
     */
    public function can_signup() {
        // Override if needed.
        return false;
    }


    /**
     * No password updates.
     *
     * @param string $user The username
     * @param string $newpassword The password
     */
    public function user_update_password($user, $newpassword) {
        if (get_config('auth_magic', 'supportpassword')) {
            $user = get_complete_user_data('id', $user->id);
            // This will also update the stored hash to the latest algorithm
            // if the existing hash is using an out-of-date algorithm (or the
            // legacy md5 algorithm).
            return update_internal_user_password($user, $newpassword);
        }
        return false;
    }

    /**
     * Don't store local passwords.
     *
     * @return bool True.
     */
    public function prevent_local_passwords() {
        // Just in case, we do not want to loose the passwords.
        return !get_config('auth_magic', 'supportpassword');
    }

    /**
     * No external data sync.
     *
     * @return bool
     */
    public function is_internal() {
        // We do not know if it was internal or external originally.
        return get_config('auth_magic', 'supportpassword');
    }

    /**
     * No changing of password.
     *
     * @return bool
     */
    public function can_change_password() {
        return get_config('auth_magic', 'supportpassword');
    }

    /**
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    public function change_password_url() {
        return null;
    }

    /**
     * No password resetting.
     */
    public function can_reset_password() {
        return get_config('auth_magic', 'supportpassword');
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
     * Create key for a specific user.
     *
     * @param int $userid User ID.
     * @param int $validuntil
     */
    public function create_user_key($userid, $validuntil) {
        return create_user_key(
            'auth/magic',
            $userid,
            null,
            null,
            $validuntil
        );
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
     * Delete the magic link of a user, with its keys.
     *
     * @param int $userid
     */
    public function delete_magic_instance(int $userid): void {
        global $DB;
        $this->delete_keys($userid);
        $DB->delete_records('auth_magic_loginlinks', ['userid' => $userid]);
    }

    /**
     * Whether the user may log in with a magic link.
     *
     * Magic accounts always may. Accounts with other login methods may if the plugin supports any
     * method and their own method is enabled, as the core login requires.
     *
     * @param stdClass $user
     * @return bool
     */
    public function can_login_with_link(stdClass $user): bool {
        if ($user->auth === $this->authtype) {
            return true;
        }
        return !empty(get_config('auth_magic', 'authmethod')) && $user->auth !== 'nologin' && is_enabled_auth($user->auth);
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
        global $CFG;
        // From Moodle 5.3 the login page shows the name as "Log in with ...".
        $name = (int) $CFG->branch >= 503 ? 'magiclinkprovider' : 'getmagiclinkviagmail';
        return [
            [
                'url' => new \moodle_url('/auth/magic/login.php'),
                'iconurl' => '',
                'name' => get_string($name, 'auth_magic'),
            ],
        ];
    }

    /**
     * Hook for overriding behaviour of login page.
     * This method is called from login/index.php page for all enabled auth plugins.
     */
    public function loginpage_hook() {
        global $PAGE;
        $params = [
            'loginhook' => true,
            'linkurl' => (new \moodle_url('/auth/magic/login.php'))->out(false),
        ];
        $PAGE->requires->js_call_amd('auth_magic/authmagic', 'init', [$params]);
    }


    /**
     * Create key for users.
     * @param object $user
     * @param bool $checkparent
     * @return void
     */
    public function create_magic_instance($user, $checkparent = true) {
        global $DB;

        if (!$DB->record_exists('auth_magic_loginlinks', ['userid' => $user->id])) {
            $loginexpiry = $this->get_login_expiry();
            $loginuserkey = $this->create_user_key($user->id, $loginexpiry);
            // Insert record.
            $record = new stdClass();
            $record->userid = $user->id;
            $record->parent = 0;
            $record->magicauth = ($checkparent) ? 1 : 0;
            $record->parentrole = ($checkparent) ? null : 0;
            $record->loginuserkey = $loginuserkey;
            $record->magiclogin = auth_magic_link_url($loginuserkey)->out(false);
            $record->loginexpiry = $loginexpiry;
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
        global $DB;
        if (!empty($keyinstance)) {
            // Delete the previous loginkey.
            $DB->delete_records('user_private_key', ['value' => $keyinstance->loginuserkey,
                'userid' => $keyinstance->userid]);
            $loginexpiry = $this->get_login_expiry();
            $loginuserkey = $this->create_user_key($user->id, $loginexpiry);
            $keyinstance->loginuserkey = $loginuserkey;
            $keyinstance->magiclogin = auth_magic_link_url($loginuserkey)->out(false);
            $keyinstance->loginexpiry = $loginexpiry;
            $keyinstance->timemodified = time();
            $DB->update_record('auth_magic_loginlinks', $keyinstance);
        }
    }

    /**
     * Expiry time for a login key created now.
     *
     * @return int Timestamp, 0 if login keys never expire
     */
    protected function get_login_expiry(): int {
        $expiry = (int) get_config('auth_magic', 'loginexpiry');
        return $expiry > 0 ? time() + $expiry : 0;
    }

    /**
     * Send a new link if the login key has expired.
     *
     * The user is told on the login page that a new link is on its way, unless the account may
     * not use magic links any more. Then the key is refused like any other expired key.
     *
     * @param string $key
     * @return bool
     */
    public function check_userkey_type($key) {
        global $DB;

        $instance = $DB->get_record('auth_magic_loginlinks', ['loginuserkey' => $key]);
        if (!$instance || empty($instance->loginexpiry) || $instance->loginexpiry >= time()) {
            return true;
        }
        $user = \core_user::get_user($instance->userid);
        if (!$user || $user->deleted || $user->suspended || !$this->can_login_with_link($user)) {
            return true;
        }
        if (auth_magic_may_send_link_email($user->id)) {
            $this->update_new_loginkey($user, $instance);
            auth_magic_sent_loginlink_touser($user->id, false, true);
        }
        if (!isloggedin()) {
            redirect(
                get_login_url(),
                get_string('loginexpiryloginlinkwithupdate', 'auth_magic'),
                null,
                \core\output\notification::NOTIFY_INFO
            );
        }
        return true;
    }
}
