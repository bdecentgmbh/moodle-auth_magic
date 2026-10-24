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
 * Uninstall cleanup for magic authentication.
 *
 * @package    auth_magic
 * @copyright  2026 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Remove what the plugin stored outside its own table and settings.
 *
 * Core drops those, but not the plugin's login keys in user_private_key and its user preference.
 *
 * @return bool
 */
function xmldb_auth_magic_uninstall() {
    global $DB;
    $DB->delete_records('user_private_key', ['script' => 'auth/magic']);
    $DB->delete_records('user_preferences', ['name' => 'auth_magic_lastlinkemail']);
    return true;
}
