# Changes

## 1.7 (2026102400)

- Moodle 4.5 to 5.3; Moodle 4.5 is now the minimum.
- Security: link requests give the same answer for every account, so they no longer tell whether an
  email address or username is registered (unless the site turns off *Protect usernames*).
- Security: link requests need the login token of the login form, and an account gets at most one
  email per *Minimum time between magic link emails* (new setting, default 2 minutes).
- Security: opening a magic link only asks "Do you want to log in to … as …?" and the user confirms
  with a click. So mail scanners and link previews no longer use up links or log in, and a link from
  somebody else can't log a visitor in unnoticed. The new setting *Ask before logging in* (default
  *Always*) can limit this to single-use links or turn it off.
- Security: a magic link of another account asks before it switches the logged-in user.
- Security: links are checked against *Supported authentication method* when they are used, and
  deleted when an account or the setting no longer allows them. The guest account and web service
  accounts never get or use links.
- Security: the privacy export no longer contains working login keys, and privacy deletions and
  uninstalling remove the keys.
- Fixed: a login link expiry of 0 (never expires) made link requests loop forever.
- Fixed: single-use links failed when another user was logged in.
- Fixed: "A required parameter (key) was missing" after password logins and in themes where the login
  page script didn't find the button (#6). The button now asks for the email address in that case.
- Fixed: the login page button didn't work on Moodle 5.3 and in some themes. It is now called "Magic
  link via email" (on Moodle 5.3: "Log in with Magic link via email").
- Fixed: emails are matched without regard to case, and an email shared by several accounts matches
  none of them.
- Links are sent by email only, from the no-reply address (#8).
- The plugin no longer turns on *Allow log in via email* for password logins.
- Answers appear in the login form, and a used or replaced link says so on the login page.
- Accounts that can't use links are told to log in as usual, with a password reset link only if their
  login method can reset passwords.
- Every setting is explained on the settings page, and the documentation has administrator, student
  and developer guides.
- Links follow changes of the site URL, unused invitation keys are no longer created, and the links
  table is indexed.

## 1.0.1 (2023112400)

- Moodle 3.9 to 4.3.
- Login keys can be used either once or until they expire.
- The expiration time is shown in the notification.
- Any authentication method can be supported.
- Magic accounts can also use passwords.
- Usernames can be used to get a magic link.
- Shorter default expiry times: 10 minutes (login) and 1 hour (invitation).

## 1.0.0 (2023033000)

- Moodle 3.9 to 4.1.
- Log in using magic links.
- Privacy provider.
- Settings: magic login link expiry and magic invitation link expiry.
