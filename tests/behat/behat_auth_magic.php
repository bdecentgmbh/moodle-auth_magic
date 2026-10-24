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

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Steps for magic login links.
 *
 * The links are read from the database, as the emails that carry them are not sent in Behat.
 *
 * @package    auth_magic
 * @category   test
 * @copyright  2026 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_auth_magic extends behat_base {
    /** @var moodle_url|null The magic login link opened last. */
    protected static $lastlink = null;

    /**
     * Opens the current magic login link of a user.
     *
     * @When /^I open the magic login link of "(?P<username_string>(?:[^"]|\\")*)"$/
     * @param string $username
     */
    public function i_open_the_magic_login_link_of(string $username): void {
        global $DB;

        $userid = $DB->get_field('user', 'id', ['username' => $username], MUST_EXIST);
        $key = $DB->get_field('auth_magic_loginlinks', 'loginuserkey', ['userid' => $userid], MUST_EXIST);
        self::$lastlink = new moodle_url('/auth/magic/login.php', ['key' => $key]);
        $this->execute('behat_general::i_visit', [self::$lastlink]);
    }

    /**
     * Opens the current magic login link of a user and confirms the login.
     *
     * @When /^I log in with the magic login link of "(?P<username_string>(?:[^"]|\\")*)"$/
     * @param string $username
     */
    public function i_log_in_with_the_magic_login_link_of(string $username): void {
        $this->i_open_the_magic_login_link_of($username);
        $this->execute('behat_forms::press_button', [get_string('login')]);
    }

    /**
     * Opens the magic login link opened last once more and confirms the login.
     *
     * @When /^I log in with the last magic login link again$/
     */
    public function i_log_in_with_the_last_magic_login_link_again(): void {
        $this->i_open_the_last_magic_login_link_again();
        $this->execute('behat_forms::press_button', [get_string('login')]);
    }

    /**
     * Clicks the magic link button of the login page, whose label differs between Moodle versions.
     *
     * @When /^I click on the magic link button$/
     */
    public function i_click_on_the_magic_link_button(): void {
        $this->execute('behat_general::i_click_on', ["a[href$='/auth/magic/login.php']", 'css_element']);
    }

    /**
     * Opens the magic login link opened last once more.
     *
     * @When /^I open the last magic login link again$/
     */
    public function i_open_the_last_magic_login_link_again(): void {
        if (self::$lastlink === null) {
            throw new coding_exception('No magic login link has been opened in this scenario.');
        }
        $this->execute('behat_general::i_visit', [self::$lastlink]);
    }

    /**
     * Makes the current magic login link of a user expire.
     *
     * @Given /^the magic login link of "(?P<username_string>(?:[^"]|\\")*)" has expired$/
     * @param string $username
     */
    public function the_magic_login_link_of_has_expired(string $username): void {
        global $DB;

        $userid = $DB->get_field('user', 'id', ['username' => $username], MUST_EXIST);
        $instance = $DB->get_record('auth_magic_loginlinks', ['userid' => $userid], '*', MUST_EXIST);
        $past = time() - MINSECS;
        $DB->set_field('auth_magic_loginlinks', 'loginexpiry', $past, ['id' => $instance->id]);
        $DB->set_field('user_private_key', 'validuntil', $past, ['value' => $instance->loginuserkey]);
    }

    /**
     * Forgets the magic login link opened last at the start of each scenario.
     *
     * @BeforeScenario
     */
    public function forget_last_link(): void {
        self::$lastlink = null;
    }
}
