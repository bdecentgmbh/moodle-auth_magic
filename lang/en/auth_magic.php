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
$string['sentregisterlinktouser'] = "If you supplied a correct email address, an email containing a registration link should have been sent to you.";
$string['sentlinktousername'] = "If you supplied a correct username, an email containing a magic login link should have been sent to your email address.";
$string['preventmagicauthsubject'] = "Magic authentication support information";
$string['invitationexpiryloginlink'] = "The invitation link has expired. If the email address belongs to an account that supports login via link, a link has been sent via email";
$string['loginexpiryloginlink'] = "The magic login link has expired. A new magic login link has been sent to your email address.";
$string['registrationexpirylink'] = "The registration link has expired. A new registration link has been sent to your email address.";
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


$string['registrationmessage'] = 'Hi {$a->emailplaceholder},

Thank you for your interest in joining {$a->sitename}! To create your account, please use the following registration link:

<a href={$a->link}> {$a->link} </a> <br>

If you have any questions or need assistance, please don\'t hesitate to contact the site administrator, {$a->admin}.

We look forward to having you as a member of our community!

Best regards,
The {$a->sitename} Team';

$string['registrationsubject'] = "Magic authentication Registration link";



$string['invitationmessage'] = 'Hi {$a->fullname},

A new account has been requested at \'{$a->sitename}\'
using your email address.

To login your new account, please go to this web address login directly instead username and password :

<a href=\'{$a->link}\'> {$a->link} </a> <br>

If you need help, please contact the site administrator,
{$a->admin}';

$string['expiredregistrationmessage'] = 'Hi {$a->fullname},

you have tried to access \'{$a->sitename}\' with an expired registration link.

A new magic link was automatically created for you:

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
// Campaign settings.
$string['generalsettings'] = 'General settings';
$string['strcampaignownerrole'] = 'Campaign owner role';
$string['strcampaignownerrole_desc'] = 'Add a campaign owner role for magic campaigns';
$string['managecampaign'] = 'Manage campaign';
$string['createcampaign'] = 'Create campaign';
$string['campaigns:generalsection'] = 'Genaral settings';
$string['campaigns:title'] = 'Title';
$string['campaigns:description'] = 'Description';
$string['campaigns:comments'] = 'Comments';
$string['campaigns:availabilitysection'] = 'Availability';
$string['campaigns:capacity'] = 'Capacity';
$string['campaigns:status'] = 'Status';
$string['campaigns:visibility'] = 'Visibility';
$string['campaigns:start_from'] = 'Available from';
$string['campaigns:end_from'] = 'Available closes';
$string['campaigns:password'] = 'Password';
$string['campaigns:appearancesection'] = 'Appearance';
$string['campaigns:logo'] = 'Logo';
$string['campaigns:headerimg'] = 'Header image';
$string['campaigns:backgroundimg'] = 'Background image';
$string['campaigns:transform'] = 'Transparent form';
$string['campaigns:ownerprofile'] = 'Display campaign owner\'s profile picture';
$string['campaigns:formposition'] = 'Form Position';
$string['campaigns:center'] = 'Center';
$string['campaigns:leftoverlay'] = 'Left Overlay';
$string['campaigns:rightoverlay'] = 'Right Overlay';
$string['campaigns:leftfull'] = 'Left Full';
$string['campaigns:rightfull'] = 'Right Full';
$string['campaigns:assignmentssection'] = 'Assignments';
$string['campaigns:cohorts'] = 'Cohort membership';
$string['campaigns:globalrole'] = 'Global role';
$string['campaigns:owneraccount'] = 'Campaign owner account';
$string['campaigns:privacypolicysection'] = 'Privacy policy';
$string['campaigns:privacypolicy'] = 'Display consent option';
$string['campaigns:consentstatement'] = 'Consent statement';
$string['campaigns:welcomemessagesection'] = 'Welcome message';
$string['campaigns:welcomemessage'] = 'Send welcome message to new accounts';
$string['campaigns:welcomemessagecontent'] = 'Message content';
$string['campaigns:welcomemessageowner'] = 'Also send to campaign owner';
$string['campaigns:followupmessagesection'] = 'Follow up Message';
$string['campaigns:followupmessage'] = 'Send follow up message to new accounts';
$string['campaigns:followupmessagecontent'] = 'Message content';
$string['campaigns:messagedelay'] = 'Delay';
$string['campaigns:followupmessageowner'] = 'Also send to campaign owner';
$string['campaigns:formfieldsection'] = 'Form fields';
$string['campaigns:standard_firstname'] = 'First name';
$string['campaigns:standard_lastname'] = 'Last name';
$string['campaigns:standard_username'] = 'Username';
$string['campaigns:standard_email'] = 'e-Mail';
$string['campaigns:standard_country'] = 'Country';
$string['campaigns:standard_lang'] = 'Language';
$string['campaigns:standard_city'] = 'City';
$string['campaigns:standard_idnumber'] = 'ID Number';
$string['campaigns:standard_alternatename'] = 'Alternatename';
$string['campaigns:standard_department'] = 'Department';
$string['campaigns:standard_institution'] = 'Institution';
$string['campaigns:standard_address'] = 'Address';
$string['campaigns:pointoffirstcontact'] = 'Point of first contact';
$string['campaigns:typeofaccount'] = 'Type of account';
$string['campaigns:required'] = 'Required';
$string['campaigns:optional'] = 'Optional';
$string['campaigns:hiddentype1'] = 'Hidden (use provided text)';
$string['campaigns:hiddentype2'] = 'Hidden (use default)';
$string['campaigns:hiddentype3'] = 'Hidden (use other field\'s value)';
$string['campaigns:hidden'] = 'Hidden';
$string['disabled'] = 'Disabled';
$string['campaigns:title'] = 'Title';
$string['campaigns:campaignowner'] = 'Campaign Owner';
$string['campaigns:availability'] = 'Availability';
$string['campaigns:capacity'] = 'Capacity';
$string['campaigns:link'] = 'Copy link';
$string['campaigns:preview'] = 'Preview';
$string['campaigns:available'] = 'Available';
$string['campaigns:archived'] = 'Archived';
$string['campaigns:unlimited'] = 'Unlimited';
$string['campaigns:updatesuccess'] = 'Campaign updated successfully';
$string['campaigns:insertsuccess'] = 'Campaign created successfully';
$string['campaigns:recordmissing'] = 'Campaign record missing';
$string['campaigns:campaigndeleted'] = 'Campaign deleted successfully';
$string['campaigns:deleteconfirmcampaign'] = 'Are you sure you want to delete this campaign from the manage campaigns?';
$string['campaign:notavailable'] = 'This campaign is not avilable to signup';
$string['campaign:capacity_info'] = '<ul><li>No of users registered using this campaign: {$a->used}</li><li>No of users still able to signup using this campaign: {$a->available} </li></ul>';
$string['campaign:requirepasswordmessage'] = 'To signup using this campaign you need to know this campaign password';
$string['campaignpassword'] = 'Campaign password';
$string['campaign:emptypassword'] = 'Password verification failed';
$string['campaign:verifiedsuccess'] = 'Password verified successfully';
$string['campaign:hidden'] = 'Hidden';
$string['campaign:available'] = 'Available';
$string['campaign:archived'] = 'Archived';
$string['campaign:notaccess'] = 'You do not have permission to view this page for the specified user.';
$string['lang'] = "Language";
$string['none'] = 'None';
$string['verify'] = 'Verify';
$string['unlimited'] = 'unlimited';
$string['confirmpassword'] = 'Confirm password';
$string['welcomemessagesubject'] = 'Signup completed ';
$string['followupmessagesubject'] = 'This is follow up message';
$string['sendmessage'] = 'Send follow up message';
$string['campaignlink'] = 'Campaign link';
$string['signupsuccess'] = "User signup successfully.";
$string['strsupportpassword'] = "Supports password";
$string['strsignup'] = 'Sign up';
$string['auth_emailnoemail'] = 'Tried to send you an email but failed!';
$string['strprofilefield:'] = 'Profile Field: {$a}';
$string['strcampaigns'] = "Campaigns";
$string['strviewcampaign'] = "View Campaign";
$string['strmagicsignup'] = "Magic Sign Up";
$string['strautocreateusers'] = "Auto create users";
$string['strautocreateusers_desc'] = "If enabled, visitors will be able to register using a magic registration link to the site, if the email matches the list of allowed email domains and/or does not match the list of denied email domains. The account will be created when the user clicks on the registration link in their inbox. Before they can access the site, they need to fill in mandatory profile fields like their name and potentially other fields that are required on this site, as well as consent to any existing privacy policy.";
$string['campaigns:title_help'] = 'Visible to the user.';
$string['campaigns:description_help'] = 'Visible to the user.';
$string['campaigns:comments_help'] = 'Only visible to campaign owners and campaign managers.';
$string['campaigns:capacity_help'] = 'How many users can use the campaign to sign up. Leave empty or set to 0 for unlimited.';
$string['campaigns:status_help'] = 'Wether this campaign is available or archived. Archived campaigns cannot be used to sign up, even if there is still capacity. They are no longer visible to the campaign owner.';
$string['campaigns:visibility_help'] = 'Wether this campaign is visible for guests.';
$string['campaigns:start_from_help'] = 'When this campaign opens.';
$string['campaigns:end_from_help'] = 'When this campaign closes.';
$string['campaigns:password_help'] = 'Secure your campaign using a password.

This can either be entered by the user, or submitted as

a parameter (use the link icon to copy the link with a token).';
$string['campaigns:logo_help'] = "Will be displayed centered at the top of the page.

It does not replace the site's logo (i.e. it is below the navbar)

If you want the logo to align differently, add it to the description.";
$string['campaigns:headerimg_help'] = 'Will be displayed below the navbar, full width.

Height depends on the image you upload.';
$string['campaigns:backgroundimg_help'] = 'Will be displayed below the header image if set,

otherweise below the navbar.

Width and height will be full.';
$string['campaigns:transform_help'] = 'Wether to show a border/box shadow/background color for the form.';
$string['campaigns:ownerprofile_help'] = 'Displays the campaign owner\'s profile picture above the campaign form.';
$string['campaigns:formposition_help'] = 'Define where the form shall be placed in the sign up page.';

$string['campaigns:cohorts_help'] = 'Automatically add users to the following cohorts.';
$string['campaigns:globalrole_help'] = 'Automatically assign the following role to a user.';
$string['campaigns:campaignowner_help'] = 'Automatically assign the following user to the following account.';
$string['campaigns:privacypolicy_help'] = 'IF enable users to give their consent to the privacy policies by showing a consent statement on the campaign page.';
$string['campaigns:consentstatement_help'] = 'Will be displayed if "Display consent option" is enabled

next to a checkbox, which, when ticked by the user will

automatically set the consent to the privacy policies.';
$string['campaigns:welcomemessage_help'] = 'When enabled, sent to new users.';
$string['campaigns:welcomemessagecontent_help'] = 'You can use placeholders.

Password placeholder will only work if the global setting

"Support passwords" in auth_magic is set.';
$string['campaigns:welcomemessageowner_help'] = 'Will CC the campaign owner (if set).';
$string['campaigns:followupmessage_help'] = 'When enabled, sent to follow-up message for new users.';
$string['campaigns:messagedelay_help'] = 'Send after the X days.';
$string['campaigns:followupmessagecontent_help'] = 'You can use placeholders.

Password placeholder will only work if the global setting

"Support passwords" in auth_magic is set.';
$string['campaigns:followupmessageowner_help'] = 'Will CC the campaign owner (if set).';
$string['privilegedrole'] = 'Privileged role';
$string['teacherrole'] = 'Teacher';
$string['noneditteacherrole'] = 'Non-editing teacher';
$string['managerrole'] = 'Manager';
$string['strsignsite'] = 'Sign in to {$a}';
$string['loginfooter'] = "Login footer links";
$string['loginfooter_desc'] = 'Enter the content/links on the "Login footer" block. A URL is separated by pipe characters followed by the link text.';
$string['loginfooterdefault'] = 'Imprint |#
Terms & Conditions |#
Login instructions |#
Admin login|#';
$string['strsignin'] = "Sign In";
$string['strstandardprofilefield_help'] = "Standard profile field";
$string['strstandardprofilefield'] = "Standard profile field";
$string['strcustomprofilefield'] = "Custom profile field";
$string['strcustomprofilefield_help'] = "Custom profile field";
$string['strenteryouremail'] = "enter your e-Mail address";
