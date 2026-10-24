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
 * Tests for the magic authentication plugin and its login keys.
 *
 * @package   auth_magic
 * @copyright 2023 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\auth_plugin_magic::class)]
#[\PHPUnit\Framework\Attributes\CoversFunction('auth_magic_sent_loginlink_touser')]
#[\PHPUnit\Framework\Attributes\CoversFunction('auth_magic_get_user_login_link')]
#[\PHPUnit\Framework\Attributes\CoversFunction('auth_magic_authmethod_updated')]
#[\PHPUnit\Framework\Attributes\CoversClass(\auth_magic\event\observer::class)]
final class auth_magic_test extends \advanced_testcase {
    /** @var \auth_plugin_magic */
    protected $auth;

    #[\Override]
    public static function setUpBeforeClass(): void {
        global $CFG;
        require_once($CFG->dirroot . '/auth/magic/auth.php');
        parent::setUpBeforeClass();
    }

    #[\Override]
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->auth = get_auth_plugin('magic');
    }

    /**
     * Magic users can't log in with a password unless the plugin supports passwords.
     */
    public function test_users_can_not_login(): void {
        $this->getDataGenerator()->create_user(['auth' => 'magic', 'username' => 'user', 'password' => 'pass']);
        $this->assertFalse($this->auth->user_login('user', 'pass'));

        set_config('supportpassword', 1, 'auth_magic');
        $this->assertTrue($this->auth->user_login('user', 'pass'));
    }

    /**
     * Keys are created and deleted for a user.
     */
    public function test_create_and_delete_user_keys(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $this->auth->create_user_key($user->id, 0);
        $this->assertTrue($DB->record_exists('user_private_key', ['userid' => $user->id, 'script' => 'auth/magic']));

        $this->auth->delete_keys($user->id);
        $this->assertFalse($DB->record_exists('user_private_key', ['userid' => $user->id, 'script' => 'auth/magic']));
    }

    /**
     * Magic accounts get a login key when they are created, other accounts when they ask for one.
     */
    public function test_create_magic_instance(): void {
        global $DB;

        $manual = $this->getDataGenerator()->create_user(['auth' => 'manual']);
        $magic = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $this->assertFalse($DB->record_exists('auth_magic_loginlinks', ['userid' => $manual->id]));
        $this->assertCount(1, $DB->get_records('user_private_key', ['userid' => $magic->id]));

        $this->auth->create_magic_instance($manual, false);
        $instance = $DB->get_record('auth_magic_loginlinks', ['userid' => $manual->id], '*', MUST_EXIST);
        $this->assertCount(1, $DB->get_records('user_private_key', ['userid' => $manual->id]));
        // Free creates no invitation keys, nothing sends them.
        $this->assertNull($instance->invitationuserkey);
        $this->assertNull($instance->magicinvitation);
    }

    /**
     * A login link that never expires is sent once, without renewing the key.
     */
    public function test_send_link_that_never_expires(): void {
        global $DB;

        set_config('loginexpiry', 0, 'auth_magic');
        $user = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $key = $DB->get_field('auth_magic_loginlinks', 'loginuserkey', ['userid' => $user->id]);
        $sink = $this->redirectMessages();

        $this->assertTrue(auth_magic_sent_loginlink_touser($user->id));

        $messages = $sink->get_messages();
        $this->assertCount(1, $messages);
        $this->assertStringContainsString('key=' . $key, $messages[0]->fullmessagehtml);
        $this->assertStringContainsString(get_string('never'), $messages[0]->fullmessagehtml);
        $this->assertEquals($key, $DB->get_field('auth_magic_loginlinks', 'loginuserkey', ['userid' => $user->id]));
    }

    /**
     * An expired or missing login key is replaced before the link is sent.
     */
    public function test_send_link_replaces_unusable_key(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $instance = $DB->get_record('auth_magic_loginlinks', ['userid' => $user->id]);
        $DB->set_field('auth_magic_loginlinks', 'loginexpiry', time() - 1, ['id' => $instance->id]);
        $sink = $this->redirectMessages();

        auth_magic_sent_loginlink_touser($user->id);
        $newkey = $DB->get_field('auth_magic_loginlinks', 'loginuserkey', ['userid' => $user->id]);
        $this->assertNotEquals($instance->loginuserkey, $newkey);
        $this->assertFalse($DB->record_exists('user_private_key', ['value' => $instance->loginuserkey]));
        $this->assertStringContainsString('key=' . $newkey, $sink->get_messages()[0]->fullmessagehtml);

        // A key that is gone, for example after a single-use login, is replaced as well.
        $DB->delete_records('user_private_key', ['userid' => $user->id]);
        auth_magic_sent_loginlink_touser($user->id);
        $key = $DB->get_field('auth_magic_loginlinks', 'loginuserkey', ['userid' => $user->id]);
        $this->assertTrue($DB->record_exists('user_private_key', ['value' => $key, 'userid' => $user->id]));
    }

    /**
     * Links are built from the key, so that they follow the site URL.
     */
    public function test_login_link_follows_site_url(): void {
        global $CFG, $DB;

        $user = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $key = $DB->get_field('auth_magic_loginlinks', 'loginuserkey', ['userid' => $user->id]);
        $CFG->wwwroot = 'https://moved.example.com';

        $this->assertEquals(
            'https://moved.example.com/auth/magic/login.php?key=' . $key,
            auth_magic_get_user_login_link($user->id)
        );
        $this->assertSame('', auth_magic_get_user_login_link($user->id + 1));
    }

    /**
     * Using an expired link sends a new one, at most once per request interval.
     */
    public function test_check_userkey_type_sends_new_link_for_expired_key(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $instance = $DB->get_record('auth_magic_loginlinks', ['userid' => $user->id]);
        $DB->set_field('auth_magic_loginlinks', 'loginexpiry', time() - 1, ['id' => $instance->id]);
        set_config('requestinterval', 2 * MINSECS, 'auth_magic');
        // Logged in, so that the check doesn't redirect to the login page.
        $this->setUser($this->getDataGenerator()->create_user());
        $sink = $this->redirectMessages();

        $this->auth->check_userkey_type($instance->loginuserkey);
        $messages = $sink->get_messages();
        $this->assertCount(1, $messages);
        $newkey = $DB->get_field('auth_magic_loginlinks', 'loginuserkey', ['userid' => $user->id]);
        $this->assertNotEquals($instance->loginuserkey, $newkey);
        $this->assertStringContainsString('key=' . $newkey, $messages[0]->fullmessagehtml);

        // Within the interval, the old link doesn't send another one.
        $sink->clear();
        $DB->set_field('auth_magic_loginlinks', 'loginexpiry', time() - 1, ['userid' => $user->id]);
        $this->auth->check_userkey_type($newkey);
        $this->assertCount(0, $sink->get_messages());
        $this->assertEquals($newkey, $DB->get_field('auth_magic_loginlinks', 'loginuserkey', ['userid' => $user->id]));
    }

    /**
     * Keys that are still valid, unknown keys and keys without expiry are left alone.
     */
    public function test_check_userkey_type_ignores_usable_keys(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $key = $DB->get_field('auth_magic_loginlinks', 'loginuserkey', ['userid' => $user->id]);
        $sink = $this->redirectMessages();

        $this->assertTrue($this->auth->check_userkey_type($key));
        $this->assertTrue($this->auth->check_userkey_type('unknown'));
        $DB->set_field('auth_magic_loginlinks', 'loginexpiry', 0, ['userid' => $user->id]);
        $this->assertTrue($this->auth->check_userkey_type($key));

        $this->assertCount(0, $sink->get_messages());
    }

    /**
     * Other login methods may use links only if the plugin supports any method and they are enabled.
     */
    public function test_can_login_with_link(): void {
        $magic = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $manual = $this->getDataGenerator()->create_user(['auth' => 'manual']);
        $nologin = $this->getDataGenerator()->create_user(['auth' => 'nologin']);
        $email = $this->getDataGenerator()->create_user(['auth' => 'email']);
        set_config('auth', 'magic');

        $this->assertTrue($this->auth->can_login_with_link($magic));
        $this->assertFalse($this->auth->can_login_with_link($manual));

        set_config('authmethod', 1, 'auth_magic');
        $this->assertTrue($this->auth->can_login_with_link($manual));
        $this->assertFalse($this->auth->can_login_with_link($nologin));
        // Like the core login, accounts of a disabled login method can't log in.
        $this->assertFalse($this->auth->can_login_with_link($email));
        set_config('auth', 'magic,email');
        $this->assertTrue($this->auth->can_login_with_link($email));
    }

    /**
     * An account that moves to a login method that may not use links loses its link.
     */
    public function test_changing_login_method_removes_link(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $other = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $changeauth = function (int $userid): void {
            global $DB;
            $DB->set_field('user', 'auth', 'manual', ['id' => $userid]);
            \core\event\user_updated::create_from_userid($userid)->trigger();
        };
        set_config('authmethod', 1, 'auth_magic');
        $changeauth($other->id);
        $this->assertTrue($DB->record_exists('auth_magic_loginlinks', ['userid' => $other->id]));

        set_config('authmethod', 0, 'auth_magic');
        $changeauth($user->id);
        $this->assertFalse($DB->record_exists('auth_magic_loginlinks', ['userid' => $user->id]));
        $this->assertFalse($DB->record_exists('user_private_key', ['userid' => $user->id, 'script' => 'auth/magic']));
    }

    /**
     * Going back to magic only removes the links of accounts with other login methods.
     */
    public function test_authmethod_updated_removes_links_of_other_methods(): void {
        global $DB;

        set_config('authmethod', 1, 'auth_magic');
        $manual = $this->getDataGenerator()->create_user(['auth' => 'manual']);
        $magic = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $this->auth->create_magic_instance($manual, false);

        auth_magic_authmethod_updated();
        $this->assertTrue($DB->record_exists('auth_magic_loginlinks', ['userid' => $manual->id]));

        set_config('authmethod', 0, 'auth_magic');
        auth_magic_authmethod_updated();
        $this->assertFalse($DB->record_exists('auth_magic_loginlinks', ['userid' => $manual->id]));
        $this->assertFalse($DB->record_exists('user_private_key', ['userid' => $manual->id, 'script' => 'auth/magic']));
        $this->assertTrue($DB->record_exists('auth_magic_loginlinks', ['userid' => $magic->id]));
        $this->assertTrue($DB->record_exists('user_private_key', ['userid' => $magic->id, 'script' => 'auth/magic']));
    }

    /**
     * Deleting an account deletes its link and keys.
     */
    public function test_deleting_user_removes_link(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        delete_user($user);

        $this->assertFalse($DB->record_exists('auth_magic_loginlinks', ['userid' => $user->id]));
        $this->assertFalse($DB->record_exists('user_private_key', ['userid' => $user->id, 'script' => 'auth/magic']));
    }
}
