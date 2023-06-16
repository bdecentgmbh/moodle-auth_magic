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
 * Authentication Plugin: Magic Authentication external functions.
 *
 *
 * @package     auth_magic
 * @copyright   2023 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 defined('MOODLE_INTERNAL') || die();
 require_once($CFG->libdir.'/externallib.php');

/**
 * Define external class.
 */
class external extends \external_api {


    /**
     * Parameters define to the get magic link.
     *
     * @return array list of option parameters
     */
    public static function get_magiclink_passcheck_parameters() {
        return new external_function_parameters(
            array(
                'email' => new external_value(PARAM_RAW_TRIMMED, 'User email'),
                'password' => new external_value(PARAM_RAW, 'User password')
            )
        );
    }

    /**
     * Check auth login.
     *
     * @param array $email email
     * @param string $password password
     *
     * @return bool status
     */
    public static function get_magiclink_passcheck($email, $password) {
        global $CFG;
        require_once($CFG->dirroot. "/auth/magic/lib.php");
        $status = false;
        $params = self::validate_parameters(self::get_magiclink_passcheck_parameters(),
            array('email' => $email, 'password' => $password));
        if (auth_magic_has_pro()) {
            require_once($CFG->dirroot. "/local/magic/lib.php");
            $userrecord = local_magic_get_email_user($email);
            $user = authenticate_user_login($userrecord->username, $params['password']);
            $status = ($user) ? true : false;
        }
        return ["status" => $status];
    }

    /**
     * Returns magic links and expiration time.
     *
     * @return array magic link and expiration time.
     */
    public static function get_magiclink_passcheck_returns() {
        return new external_single_structure(
            array(
                'status' => new \external_value(PARAM_BOOL, 'Return status'),
            )
        );
    }

}
