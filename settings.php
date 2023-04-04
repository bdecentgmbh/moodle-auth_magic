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
 * Admin settings and defaults
 *
 * @package auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot."/auth/magic/lib.php");

if ($ADMIN->fulltree) {

     // Magic login link expiry.
     $name = "auth_magic/invitationexpiry";
     $title = get_string("invitationexpiry", "auth_magic");
     $desc = "";
     $setting = new admin_setting_configduration($name, $title, $desc, 1 * WEEKSECS);
     $settings->add($setting);

    // Magic login link expiry.
    $name = "auth_magic/loginexpiry";
    $title = get_string("loginexpiry", "auth_magic");
    $desc = "";
    $setting = new admin_setting_configduration($name, $title, $desc, 4 * HOURSECS);
    $settings->add($setting);

    // Pro plugin feature settings.
    if (auth_magic_has_pro()
        && file_exists($CFG->dirroot."/local/magic/setting.php")) {
        require_once($CFG->dirroot."/local/magic/setting.php");
    }
}
