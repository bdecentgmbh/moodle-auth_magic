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
 * Strings for component 'auth_magic', language 'en'.
 *
 * @package   auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['anymethod'] = "Any method";
$string['backtologin'] = 'Back to the login page';
$string['currentlylinkexpiry'] = "As the login link has expired, a new link will be sent to your email address when you click on the expired login link.";
$string['expiredloginlinkmsg'] = 'Hi {$a->fullname},

you have tried to access \'{$a->sitename}\' with an expired magic login link.

A new magic link was automatically created for you:

<a href=\'{$a->link}\'> {$a->link} </a> <br>

This link will expire on: <b> {$a->expiry} </b> <br>

If you need help, please contact the site administrator,
{$a->admin}';
$string['getmagiclinkviagmail'] = "Get a magic link via email";
$string['invalidlink'] = 'This magic link is no longer valid. It may have been used already or replaced by a newer link. Please ask for a new link.';
$string['keyusemultiple'] = "Until it expires";
$string['keyuseonce'] = "Only once";
$string['linknotallowed'] = 'Your account can no longer log in with a magic link. Please log in with your password or the login method of your account.';
$string['loginexpiry'] = "Magic login link expiry";
$string['loginexpiryloginlinkwithupdate'] = "The magic login link has expired. A new magic login link has been sent to your email address.";
$string['loginkeytype'] = "Configure login key link";
$string['loginkeytype_desc'] = "This setting allows administrators to configure the behavior of login keys. The login keys can be used only once or until the expiry time.";
$string['loginlinkmessage'] = 'Hi {$a->fullname},

to access your account on \'{$a->sitename}\', please use the following magic link:

<a href=\'{$a->link}\'> {$a->link} </a> <br>

This link will expire on: <b> {$a->expiry} </b> <br>

If you need help, please contact the site administrator,
{$a->admin}';
$string['loginoption'] = "Allow Username to get magic link";
$string['loginoptiondesc'] = "Enable this setting to login using the username provided in the login form.";
$string['loginsubject'] = '{$a}: Magic authentication via login';
$string['magiclinkprovider'] = 'Magic link via email';
$string['magiconly'] = "Magic only";
$string['pluginisdisabled'] = 'The magic authentication plugin is disabled.';
$string['pluginname'] = 'Magic authentication';


$string['preventmagicauthmessage'] = 'Hi {$a->fullname},

A new account has been requested at \'{$a->sitename}\' using your email address. <br>

<strong> Note: </strong> Authenticating using a Magic link is not supported for your account, please use your password instead.

<br>

{$a->forgothtml} <br>

If you need help, please contact the site administrator,
{$a->admin}';
$string['privacy:metadata:auth_magic:loginexpiry'] = 'The date that the login key is valid until';
$string['privacy:metadata:auth_magic:loginuserkey'] = 'The value of the user login key';
$string['privacy:metadata:auth_magic:magiclogin'] = 'The magic login link of the user';
$string['privacy:metadata:auth_magic:timecreated'] = 'The date and time that the login link was created.';
$string['privacy:metadata:auth_magic:timemodified'] = 'The date and time that the login link was modified.';
$string['privacy:metadata:auth_magic:userid'] = 'The ID of the user with this login links';
$string['privacy:metadata:auth_magic_loginlinks'] = 'Magic links for the user.';
$string['privacy:metadata:core_userkey'] = 'The key of the magic link, which logs the user in.';
$string['privacy:metadata:preference:lastlinkemail'] = 'The time the user was last sent a magic link email. It limits how often anybody can make the site send one.';
$string['requestinterval'] = 'Minimum time between magic link emails';
$string['requestinterval_desc'] = 'Anybody can ask for a magic link for any email address. To keep this from flooding inboxes, an account gets at most one magic link email within this time, including the notice for accounts that use another login method. Requests in between get the usual answer but no email. 0 turns the limit off.';
$string['requestlinkheading'] = 'Log in with a magic link';
$string['requestlinkintro'] = 'Enter the email address of your account. You will get an email with a link that logs you in.';
$string['requestlinkintrousername'] = 'Enter the email address or the username of your account. You will get an email with a link that logs you in.';
$string['sentlinktouser'] = "If you supplied a correct email address, an email containing a magic login link should have been sent to you.";
$string['sentlinktousername'] = "If you supplied a correct username, an email containing a magic login link should have been sent to your email address.";
$string['strsupportauth'] = "Supported authentication method";
$string['strsupportpassword'] = "Supports password";


































$string['switchaccount'] = 'Switch account';
$string['switchaccountconfirm'] = 'You are logged in as {$a->current}. Do you want to log out and log in as {$a->target}?';
