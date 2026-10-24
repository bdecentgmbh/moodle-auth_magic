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

namespace auth_magic\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider for magic authentication.
 *
 * A user's magic link is a row in auth_magic_loginlinks and a key in the core user_private_key table.
 * Both live in the user context.
 *
 * @package    auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\user_preference_provider {
    /** @var string Script of the plugin's keys in user_private_key. */
    private const KEYSCRIPT = 'auth/magic';

    /** @var string Preference with the time of the last link email. */
    private const LASTEMAILPREFERENCE = 'auth_magic_lastlinkemail';

    /**
     * Describe the personal data the plugin stores.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('auth_magic_loginlinks', [
            'userid' => 'privacy:metadata:auth_magic:userid',
            'loginuserkey' => 'privacy:metadata:auth_magic:loginuserkey',
            'magiclogin' => 'privacy:metadata:auth_magic:magiclogin',
            'loginexpiry' => 'privacy:metadata:auth_magic:loginexpiry',
            'timecreated' => 'privacy:metadata:auth_magic:timecreated',
            'timemodified' => 'privacy:metadata:auth_magic:timemodified',
        ], 'privacy:metadata:auth_magic_loginlinks');
        $collection->add_subsystem_link('core_userkey', [], 'privacy:metadata:core_userkey');
        $collection->add_user_preference(self::LASTEMAILPREFERENCE, 'privacy:metadata:preference:lastlinkemail');
        return $collection;
    }

    /**
     * The user context, if the user has a magic link or key.
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                 WHERE ctx.contextlevel = :contextlevel AND ctx.instanceid = :userid
                       AND (EXISTS (SELECT 1 FROM {auth_magic_loginlinks} ml WHERE ml.userid = ctx.instanceid)
                            OR EXISTS (SELECT 1 FROM {user_private_key} k WHERE k.userid = ctx.instanceid AND k.script = :script))";
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_USER,
            'userid' => $userid,
            'script' => self::KEYSCRIPT,
        ]);
        return $contextlist;
    }

    /**
     * The user of a user context, if they have a magic link or key.
     *
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if (!$context instanceof \context_user) {
            return;
        }
        $userlist->add_from_sql(
            'userid',
            'SELECT userid FROM {auth_magic_loginlinks} WHERE userid = :userid',
            ['userid' => $context->instanceid]
        );
        $userlist->add_from_sql(
            'userid',
            'SELECT userid FROM {user_private_key} WHERE userid = :userid AND script = :script',
            ['userid' => $context->instanceid, 'script' => self::KEYSCRIPT]
        );
    }

    /**
     * Export the magic link of the user, without the key, which works like a password.
     *
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $user = $contextlist->get_user();
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_USER || $context->instanceid != $user->id) {
                continue;
            }
            $subcontext = [get_string('pluginname', 'auth_magic')];
            $link = $DB->get_record('auth_magic_loginlinks', ['userid' => $user->id]);
            if ($link) {
                writer::with_context($context)->export_data($subcontext, (object) [
                    'loginexpiry' => $link->loginexpiry ? transform::datetime($link->loginexpiry) : get_string('never'),
                    'timecreated' => transform::datetime($link->timecreated),
                    'timemodified' => $link->timemodified ? transform::datetime($link->timemodified) : '-',
                ]);
            }
            \core_userkey\privacy\provider::export_userkeys($context, $subcontext, self::KEYSCRIPT);
        }
    }

    /**
     * Export the time the user was last sent a link email.
     *
     * @param int $userid
     */
    public static function export_user_preferences(int $userid) {
        $lastemail = get_user_preferences(self::LASTEMAILPREFERENCE, null, $userid);
        if ($lastemail !== null) {
            writer::export_user_preference(
                'auth_magic',
                self::LASTEMAILPREFERENCE,
                transform::datetime($lastemail),
                get_string('privacy:metadata:preference:lastlinkemail', 'auth_magic')
            );
        }
    }

    /**
     * Delete the data of the users in a user context.
     *
     * @param approved_userlist $userlist
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        $context = $userlist->get_context();
        if ($context instanceof \context_user && in_array($context->instanceid, $userlist->get_userids())) {
            self::delete_user_magicdata($context->instanceid);
        }
    }

    /**
     * Delete the data of a user.
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_USER && $context->instanceid == $userid) {
                self::delete_user_magicdata($userid);
            }
        }
    }

    /**
     * Delete the data in a context.
     *
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        if ($context->contextlevel == CONTEXT_USER) {
            self::delete_user_magicdata($context->instanceid);
        }
    }

    /**
     * Delete the magic link, the keys and the preference of a user.
     *
     * @param int $userid
     */
    private static function delete_user_magicdata(int $userid) {
        global $DB;
        $DB->delete_records('auth_magic_loginlinks', ['userid' => $userid]);
        \core_userkey\privacy\provider::delete_userkeys(self::KEYSCRIPT, $userid);
        unset_user_preference(self::LASTEMAILPREFERENCE, $userid);
    }
}
