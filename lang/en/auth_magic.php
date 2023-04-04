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
$string['pluginname'] = 'Magic authentication';
$string['configtitle'] = "Magic authentication";
$string['defaultenrolmentduration'] = "Default enrolment duration";
$string['defaultenrolmentrole'] = "Default enrolment role";
$string['loginexpiry'] = "Magic login link expiry";
$string['strsupportauth'] = "Supported authentication method";
$string['magiconly'] = "Magic only";
$string['anymethod'] = "Any method";
$string['strowneraccountrole'] = "Owner account role";
$string['strkeyaccount'] = "Key account";
$string['getmagiclinkviagmail'] = "Get a magic link via email";
$string['courseenrolment'] = "Course enrolment";
$string['enrolmentduration'] = "Enrolment duration";
$string['invitationexpiry'] = "";
$string['invitationexpiry'] = "Magic invitation link expiry";
$string['hasbeencreated'] = "has been created";
$string['strenrolinto'] = "enrolled into";
$string['magiclink'] = "Magic link";
$string['copyboard'] = "Copy link to cliboard";
$string['copyloginlink'] = "Copy magic login link for the user";
$string['more'] = '{$a} more';
$string['loginsubject'] = '{$a}: Magic authentication via login';
$string['loginlinksubject'] = "Magic authentication login link";

$string['pluginisdisabled'] = 'The magic authentication plugin is disabled.';
$string['emailnotexists'] = "Doesn't exist user email";
$string['loginlinkbtnpostion'] = 'Magic login link button position';
$string['normal'] = "Normal";
$string['belowusername'] = "Below username";
$string['belowpassword'] = "Below password";
$string['quickregistration'] = "Quick registration";
$string['listofmagiclink'] = "Magic user accounts";
$string['strconfirm'] = "Confirmation";
$string['quickregisterfornonauth'] = "Magic link via login for only magic authentication has supported the user. If you change others modifiy the supported authentication method settings.";
$string['copyinvitationlink'] = "Copy magic invitation link for the user";
$string['sendlink'] = "Send the magic link to the user";
$string['sentinvitationlink'] = "Sent the invitation link to the mail";
$string['notsentinvitationlink'] = "Doesn't sent the invitation link to the mail";
$string['userkeyslist'] = "My user accounts";
$string['createuserenrolcourse'] = 'has been created and enrolled into "{$a}"';
$string['existuserenrolcourse'] = 'has been enrolled into "{$a}"';
$string['statuscreateuser'] = 'has been created';
$string['sentlinktouser'] = "If you supplied a correct email address, an email containing a magic login link should have been sent to you.";
$string['sentlinktousername'] = "If you supplied a correct username, an email containing a magic login link should have been sent to your email address.";
$string['preventmagicauthsubject'] = "Magic authentication support information";
$string['invitationexpiryloginlink'] = "The invitation link has expired. If the email address belongs to an account that supports login via link, a link has been sent via email";
$string['loginexpiryloginlink'] = "The magic login link has expired. A new magic login link has been sent to your email address.";
$string['linkexpirytime'] = 'Set a magic login link expiry time';
$string['success'] = 'Changes updated';
$string['error'] = 'Does not updated magic login link expiration time';
$string['loginoption'] = "Login option";
$string['loginoptiondesc'] = "Enable this setting to log in using the username provided in the login form.";

$string['loginlinkmessage'] = 'Hi {$a->fullname},

to access your account on \'{$a->sitename}\', please use the following magic link:

<a href=\'{$a->link}\'> {$a->link} </a> <br>

If you need help, please contact the site administrator,
{$a->admin}';



$string['invitationmessage'] = 'Hi {$a->fullname},

A new account has been requested at \'{$a->sitename}\'
using your email address.

To login your new account, please go to this web address login directly instead username and password :

<a href=\'{$a->link}\'> {$a->link} </a> <br>

If you need help, please contact the site administrator,
{$a->admin}';

$string['expiredloginlinkmsg'] = 'Hi {$a->fullname},

you have tried to access \'{$a->sitename}\' with an expired magic login link.

A new magic link was automatically created for you:

<a href=\'{$a->link}\'> {$a->link} </a> <br>

If you need help, please contact the site administrator,
{$a->admin}';

$string['preventmagicauthmessage'] = 'Hi {$a->fullname},

A new account has been requested at \'{$a->sitename}\'
using your email address. <br>

<strong> Note: </strong> Magic link via login for only magic authentication has supported the user, so you can\'t use login via link but you have must use your password instead.

<br>

{$a->forgothtml} <br>

If you need help, please contact the site administrator,
{$a->admin}';

$string['doesnotaccesskey'] = "Doesn't have access the key in your authentication method";
$string['manualinfo'] = "Manual enrolments are not available in this course.";
$string['passinfo'] = "- or type in your password -";
$string['invailduser'] = "Invaild user";
$string['magicloginlink'] = '{$a}: Magic login link';
$string['and'] = "And";
$string['instructionsforlinktype'] = "Please provide a magic link type, the types are (invitation or login)";
$string['userhavenotlinks'] = 'User have not any {$a} link';

$string['privacy:metadata:auth_magic_loginlinks'] = 'Magic links for the user.';
$string['privacy:metadata:auth_magic:userid'] = 'The ID of the user with this login links';
$string['privacy:metadata:auth_magic:parent'] = 'The value of the userid to assign parent of the user.';
$string['privacy:metadata:auth_magic:magicauth'] = 'The value of whether parent assigns or not.';
$string['privacy:metadata:auth_magic:parentrole'] = 'The instance of the parent role id.';
$string['privacy:metadata:auth_magic:loginuserkey'] = 'The value of the user login key';
$string['privacy:metadata:auth_magic:invitationuserkey'] = 'The value of the user invitation key';
$string['privacy:metadata:auth_magic:magiclogin'] = 'The value of the user magic login link';
$string['privacy:metadata:auth_magic:magicinvitation'] = 'The value of the user magic invitation link';
$string['privacy:metadata:auth_magic:loginexpiry'] = 'The date that the login key is valid until';
$string['privacy:metadata:auth_magic:invitationexpiry'] = 'The date that the invitation key is valid until';
$string['privacy:metadata:auth_magic:manualexpiry'] = "The date that set the expiry to the user login key is valid until";
$string['privacy:metadata:auth_magic:timecreated'] = 'The date and time that the login link was created.';
$string['privacy:metadata:auth_magic:timemodified'] = 'The date and time that the login link was modified.';
$string['privacy:metadata:auth_magic'] = 'Magic authentication';
$string['messageprovider:auth_magic'] = 'Magic authentication login links';
$string['firstname'] = "First name";
$string['lastname'] = "Last name";
