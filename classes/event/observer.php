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
 * Event observer for Magic authentication.
 *
 * @package    auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_magic\event;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot. "/auth/magic/auth.php");
require_once($CFG->dirroot. "/auth/magic/lib.php");

/**
 * Event observer for auth_magic.
 */
class observer {

    /**
     * Create user data creation request when the user is created.
     *
     * @param \core\event\user_created $event
     */
    public static function created_user_data_request(\core\event\user_created $event) {
        global $USER, $CFG;
        $userid = $event->objectid;
        $usercontext = \context_user::instance($userid);
        $user = \core_user::get_user($userid);
        if ($user->auth == 'magic') {
            $auth = get_auth_plugin('magic');
            // Request login url.
            $auth->create_magic_instance($user);
        }
        return true;
    }

    /**
     * Create user data deletion request when the user is deleted.
     *
     * @param \core\event\user_deleted $event
     *
     * @return bool
     */
    public static function create_delete_data_request(\core\event\user_deleted $event) {
        global $DB;
        $userid = $event->objectid;
        $DB->delete_records('auth_magic_loginlinks', array('userid' => $userid));
        delete_user_key('auth/magic', $userid);
        return true;
    }

    /**
     * Create user data update request when the user is updated.
     *
     * @param \core\event\user_updated $event
     *
     * @return bool
     */
    public static function create_update_data_request(\core\event\user_updated $event) {
        global $DB, $USER, $CFG;
        $userid = $event->objectid;
        $usercontext = \context_user::instance($userid);
        $user = \core_user::get_user($userid);
        if ($user->auth == 'magic') {
            $auth = get_auth_plugin('magic');
            // Request login url.
            $auth->create_magic_instance($user);
        }
        return true;
    }
}
