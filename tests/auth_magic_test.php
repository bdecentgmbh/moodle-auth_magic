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
 *  Magic authendication test cases defined.
 *
 * @package   auth_magic
 * @copyright 2023 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_magic;

use stdClass;

/**
 * Magic authendication test cases defined.
 */
class auth_magic_test extends \advanced_testcase {
    /**
     * Set the admin user as User.
     *
     * @return void
     */
    public function setup(): void {
        global $CFG;
        require_once($CFG->dirroot."/auth/magic/auth.php");
        require_once($CFG->dirroot."/auth/magic/lib.php");
        $this->resetAfterTest(true);
        $this->setAdminUser();
        $this->auth = new \auth_plugin_magic();
        $this->generator = $this->getDataGenerator();
        $this->user1 = $this->generator->create_user(['auth' => 'magic']);
        $this->user2 = $this->generator->create_user(['auth' => 'magic']);
        $this->user3 = $this->generator->create_user(['auth' => 'magic']);

    }

    /**
     * Test that magic users can't login.
     * @covers ::users_can_not_login
     */
    public function test_users_can_not_login() {
        $user = new stdClass();
        $user->auth = 'magic';
        $user->username = 'user';
        $user->password = 'pass';
        $this->generator->create_user($user);
        $this->assertFalse($this->auth->user_login('user', 'pass'));
    }

    /**
     * Test create_user_key.
     * @covers ::create_user_key
     */
    public function test_create_user_key() {
        global $DB;
        $key = $this->auth->create_user_key($this->user3->id, 0);
        $userkeys = $DB->get_records('user_private_key', array('userid' => $this->user3->id));
        $this->assertTrue(count($userkeys) > 0);
    }

    /**
     * Test validate_key.
     * @covers ::validate_key
     */
    public function test_validate_key() {
        $suspenduser = $this->generator->create_user(['auth' => 'magic', 'suspended' => 1]);
        $deleteuser = $this->generator->create_user(['auth' => 'magic', 'deleted' => 1]);
        $user = $this->auth->create_user_key($this->user1->id, 0);
        $suspenduserkey = $this->auth->create_user_key($suspenduser->id, 0);
        $deleteuserkey = $this->auth->create_user_key($deleteuser->id, 0);
        $user1key = $this->auth->create_user_key($this->user1->id, 0);
        $user2key = $this->auth->create_user_key($this->user1->id, 1);
        $this->assertFalse($this->auth->validate_key($suspenduserkey));
        $this->assertFalse($this->auth->validate_key($deleteuserkey));
        $this->assertEquals($user1key, $this->auth->validate_key($user1key)->value);
        $this->assertFalse($this->auth->validate_key($user2key));
    }

    /**
     * Test user_delete_keys.
     * @covers ::user_delete_keys
     */
    public function test_user_delete_keys() {
        global $DB;
        $this->auth->create_user_key($this->user1->id, 0);
        $this->auth->delete_keys($this->user1->id);
        $userkeys = $DB->get_records('user_private_key', array('userid' => $this->user1->id));
        $this->assertTrue(count($userkeys) == 0);
    }


    /**
     * Test create_magic_instance.
     * @covers ::create_magic_instance
     */
    public function test_create_magic_instance() {
        global $DB;
        $user1 = $this->generator->create_user(['auth' => 'manual']);
        $user2 = $this->generator->create_user(['auth' => 'magic']);
        $user1keys = $DB->get_records('user_private_key', array('userid' => $user1->id));
        $user2keys = $DB->get_records('user_private_key', array('userid' => $user2->id));
        $this->assertTrue(count($user1keys) == 0);
        $this->assertTrue(count($user2keys) > 0);
        $this->auth->create_magic_instance($user1, true);
        $user1magicinstances = $DB->get_records('auth_magic_loginlinks', array('userid' => $user1->id));
        $user1keys = $DB->get_records('user_private_key', array('userid' => $user1->id));
        $this->assertTrue(count($user1magicinstances) > 0);
        $this->assertFalse(count($user1magicinstances) == 0);
        $this->assertTrue(count($user1keys) > 0);
    }
}
