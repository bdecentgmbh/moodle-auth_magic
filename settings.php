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

    // Support password.
    $name = "auth_magic/supportpassword";
    $title = get_string("strsupportpassword", "auth_magic");
    $setting = new admin_setting_configcheckbox($name, $title, "", 0);
    $settings->add($setting);

    // Magic login link expiry.
    $name = "auth_magic/loginexpiry";
    $title = get_string("loginexpiry", "auth_magic");
    $desc = "";
    $setting = new admin_setting_configduration($name, $title, $desc, 10 * MINSECS);
    $settings->add($setting);

    // Magic invitation link expiry.
    $name = "auth_magic/invitationexpiry";
    $title = get_string("invitationexpiry", "auth_magic");
    $desc = "";
    $setting = new admin_setting_configduration($name, $title, $desc, 1 * HOURSECS);
    $settings->add($setting);


    // Magic invitation link expiry.
    $name = "auth_magic/loginkeytype";
    $title = get_string("loginkeytype", "auth_magic");
    $desc = get_string("loginkeytype_desc", "auth_magic");
    $options = [
        'once' => get_string('keyuseonce', 'auth_magic'),
        'more' => get_string('keyusemultiple', 'auth_magic')
    ];
    $setting = new admin_setting_configselect($name, $title, $desc, 'once', $options);
    $settings->add($setting);

    // Allow user to use username to login option.
    $name = "auth_magic/loginoption";
    $title = get_string("loginoption", "auth_magic");
    $desc = get_string("loginoptiondesc", "auth_magic");
    $setting = new admin_setting_configcheckbox($name, $title, $desc, 0);
    $settings->add($setting);

    // Supported authentication method.
    $options = [
        0 => get_string('magiconly', 'auth_magic'),
        1 => get_string('anymethod', 'auth_magic'),
    ];
    $name = "auth_magic/authmethod";
    $title = get_string("strsupportauth", "auth_magic");
    $desc = "";
    $setting = new admin_setting_configselect($name, $title, $desc, 0, $options);
    $settings->add($setting);
}
