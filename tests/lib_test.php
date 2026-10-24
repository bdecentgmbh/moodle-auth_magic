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

namespace auth_magic;

/**
 * Tests for the functions in lib.php.
 *
 * @package    auth_magic
 * @copyright  2026 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversFunction('auth_magic_get_email_user')]
#[\PHPUnit\Framework\Attributes\CoversFunction('auth_magic_process_link_request')]
#[\PHPUnit\Framework\Attributes\CoversFunction('auth_magic_may_send_link_email')]
final class lib_test extends \advanced_testcase {
    #[\Override]
    public static function setUpBeforeClass(): void {
        global $CFG;
        require_once($CFG->dirroot . '/auth/magic/lib.php');
        parent::setUpBeforeClass();
    }

    #[\Override]
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Emails are matched without regard to case or surrounding spaces.
     */
    public function test_get_email_user_matches_email_case_insensitively(): void {
        $user = $this->getDataGenerator()->create_user(['email' => 'Anna.Muster@Example.com']);

        $this->assertEquals($user->id, auth_magic_get_email_user('anna.muster@example.com')->id);
        $this->assertEquals($user->id, auth_magic_get_email_user(' ANNA.MUSTER@EXAMPLE.COM ')->id);
        $this->assertNull(auth_magic_get_email_user('someone.else@example.com'));
        $this->assertNull(auth_magic_get_email_user(''));
    }

    /**
     * Deleted accounts and accounts of other MNet hosts are never matched.
     */
    public function test_get_email_user_ignores_deleted_and_remote_accounts(): void {
        global $CFG, $DB;

        $deleted = $this->getDataGenerator()->create_user(['email' => 'gone@example.com']);
        $DB->set_field('user', 'deleted', 1, ['id' => $deleted->id]);
        $this->getDataGenerator()->create_user([
            'email' => 'remote@example.com',
            'mnethostid' => $CFG->mnet_localhost_id + 1,
        ]);

        $this->assertNull(auth_magic_get_email_user('gone@example.com'));
        $this->assertNull(auth_magic_get_email_user('remote@example.com'));
    }

    /**
     * An email that several accounts share matches none of them.
     */
    public function test_get_email_user_refuses_a_shared_email(): void {
        set_config('allowaccountssameemail', 1);
        $this->getDataGenerator()->create_user(['email' => 'family@example.com']);
        $this->getDataGenerator()->create_user(['email' => 'family@example.com']);

        $this->assertNull(auth_magic_get_email_user('family@example.com'));
    }

    /**
     * Usernames are matched only if the plugin allows them.
     */
    public function test_get_email_user_matches_username_only_if_allowed(): void {
        $user = $this->getDataGenerator()->create_user(['username' => 'anna', 'email' => 'anna@example.com']);

        $this->assertNull(auth_magic_get_email_user('anna'));

        set_config('loginoption', 1, 'auth_magic');
        $this->assertEquals($user->id, auth_magic_get_email_user('Anna')->id);
        $this->assertEquals($user->id, auth_magic_get_email_user('anna@example.com')->id);
    }

    /**
     * A magic account gets its login link.
     */
    public function test_process_link_request_sends_link_to_magic_account(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $sink = $this->redirectMessages();

        auth_magic_process_link_request($user);

        $messages = $sink->get_messages();
        $this->assertCount(1, $messages);
        $this->assertEquals($user->id, $messages[0]->useridto);
        $key = $DB->get_field('auth_magic_loginlinks', 'loginuserkey', ['userid' => $user->id]);
        $this->assertStringContainsString('/auth/magic/login.php?key=' . $key, $messages[0]->fullmessagehtml);
    }

    /**
     * Accounts with other login methods get a link only if the plugin supports any method.
     */
    public function test_process_link_request_for_other_login_methods(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user(['auth' => 'manual']);
        set_config('requestinterval', 0, 'auth_magic');
        $sink = $this->redirectMessages();

        auth_magic_process_link_request($user);
        $messages = $sink->get_messages();
        $this->assertCount(1, $messages);
        $this->assertStringNotContainsString('/auth/magic/login.php', $messages[0]->fullmessagehtml);
        $this->assertFalse($DB->record_exists('auth_magic_loginlinks', ['userid' => $user->id]));

        $sink->clear();
        set_config('authmethod', 1, 'auth_magic');
        auth_magic_process_link_request($user);
        $messages = $sink->get_messages();
        $this->assertCount(1, $messages);
        $this->assertStringContainsString('/auth/magic/login.php?key=', $messages[0]->fullmessagehtml);
        $this->assertTrue($DB->record_exists('auth_magic_loginlinks', ['userid' => $user->id]));
    }

    /**
     * Suspended and unconfirmed accounts get nothing.
     */
    public function test_process_link_request_ignores_inactive_accounts(): void {
        $suspended = $this->getDataGenerator()->create_user(['auth' => 'magic', 'suspended' => 1]);
        $unconfirmed = $this->getDataGenerator()->create_user(['auth' => 'magic', 'confirmed' => 0]);
        $sink = $this->redirectMessages();

        auth_magic_process_link_request($suspended);
        auth_magic_process_link_request($unconfirmed);

        $this->assertCount(0, $sink->get_messages());
    }

    /**
     * An account gets at most one email per request interval, whoever asks.
     */
    public function test_process_link_request_is_limited_per_account(): void {
        $user = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $other = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        set_config('requestinterval', 2 * MINSECS, 'auth_magic');
        $sink = $this->redirectMessages();

        auth_magic_process_link_request($user);
        auth_magic_process_link_request($user);
        auth_magic_process_link_request($other);
        $this->assertEquals([$user->id, $other->id], array_column($sink->get_messages(), 'useridto'));

        // Once the interval has passed, the account gets the next email.
        $sink->clear();
        set_user_preference('auth_magic_lastlinkemail', time() - 2 * MINSECS - 1, $user);
        auth_magic_process_link_request($user);
        $this->assertCount(1, $sink->get_messages());
    }

    /**
     * An interval of 0 turns the limit off.
     */
    public function test_may_send_link_email_without_interval(): void {
        $user = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        set_config('requestinterval', 0, 'auth_magic');

        $this->assertTrue(auth_magic_may_send_link_email($user->id));
        $this->assertTrue(auth_magic_may_send_link_email($user->id));
        $this->assertNull(get_user_preferences('auth_magic_lastlinkemail', null, $user));
    }
}
