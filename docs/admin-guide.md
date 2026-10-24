---
pagetypes:
  - admin-setting-authsettingmagic
  - admin-settings-manageauths
  - auth-magic-*
---

# Magic authentication — Administrator guide

Magic authentication lets users log in with a link they get by email instead of a password. On the
login page, users type their email address and click *Magic link via email* (in Moodle 5.3:
*Log in with Magic link via email*). The link logs them in.

This guide covers setting the plugin up and what it does to keep magic links safe. For logging in with
a link, see the [student guide](student-guide.md).

## Requirements

| Component | Required? | Effect if missing |
|---|---|---|
| Moodle 4.5 to 5.3 | Yes | The plugin will not install on older versions |
| Outgoing email | Yes | Users get no links. Links are sent by email only |

## Installing and enabling

1. Install the plugin like any other plugin, to `auth/magic`, and complete the upgrade under
   *Site administration ▸ Notifications*.
2. Enable it under *Site administration ▸ Plugins ▸ Authentication ▸ Manage authentication* with the
   eye icon. The login page then shows the magic link button.
3. Give accounts the login method *Magic authentication*, or let accounts with other login methods use
   links too (see *Supported authentication method* below).

A magic account gets its login link when it is created. Other accounts get one when they first ask
for a link.

## Settings

*Site administration ▸ Plugins ▸ Authentication ▸ Magic authentication*

| Setting | Default | What it does |
|---|---|---|
| Supports password | Off | Magic accounts can also have a password: they log in with it, change it under *Preferences* and reset it with *Forgotten password*, and admins can set it. Without it, magic accounts have no password and password logins fail. Passwords set while it was on are kept, and work again when it is turned back on |
| Magic login link expiry | 10 minutes | How long a link works. 0 means links never expire |
| Minimum time between magic link emails | 2 minutes | An account gets at most one email within this time, however often anybody asks. 0 turns the limit off |
| Configure login key link | Only once | *Only once*: a link logs in once. *Until it expires*: a link logs in as often as needed until it expires |
| Allow Username to get magic link | Off | Users can type their username instead of their email address |
| Supported authentication method | Magic only | *Magic only*: only magic accounts can use links. *Any method*: accounts with other enabled login methods can use links too, except web service accounts. Going back to *Magic only* deletes their links |

Accounts that may not use links get an email that tells them to log in the way they usually do. It
links to *Forgotten password* only if their login method can reset passwords (for example Manual
accounts, but not OAuth 2).

## Behaviour overview

How the plugin treats an account depends on the account's login method and on two settings:
*Supported authentication method* and *Supports password*.

### Asking for a link

What the account gets by email when somebody asks for a link for it. The visitor always sees the same
answer (see *How links are kept safe*), and an account gets at most one email per *Minimum time
between magic link emails*.

| Account | Magic only | Any method |
|---|---|---|
| Magic authentication | Magic link | Magic link |
| Other enabled login method (Manual accounts, Email-based self-registration, OAuth 2, LDAP, LTI, …) | Notice to log in as usual | Magic link |
| Login method whose plugin is disabled | Notice to log in as usual | Notice to log in as usual |
| No login, Web services, the guest account | Nothing | Nothing |
| Suspended or unconfirmed | Nothing | Nothing |
| No account, a deleted account, an email shared by several accounts | Nothing | Nothing |

### Using a link

Whether a link that is valid (not expired, not used yet if links work only once) logs the account in.

| Account | Magic only | Any method |
|---|---|---|
| Magic authentication | Yes | Yes |
| Other enabled login method | No, the link is refused and deleted | Yes |
| Login method whose plugin is disabled | No, the link is refused and deleted | No, the link is refused and deleted |
| Web services, the guest account | No, the link is refused and deleted | No, the link is refused and deleted |
| No login, suspended, unconfirmed | No, Moodle refuses the account | No, Moodle refuses the account |

In every case:

- **An expired link** sends a new one by email (at most once per *Minimum time between magic link
  emails*) and says so on the login page, if the account may still use links.
- **A used, replaced or deleted link** says on the login page that it is no longer valid.
- **If somebody else is logged in**, they are asked before they are logged out.

### Passwords

*Supports password* affects magic accounts only. Accounts with other login methods keep the password
rules of their own method.

| Magic account | Supports password off | Supports password on |
|---|---|---|
| Log in with a password | No | Yes |
| *Change password* under *Preferences* | Not offered | Offered |
| *Forgotten password* | Moodle emails that passwords can't be reset | Reset link by email |
| Admins set the password under *Edit profile* | Field disabled | Yes |
| New passwords are stored | No | Yes |

Passwords stored while *Supports password* was on are kept when it is turned off, and work again
when it is turned back on. Accounts created while it was off have no password until they reset it.

### Other settings

| Setting | Value | Effect |
|---|---|---|
| Magic login link expiry | A duration | A link works for that long |
| | 0 | Links never expire |
| Configure login key link | Only once | A link logs in once, then it is no longer valid |
| | Until it expires | A link logs in as often as needed until it expires |
| Allow Username to get magic link | Off | Only email addresses. A username gets "Invalid email address" |
| | On | Email addresses and usernames |
| Minimum time between magic link emails | A duration | At most one email per account within that time |
| | 0 | No limit |
| *Protect usernames* (core, *Site security settings*) | On (default) | The same answer for every request |
| | Off | Unknown email addresses and usernames are reported as not found |
| *Allow log in via email* (core, *Manage authentication*) | On or off | Only affects password logins. Link requests always accept email addresses |

## How links are kept safe

- **No account probing.** The answer to a link request is the same whether an account exists or not:
  "If you supplied a correct email address, an email containing a magic login link should have been
  sent to you." Only if you turn off *Protect usernames* (*Site administration ▸ Security ▸ Site
  security settings*) does the page say that an email address or username was not found.
- **Requests come from your login page.** A link request needs the login token of the login form, so
  other sites can't make your site send emails.
- **No inbox flooding.** *Minimum time between magic link emails* limits the emails anybody can
  trigger for an account: requested links, the notice for other login methods and the new link sent
  when somebody uses an expired one.
- **No silent account switch.** If somebody else is logged in when a link is opened, they are asked
  "You are logged in as … Do you want to log out and log in as …?" before anything changes.
- **Links follow your policy.** A link only works while the account may use links. If you set
  *Supported authentication method* back to *Magic only*, or an account moves to another login method,
  its link stops working and is deleted. Like the core login, accounts whose own login method is
  disabled can't use links.
- **Suspended, unconfirmed and *No login* accounts, web service accounts and the guest account** get
  no links, and their links don't work.
- **Emails shared by several accounts** (with *Allow accounts with same email*) match none of them, so
  a link never goes to the wrong account. Those users can ask with their username if you allow it.

## Emails

Links and notices are sent by email from the no-reply address, like the password reset of core. They
don't depend on the users' notification preferences and aren't kept as notifications. Check
*Site administration ▸ Server ▸ Email ▸ Outgoing mail configuration* if users don't get them.

## Login page

The button is an identity provider of the login page. When a user clicks it after typing an email
address (or username) in the *Username* field, the link is requested straight away. Without anything
typed, without JavaScript or in a theme whose login form is different, the button opens a small page
that asks for the email address.

The plugin no longer turns on *Allow log in via email* (*Site administration ▸ Plugins ▸
Authentication ▸ Manage authentication*) for password logins. Turn it on yourself if users should be
able to log in with their email address and password.

## Privacy

The plugin stores the login link of a user (its key and expiry) and the time of the last link email.
Data requests export the dates, never a working key, and deletion requests delete the link and its
keys. Uninstalling removes the keys from the core table as well.

## Upgrading from 1.0 or 1.1

- Unused invitation keys are deleted, and the old *Magic invitation link expiry* setting is removed.
- Duplicate links of the same user are reduced to the newest.
- The new setting *Minimum time between magic link emails* starts at 2 minutes.
- *Allow log in via email* is no longer forced on, see *Login page*.
