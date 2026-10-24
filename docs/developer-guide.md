---
exclude: true
---

# Magic authentication — Developer guide

How the plugin is built, for people changing it or building on it.

## Data

| Where | What |
|---|---|
| `auth_magic_loginlinks` | One row per user (unique `userid`): the current login key (`loginuserkey`, indexed), its expiry and the link URL at the time it was created. `parent`, `magicauth`, `parentrole`, `manualexpiry` and the invitation columns are used by Magic Authentication Pro |
| `user_private_key`, script `auth/magic` | The keys themselves, created with `create_user_key()` and checked with `validate_user_key()` |
| User preference `auth_magic_lastlinkemail` | Time of the last email anybody could trigger, for the request limit |

Links are always built from the key (`auth_magic_link_url()`), never taken from the stored URL.

## Requesting a link

`amd/src/authmagic.js` runs on the login page (`auth_plugin_magic::loginpage_hook()`). It finds the
identity provider button by its address and, when the *Username* field isn't empty, posts
`magiclogin=1`, `uservalue` and the login form's `logintoken` to `login.php`. Otherwise the click
opens `login.php` without a key, which renders `templates/request_link.mustache` with the same
fields.

`login.php` then:

1. checks the login token with `\core\session\manager::validate_login_token()`;
2. finds the account with `auth_magic_get_email_user()` (case-insensitive email, local, not deleted,
   exactly one match; usernames if `loginoption` is on);
3. calls `auth_magic_process_link_request()`, which skips inactive accounts, applies
   `auth_magic_may_send_link_email()` and sends either the link (`auth_magic_sent_loginlink_touser()`)
   or the notice for other login methods (`auth_magic_requiredmail_magic_authentication()`);
4. redirects to the login page with the same message for every account, unless `protectusernames`
   is off.

## Logging in with a link

`login.php?key=…` opened with GET changes nothing. It checks the key read-only and shows a confirmation,
"Log in to … as …?" (or the account switch question if another user is logged in), whose button posts
the key with the sesskey. Mail scanners and link previews therefore neither use links up nor log
anybody in, and a link from somebody else can't log a visitor in unnoticed (login CSRF).

The confirmed POST then:

1. `auth_plugin_magic::check_userkey_type()` sends a new link for an expired key (limited like
   requests) and returns to the login page;
2. `validate_user_key()`; invalid keys return to the login page with a message;
3. `core_user::require_active_user()` and `auth_plugin_magic::can_login_with_link()`; links the policy
   no longer allows are deleted;
4. if another user is logged in, `require_logout()` and a redirect to the link, which asks again,
   because the logout ends the session;
5. single-use keys are deleted (`delete_magic_instance()`), then `complete_user_login()` and a redirect
   to `core_login_get_return_url()`.

## Keeping links in line with the policy

- `\auth_magic\event\observer`: links for new magic accounts, links removed when an account moves to
  a login method that may not use them, and when it is deleted.
- `auth_magic_authmethod_updated()`: settings callback that removes the links of other login methods
  when *Supported authentication method* goes back to *Magic only*.

## Emails

`auth_magic_messagetouser()` sends with `email_to_user()` from the no-reply user. The message strings
contain HTML; the plain text part is `html_to_text()` of it.

## Shared with Magic Authentication Pro

Pro is a separate plugin with the same component. It builds on the functions above and keeps their
signatures: `auth_magic_sent_loginlink_touser()`, `auth_magic_get_email_user()`,
`auth_magic_messagetouser()`, `auth_magic_get_user_login_link()`,
`auth_plugin_magic::create_magic_instance()`, `update_new_loginkey()` and `check_userkey_type()`.
The 2026102400 upgrade step deletes invitation keys, which free never sends; Pro must not run it.

## Tests

| Kind | Where |
|---|---|
| PHPUnit | `tests/lib_test.php`, `tests/auth_magic_test.php`, `tests/privacy/provider_test.php` |
| Behat | `tests/behat/request_link.feature`, `tests/behat/magic_login.feature`; steps in `tests/behat/behat_auth_magic.php` read the links from the database, as Behat sends no emails |

## Releasing

`.github/workflows/moodle-release.yml` publishes a version to the Moodle Marketplace and creates the
GitHub Release with the ZIP:

1. Merge `dev` into `main` and wait for *Moodle plugin CI* on `main`.
2. Push a tag `v<release>` or `v<release>-r<n>` on `main`, for example `v1.7-r1` for
   `$plugin->release = '1.7'`.

The workflow checks that the tag is on `main`, matches `$plugin->release` and has a section
`## <release> (<version>)` in `CHANGES.md`, whose text becomes the release notes, and waits for CI on
the tagged commit. *Actions ▸ Release to Moodle Marketplace ▸ Run workflow* re-runs a tag or does a dry
run. It needs the repository secret `MOODLE_MARKETPLACE_TOKEN`, an API token of a maintainer of the
plugin from https://marketplace.moodle.com/account/security.
