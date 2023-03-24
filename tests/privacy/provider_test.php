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
 * Privacy test for the authentication magic
 *
 * @package    auth_magic
 * @category   test
 * @copyright  2018 Victor Deniz <victor@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_magic\privacy;

use auth_magic\privacy\provider;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;
use core_privacy\local\request\transform;
use core_privacy\local\request\approved_userlist;

/**
 * Privacy test for the authentication magic
 *
 * @package    auth_magic
 * @category   test
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider_test extends provider_testcase {
    /**
     * Set up method.
     */
    public function setUp(): void {
        $this->resetAfterTest(true);
    }

    /**
     * Check that a user context is returned if there is any user data for this user.
     */
    public function test_get_contexts_for_userid() {
        global $DB;

        $user = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $contextlist = provider::get_contexts_for_userid($user->id);

        // Check that we only get back one context.
        $this->assertCount(1, $contextlist);

        // Check that a context is returned is the expected.
        $usercontext = \context_user::instance($user->id);
        $this->assertEquals($usercontext->id, $contextlist->get_contextids()[0]);
    }

    /**
     * Test that user data is exported correctly.
     */
    public function test_export_user_data() {
        global $DB;

        $user = $this->getDataGenerator()->create_user(['auth' => 'magic']);

        $logrecord = $DB->get_record('auth_magic_loginlinks', array('userid' => $user->id));

        $usercontext = \context_user::instance($user->id);

        $writer = writer::with_context($usercontext);
        $this->assertFalse($writer->has_any_data());
        $approvedlist = new approved_contextlist($user, 'auth_magic', [$usercontext->id]);
        provider::export_user_data($approvedlist);

        $data = $writer->get_data([get_string('privacy:metadata:auth_magic', 'auth_magic').' '.$logrecord->userid]);
        $this->assertEquals(transform::datetime($logrecord->loginexpiry), reset($data)['loginexpiry']);
        $this->assertEquals(transform::datetime($logrecord->invitationexpiry), reset($data)['invitationexpiry']);
    }

    /**
     * Test deleting all user data for a specific context.
     */
    public function test_delete_data_for_all_users_in_context() {
        global $DB;

        $user1 = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $user1context = \context_user::instance($user1->id);

        $user2 = $this->getDataGenerator()->create_user(['auth' => 'magic']);

        // Get all magic login records.
        $records = $DB->get_records('auth_magic_loginlinks', array());
        // There should be two.
        $this->assertCount(2, $records);

        // Delete everything for the first user context.
        provider::delete_data_for_all_users_in_context($user1context);

        // Get all user1 magic login records.
        $records = $DB->get_records('auth_magic_loginlinks', ['userid' => $user1->id]);
        $this->assertCount(0, $records);

        // Get all magic login records.
        $records = $DB->get_records('auth_magic_loginlinks', array());
        // There should be one (user2).
        $this->assertCount(1, $records);
    }

    /**
     * This should work identical to the above test.
     */
    public function test_delete_data_for_user() {
        global $DB;

        $user1 = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $user1context = \context_user::instance($user1->id);

        $user2 = $this->getDataGenerator()->create_user(['auth' => 'magic']);

        // Get all magic login records.
        $records = $DB->get_records('auth_magic_loginlinks', array());
        // There should be two.
        $this->assertCount(2, $records);

        // Delete everything for the first user.
        $approvedlist = new approved_contextlist($user1, 'auth_magic', [$user1context->id]);
        provider::delete_data_for_user($approvedlist);

        // Get all user1 magic login records.
        $records = $DB->get_records('auth_magic_loginlinks', ['userid' => $user1->id]);
        $this->assertCount(0, $records);

        // Get all magic login records.
        $records = $DB->get_records('auth_magic_loginlinks', array());
        // There should be one (user2).
        $this->assertCount(1, $records);
    }

    /**
     * Test that only users with a user context are fetched.
     */
    public function test_get_users_in_context() {
        global $DB;

        $this->resetAfterTest();

        $component = 'auth_magic';
        // Create a user.
        $user = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $usercontext = \context_user::instance($user->id);

        // The list of users should not return anything yet (related data still haven't been created).
        $userlist = new \core_privacy\local\request\userlist($usercontext, $component);
        // The list of users for user context should return the user.
        provider::get_users_in_context($userlist);
        $this->assertCount(1, $userlist);
        $expected = [$user->id];
        $actual = $userlist->get_userids();
        $this->assertEquals($expected, $actual);

        // The list of users for system context should not return any users.
        $systemcontext = \context_system::instance();
        $userlist = new \core_privacy\local\request\userlist($systemcontext, $component);
        provider::get_users_in_context($userlist);
        $this->assertCount(0, $userlist);
    }

    /**
     * Test that data for users in approved userlist is deleted.
     */
    public function test_delete_data_for_users() {
        global $DB;

        $this->resetAfterTest();

        $component = 'auth_magic';
        // Create user1.
        $user1 = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $usercontext1 = \context_user::instance($user1->id);
        // Create user2.
        $user2 = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $usercontext2 = \context_user::instance($user2->id);

        // The list of users for usercontext1 should return user1.
        $userlist1 = new \core_privacy\local\request\userlist($usercontext1, $component);
        provider::get_users_in_context($userlist1);
        $this->assertCount(1, $userlist1);
        $expected = [$user1->id];
        $actual = $userlist1->get_userids();
        $this->assertEquals($expected, $actual);

        // The list of users for usercontext2 should return user2.
        $userlist2 = new \core_privacy\local\request\userlist($usercontext2, $component);
        provider::get_users_in_context($userlist2);
        $this->assertCount(1, $userlist2);
        $expected = [$user2->id];
        $actual = $userlist2->get_userids();
        $this->assertEquals($expected, $actual);

        // Add userlist1 to the approved user list.
        $approvedlist = new approved_userlist($usercontext1, $component, $userlist1->get_userids());

        // Delete user data using delete_data_for_user for usercontext1.
        provider::delete_data_for_users($approvedlist);

        // Re-fetch users in usercontext1 - The user list should now be empty.
        $userlist1 = new \core_privacy\local\request\userlist($usercontext1, $component);
        provider::get_users_in_context($userlist1);
        $this->assertCount(0, $userlist1);
        // Re-fetch users in usercontext2 - The user list should not be empty (user2).
        $userlist2 = new \core_privacy\local\request\userlist($usercontext2, $component);
        provider::get_users_in_context($userlist2);
        $this->assertCount(1, $userlist2);

        // User data should be only removed in the user context.
        $systemcontext = \context_system::instance();
        // Add userlist2 to the approved user list in the system context.
        $approvedlist = new approved_userlist($systemcontext, $component, $userlist2->get_userids());
        // Delete user1 data using delete_data_for_user.
        provider::delete_data_for_users($approvedlist);
        // Re-fetch users in usercontext2 - The user list should not be empty (user2).
        $userlist2 = new \core_privacy\local\request\userlist($usercontext2, $component);
        provider::get_users_in_context($userlist2);
        $this->assertCount(1, $userlist2);
    }
}
