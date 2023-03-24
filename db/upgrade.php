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
 * Upgrade function.
 *
 * @package    auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Magic authentication upgrade method.
 * @param int $oldversion
 * @return bool
 */
function xmldb_auth_magic_upgrade($oldversion) {
    global $CFG, $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2023032000) {
        // Define table auth_magic_loginlinks to be created.
        $table = new xmldb_table('auth_magic_loginlinks');
        $field = new xmldb_field('manualexpiry', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'loginexpiry');

        // Conditionally launch add field description_format.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Magic savepoint reached.
        upgrade_plugin_savepoint(true, 2023032000, 'auth', 'magic');
    }
    return true;
}
