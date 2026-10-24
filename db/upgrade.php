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
    global $DB;
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

    if ($oldversion < 2026102400) {
        $table = new xmldb_table('auth_magic_loginlinks');

        // A user has one magic link. Keep the newest of any duplicates, so that userid can be unique.
        $duplicates = $DB->get_records_sql(
            "SELECT userid, MAX(id) AS keepid
               FROM {auth_magic_loginlinks}
              WHERE userid IS NOT NULL
           GROUP BY userid
             HAVING COUNT(1) > 1"
        );
        foreach ($duplicates as $duplicate) {
            $links = $DB->get_records_select(
                'auth_magic_loginlinks',
                'userid = :userid AND id <> :keepid',
                ['userid' => $duplicate->userid, 'keepid' => $duplicate->keepid]
            );
            foreach ($links as $link) {
                foreach ([$link->loginuserkey, $link->invitationuserkey] as $key) {
                    if ($key) {
                        $DB->delete_records('user_private_key', ['script' => 'auth/magic', 'value' => $key]);
                    }
                }
                $DB->delete_records('auth_magic_loginlinks', ['id' => $link->id]);
            }
        }
        if (!$dbman->index_exists($table, new xmldb_index('userid', XMLDB_INDEX_UNIQUE, ['userid']))) {
            $dbman->add_key($table, new xmldb_key('userid', XMLDB_KEY_FOREIGN_UNIQUE, ['userid'], 'user', ['id']));
        }

        // Links are looked up by their key.
        $index = new xmldb_index('loginuserkey', XMLDB_INDEX_NOTUNIQUE, ['loginuserkey']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Free never sent the invitation keys it created, and without the removed invitation expiry
        // setting they never expired. Magic Authentication Pro, which sends invitations, has its own upgrade.
        $DB->execute(
            "DELETE FROM {user_private_key}
              WHERE script = :script
                    AND value IN (SELECT invitationuserkey FROM {auth_magic_loginlinks} WHERE invitationuserkey IS NOT NULL)",
            ['script' => 'auth/magic']
        );
        $DB->execute(
            "UPDATE {auth_magic_loginlinks} SET invitationuserkey = NULL, magicinvitation = NULL, invitationexpiry = NULL"
        );
        unset_config('invitationexpiry', 'auth_magic');

        // Magic savepoint reached.
        upgrade_plugin_savepoint(true, 2026102400, 'auth', 'magic');
    }
    return true;
}
