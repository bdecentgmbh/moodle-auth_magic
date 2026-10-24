Magic authentication
=================
bdecentgmbh / moodle-auth_magic

Changes
-------

### v1.7

* 2026-10-24 - Security: Link requests no longer tell whether an email address or username has an account (unless the site turns off "Protect usernames")
* 2026-10-24 - Security: Link requests need the login token of the login form and send an account at most one email per "Minimum time between magic link emails" (new setting, default 2 minutes)
* 2026-10-24 - Security: A magic link of another account asks before it switches the logged-in user
* 2026-10-24 - Security: Links are checked against the supported authentication method when they are used, and removed when an account or the setting no longer allows them
* 2026-10-24 - Security: The guest account and web service accounts never get or use magic links, also with "Any method"
* 2026-10-24 - Security: The privacy export no longer contains working login keys, and privacy deletions and uninstalling remove the keys
* 2026-10-24 - Bugfix: A login link expiry of 0 (never expires) no longer makes link requests loop forever
* 2026-10-24 - Bugfix: Single-use links work when another user is logged in
* 2026-10-24 - Bugfix: No "A required parameter (key) was missing" error after password logins or in themes where the login page script doesn't find the button (issue #6); the button asks for the email address instead
* 2026-10-24 - Bugfix: The login page button works in every theme and on Moodle 5.3; it is now called "Magic link via email" (Moodle 5.3: "Log in with Magic link via email")
* 2026-10-24 - Bugfix: Emails are matched without regard to case, and emails shared by several accounts match none
* 2026-10-24 - Improvement: Links are sent by email only, from the no-reply address (issue #8)
* 2026-10-24 - Improvement: The plugin no longer turns on "Allow log in via email" for password logins
* 2026-10-24 - Improvement: Links follow changes of the site URL; free no longer creates unused invitation keys
* 2026-10-24 - Improvement: A used or replaced link explains this on the login page

### v1.0.1 (Release Candidate)
Compatibility: Moodle 3.9 - 4.3

* 2023-11*24 - Improvement: Login keys can be used either once or until expiry
* 2023-11*24 - Improvement: Display expiration time in notification
* 2023-11*24 - Improvement: Support any authentication method
* 2023-11*24 - Improvement: Support for passwords users using magic auth
* 2023-11*24 - Improvement: Support for usernames to get magic link
* 2023-11*24 - Improvement: Reduced default expiry time to 10min (login) and 1hour (invitation)
* 2023-11*24 - Compatibility: Moodle 4.3 support
* 2023-11*24 - Compatibility: Moodle 4.2 support

### v1.0.0 (Release Candidate)
Compatibility: Moodle 3.9 - 4.1

* 2023-04*04 - Feature: Log in using magic links
* 2023-04*04 - Feature: Implemented privacy provider
* 2023-04*04 - Setting: Magic invitation link expiry
* 2023-04*04 - Setting: Magic login link expiry
