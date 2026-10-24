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

#[\PHPUnit\Framework\Attributes\CoversFunction('auth_magic_get_email_user')]
#[\PHPUnit\Framework\Attributes\CoversFunction('auth_magic_process_link_request')]
#[\PHPUnit\Framework\Attributes\CoversFunction('auth_magic_may_send_link_email')]
/**
 * Tests for the functions in lib.php.
 *
 * @package    auth_magic
 * @copyright  2026 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers ::auth_magic_get_email_user
 * @covers ::auth_magic_process_link_request
 * @covers ::auth_magic_may_send_link_email
 */
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
     * Text of a captured email, with quoted-printable encoding removed.
     *
     * @param \stdClass $email Email from the email sink
     * @return string
     */
    protected function email_text(\stdClass $email): string {
        return quoted_printable_decode($email->body);
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
        $sink = $this->redirectEmails();

        auth_magic_process_link_request($user);

        $messages = $sink->get_messages();
        $this->assertCount(1, $messages);
        $this->assertEquals($user->email, $messages[0]->to);
        $key = $DB->get_field('auth_magic_loginlinks', 'loginuserkey', ['userid' => $user->id]);
        $this->assertStringContainsString('/auth/magic/login.php?key=' . $key, $this->email_text($messages[0]));
        // Links go by email from the no-reply address, with a plain text part without HTML.
        $this->assertEquals(\core_user::get_noreply_user()->email, $messages[0]->from);
        $this->assertStringContainsString('Content-Type: text/plain', $messages[0]->body);
        $plain = explode('Content-Type: text/html', $this->email_text($messages[0]))[0];
        $this->assertStringNotContainsString('<a href', $plain);
        $this->assertStringNotContainsString('<br', $plain);
    }

    /**
     * Accounts with other login methods get a link only if the plugin supports any method.
     */
    public function test_process_link_request_for_other_login_methods(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user(['auth' => 'manual']);
        set_config('requestinterval', 0, 'auth_magic');
        $sink = $this->redirectEmails();

        auth_magic_process_link_request($user);
        $messages = $sink->get_messages();
        $this->assertCount(1, $messages);
        $this->assertStringNotContainsString('/auth/magic/login.php', $this->email_text($messages[0]));
        $this->assertStringContainsString(
            'log in with a magic link. Please log in the way you usually do.',
            $this->email_text($messages[0])
        );
        $this->assertFalse($DB->record_exists('auth_magic_loginlinks', ['userid' => $user->id]));

        $sink->clear();
        set_config('authmethod', 1, 'auth_magic');
        auth_magic_process_link_request($user);
        $messages = $sink->get_messages();
        $this->assertCount(1, $messages);
        $this->assertStringContainsString('/auth/magic/login.php?key=', $this->email_text($messages[0]));
        $this->assertTrue($DB->record_exists('auth_magic_loginlinks', ['userid' => $user->id]));
    }

    /**
     * The notice for other login methods offers a password reset only if the method can reset passwords.
     */
    public function test_notice_offers_password_reset_only_if_possible(): void {
        set_config('requestinterval', 0, 'auth_magic');
        set_config('auth', 'magic,oauth2');
        $manual = $this->getDataGenerator()->create_user(['auth' => 'manual']);
        $oauth2 = $this->getDataGenerator()->create_user(['auth' => 'oauth2']);
        $disabled = $this->getDataGenerator()->create_user(['auth' => 'email']);
        $sink = $this->redirectEmails();

        foreach ([$manual, $oauth2, $disabled] as $user) {
            auth_magic_process_link_request($user);
        }

        $texts = array_map(fn($email) => $this->email_text($email), $sink->get_messages());
        $this->assertCount(3, $texts);
        $this->assertStringContainsString('/login/forgot_password.php', $texts[0]);
        $this->assertStringNotContainsString('/login/forgot_password.php', $texts[1]);
        $this->assertStringNotContainsString('/login/forgot_password.php', $texts[2]);
        foreach ($texts as $text) {
            $this->assertStringContainsString('asked for a magic link to log in', $text);
            $this->assertStringNotContainsString('new account', $text);
        }
    }

    /**
     * Suspended and unconfirmed accounts get nothing.
     */
    public function test_process_link_request_ignores_inactive_accounts(): void {
        $suspended = $this->getDataGenerator()->create_user(['auth' => 'magic', 'suspended' => 1]);
        $unconfirmed = $this->getDataGenerator()->create_user(['auth' => 'magic', 'confirmed' => 0]);
        $sink = $this->redirectEmails();

        auth_magic_process_link_request($suspended);
        auth_magic_process_link_request($unconfirmed);

        $this->assertCount(0, $sink->get_messages());
    }

    /**
     * The guest account and accounts that can't log in interactively get nothing, whatever the settings.
     */
    public function test_process_link_request_ignores_non_interactive_accounts(): void {
        global $CFG;

        set_config('authmethod', 1, 'auth_magic');
        set_config('loginoption', 1, 'auth_magic');
        set_config('auth', 'magic,webservice');
        $webservice = $this->getDataGenerator()->create_user(['auth' => 'webservice']);
        $nologin = $this->getDataGenerator()->create_user(['auth' => 'nologin']);
        $sink = $this->redirectEmails();

        auth_magic_process_link_request(\core_user::get_user($CFG->siteguest));
        auth_magic_process_link_request($webservice);
        auth_magic_process_link_request($nologin);

        $this->assertCount(0, $sink->get_messages());
    }

    /**
     * An account gets at most one email per request interval, whoever asks.
     */
    public function test_process_link_request_is_limited_per_account(): void {
        $user = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $other = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        set_config('requestinterval', 2 * MINSECS, 'auth_magic');
        $sink = $this->redirectEmails();

        auth_magic_process_link_request($user);
        auth_magic_process_link_request($user);
        auth_magic_process_link_request($other);
        $this->assertEquals([$user->email, $other->email], array_column($sink->get_messages(), 'to'));

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
