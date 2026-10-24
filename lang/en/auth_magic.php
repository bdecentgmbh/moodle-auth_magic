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
$string['authmethod_desc'] = 'Magic only: only accounts with the login method Magic authentication can use magic links. Accounts with other login methods that ask for one are told to log in as usual. Any method: accounts of all enabled login methods (for example Manual accounts or OAuth 2) can use magic links too, including administrators, who can then log in with access to their mailbox alone. Web service accounts and the guest account never can. Going back to Magic only deletes the links of other login methods.';
$string['backtologin'] = 'Back to the login page';
$string['confirmlogin'] = 'Ask before logging in';
$string['confirmlogin_desc'] = 'Whether opening a magic link first asks "Do you want to log in to … as …?" and logs in only when the user clicks Log in. Always: mail security scanners and link previews (for example Outlook Safe Links) then neither use up links nor log in, and nobody can be logged into somebody else\'s account with a link they were sent. Only for single-use links: links that work until they expire log in as soon as they are opened, which suits permanent personal links such as bookmarks or QR codes. If such a link arrives by email, a mail scanner that opens it logs in as the user, and Moodle may send the user a "New sign in" notification. Never: links also log in as soon as they are opened, and mail scanners can use up single-use links before the user clicks them. A user who is logged in as somebody else is always asked before switching accounts.';
$string['confirmloginalways'] = 'Always';
$string['confirmloginnever'] = 'Never';
$string['confirmloginonce'] = 'Only for single-use links';
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
$string['loginconfirm'] = 'Do you want to log in to {$a->sitename} as {$a->target}?';
$string['loginexpiry'] = "Magic login link expiry";
$string['loginexpiry_desc'] = 'How long a magic link works after it was sent. Somebody who opens an expired link gets a new one by email. 0 means that links never expire: together with "Until it expires" below, a link then works like a permanent password that sits in an email inbox.';
$string['loginexpiryloginlinkwithupdate'] = "The magic login link has expired. A new magic login link has been sent to your email address.";
$string['loginkeytype'] = "Configure login key link";
$string['loginkeytype_desc'] = 'Only once: a link logs in once, and users ask for a new link to log in again. Until it expires: a link logs in as often as needed until it expires. Single-use links are safer, as a forwarded or leaked link stops working after its first use.';
$string['loginlinkmessage'] = 'Hi {$a->fullname},

to access your account on \'{$a->sitename}\', please use the following magic link:

<a href=\'{$a->link}\'> {$a->link} </a> <br>

This link will expire on: <b> {$a->expiry} </b> <br>

If you need help, please contact the site administrator,
{$a->admin}';
$string['loginoption'] = "Allow Username to get magic link";
$string['loginoptiondesc'] = 'Users can type their username instead of their email address to ask for a magic link. The link always goes to the email address of the account.';
$string['loginsubject'] = '{$a}: Magic authentication via login';
$string['magiclinkprovider'] = 'Magic link via email';
$string['magiconly'] = "Magic only";
$string['nomagiclinkforgotten'] = 'If you have forgotten your password, you can reset it: {$a} <br>';
$string['nomagiclinkmessage'] = 'Hi {$a->fullname},

somebody, probably you, asked for a magic link to log in to \'{$a->sitename}\' with your account.

Your account can\'t log in with a magic link. Please log in the way you usually do.{$a->forgotten}

If you need help, please contact the site administrator,
{$a->admin}';
$string['nomagiclinksubject'] = '{$a}: Logging in to your account';
$string['pluginisdisabled'] = 'The magic authentication plugin is disabled.';
$string['pluginname'] = 'Magic authentication';
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
$string['supportpassword_desc'] = 'Magic accounts can also have a password. They then log in with their password as well as with magic links, change it under Preferences and reset it with Forgotten password, and admins can set it under Edit profile. When this is off, magic accounts have no password and password logins fail. Passwords set while this was on are kept, and work again when it is turned back on.';
$string['switchaccountconfirm'] = 'You are logged in as {$a->current}. Do you want to log out and log in as {$a->target}?';
