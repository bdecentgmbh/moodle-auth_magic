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
 * Behat Magic authendication steps definitions.
 *
 * @package    auth_magic
 * @category   test
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Magic authendication steps definitions.
 *
 * @package    auth_magic
 * @category   test
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_auth_magic extends behat_base {

    /**
     * Navigate course enroll page.
     *
     * @Given /^I navigate course enroll page "(?<page>[^"]*)"$/
     * @param string $course
     */
    public function i_navigate_course_enroll_page($course) {
        global $CFG;

        if ($CFG->backup_version > 2022041800) {
            // Moodle - 4.0.
            $this->execute('behat_navigation::i_am_on_page_instance', array($course, "Enrolled users"));
        } else {
            $this->execute('behat_navigation::i_navigate_to_in_current_page_administration', array("Users > Enrolled users"));
        }
    }
}
