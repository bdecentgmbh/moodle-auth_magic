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
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;

#[\PHPUnit\Framework\Attributes\CoversClass(provider::class)]
/**
 * Privacy tests for magic authentication.
 *
 * @package    auth_magic
 * @category   test
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \auth_magic\privacy\provider
 */
final class provider_test extends provider_testcase {
    #[\Override]
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * The table, the core user keys and the preference are declared.
     */
    public function test_get_metadata(): void {
        $items = provider::get_metadata(new collection('auth_magic'))->get_collection();
        $names = array_map(fn($item) => $item->get_name(), $items);

        $this->assertContains('auth_magic_loginlinks', $names);
        $this->assertContains('core_userkey', $names);
        $this->assertContains('auth_magic_lastlinkemail', $names);
    }

    /**
     * Users with a magic link or a key have their user context, others have none.
     */
    public function test_get_contexts_for_userid(): void {
        $magic = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $manual = $this->getDataGenerator()->create_user(['auth' => 'manual']);
        $keyonly = $this->getDataGenerator()->create_user(['auth' => 'manual']);
        create_user_key('auth/magic', $keyonly->id);

        $contextids = provider::get_contexts_for_userid($magic->id)->get_contextids();
        $this->assertEquals([\context_user::instance($magic->id)->id], $contextids);
        $this->assertCount(0, provider::get_contexts_for_userid($manual->id));
        $this->assertCount(1, provider::get_contexts_for_userid($keyonly->id));
    }

    /**
     * Only the user of a user context is listed, and only if they have a magic link.
     */
    public function test_get_users_in_context(): void {
        $magic = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $manual = $this->getDataGenerator()->create_user(['auth' => 'manual']);

        $userlist = new userlist(\context_user::instance($magic->id), 'auth_magic');
        provider::get_users_in_context($userlist);
        $this->assertEquals([$magic->id], $userlist->get_userids());

        $userlist = new userlist(\context_user::instance($manual->id), 'auth_magic');
        provider::get_users_in_context($userlist);
        $this->assertCount(0, $userlist);

        $userlist = new userlist(\context_system::instance(), 'auth_magic');
        provider::get_users_in_context($userlist);
        $this->assertCount(0, $userlist);
    }

    /**
     * The export has the dates of the link and the keys, but not the key itself.
     */
    public function test_export_user_data(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $link = $DB->get_record('auth_magic_loginlinks', ['userid' => $user->id]);
        $context = \context_user::instance($user->id);
        // Core exports the user keys of the current user, who is the user of the export.
        $this->setUser($user);

        $this->export_context_data_for_user($user->id, $context, 'auth_magic');

        $writer = writer::with_context($context);
        $subcontext = [get_string('pluginname', 'auth_magic')];
        $data = $writer->get_data($subcontext);
        $keys = $writer->get_related_data($subcontext, 'userkeys');
        $this->assertNotEmpty($data->loginexpiry);
        $this->assertNotEmpty($data->timecreated);
        $this->assertCount(1, $keys->keys);
        $this->assertStringNotContainsString($link->loginuserkey, json_encode([$data, $keys]));
    }

    /**
     * The time of the last link email is exported.
     */
    public function test_export_user_preferences(): void {
        $user = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        provider::export_user_preferences($user->id);
        $this->assertFalse(writer::with_context(\context_system::instance())->has_any_data());

        set_user_preference('auth_magic_lastlinkemail', time(), $user);
        provider::export_user_preferences($user->id);
        $preferences = writer::with_context(\context_system::instance())->get_user_preferences('auth_magic');
        $this->assertObjectHasProperty('auth_magic_lastlinkemail', $preferences);
    }

    /**
     * Deleting the data of a user removes their link, keys and preference, and nothing of others.
     */
    public function test_delete_data_for_user(): void {
        [$user1, $user2] = $this->create_users_with_data();

        $contextids = [\context_user::instance($user1->id)->id];
        provider::delete_data_for_user(new approved_contextlist($user1, 'auth_magic', $contextids));

        $this->assert_user_has_data($user1, false);
        $this->assert_user_has_data($user2, true);
    }

    /**
     * Deleting the data in a user context removes the data of that user.
     */
    public function test_delete_data_for_all_users_in_context(): void {
        [$user1, $user2] = $this->create_users_with_data();

        provider::delete_data_for_all_users_in_context(\context_user::instance($user1->id));
        provider::delete_data_for_all_users_in_context(\context_system::instance());

        $this->assert_user_has_data($user1, false);
        $this->assert_user_has_data($user2, true);
    }

    /**
     * Deleting the data of approved users only works in their own user context.
     */
    public function test_delete_data_for_users(): void {
        [$user1, $user2] = $this->create_users_with_data();

        provider::delete_data_for_users(new approved_userlist(\context_system::instance(), 'auth_magic', [$user2->id]));
        provider::delete_data_for_users(new approved_userlist(\context_user::instance($user1->id), 'auth_magic', [$user1->id]));

        $this->assert_user_has_data($user1, false);
        $this->assert_user_has_data($user2, true);
    }

    /**
     * Create two magic users who were sent a link email.
     *
     * @return \stdClass[]
     */
    protected function create_users_with_data(): array {
        $users = [];
        for ($i = 0; $i < 2; $i++) {
            $user = $this->getDataGenerator()->create_user(['auth' => 'magic']);
            set_user_preference('auth_magic_lastlinkemail', time(), $user);
            $users[] = $user;
        }
        return $users;
    }

    /**
     * Assert whether a user still has a magic link, keys and the preference.
     *
     * @param \stdClass $user
     * @param bool $expected
     */
    protected function assert_user_has_data(\stdClass $user, bool $expected): void {
        global $DB;
        $this->assertEquals($expected, $DB->record_exists('auth_magic_loginlinks', ['userid' => $user->id]));
        $this->assertEquals($expected, $DB->record_exists('user_private_key', ['userid' => $user->id, 'script' => 'auth/magic']));
        $preference = ['userid' => $user->id, 'name' => 'auth_magic_lastlinkemail'];
        $this->assertEquals($expected, $DB->record_exists('user_preferences', $preference));
    }
}
