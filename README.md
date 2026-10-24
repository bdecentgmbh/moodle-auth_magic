# Magic authentication

Moodle authentication plugin which lets users log in with a link they get by email, instead of a
password.

We believe there should be no barriers between learners and their courses, and forgotten passwords
are one of the most common. Moodle can reset passwords, but not everybody finds or understands it.
Every Moodle account has an email address, so a link by email avoids passwords altogether.

## Features

- **Magic links**: users type their email address (or username, if allowed) on the login page and
  get a link that logs them in, also to the page they wanted.
- **Expiry and single use**: links expire after a set time and can work once or until they expire.
- **Any login method**: optionally, accounts with other login methods can use links as well.
- **Safe by default**: link requests don't reveal whether an account exists, need the login form's
  token and are limited per account. A link of another account asks before it switches the logged-in
  user, and links stop working when your login method policy no longer allows them.
- **Privacy API**: exports and deletes the users' links.

The login page itself stays the core login page. A custom login page with only an email field,
sign-up and registration campaigns are part of
[Magic Authentication Pro](https://bdecent.de/product/magic-authentication-pro/).

## Requirements

Moodle 4.0 to 4.5. Outgoing email must work, as links are sent by email only.

## Installation

Install the plugin like any other plugin to folder `auth/magic`, see
[Installing plugins](https://docs.moodle.org/en/Installing_plugins). Then enable it under
*Site administration ▸ Plugins ▸ Authentication ▸ Manage authentication* and review its settings.

## Documentation

- [Administrator guide](docs/admin-guide.md): settings, security, emails, privacy and upgrading
- [Student guide](docs/student-guide.md): logging in with a magic link
- [Developer guide](docs/developer-guide.md): how the plugin works
- [Changes](CHANGES.md)

## Theme support

This plugin is developed and tested on Moodle Core's Boost theme. In themes with a different login
form, the button opens a page that asks for the email address.

## Plugin repositories

This plugin is published in the Moodle plugins directory: https://moodle.org/plugins/auth_magic
The latest development version is on GitHub: https://github.com/bdecentgmbh/moodle-auth_magic

## Bug reports, support and feature proposals

Please report bugs and propose features on GitHub: https://github.com/bdecentgmbh/moodle-auth_magic/issues.
Pull requests are welcome: https://github.com/bdecentgmbh/moodle-auth_magic/pulls. We do our best to
help, but due to limited resources we can't always provide per-case support.

## Moodle release support

This plugin is maintained for the two most recent major releases of Moodle as well as the most recent
LTS release of Moodle.

## Translating this plugin

This plugin ships with an English language pack only. Translations are managed through AMOS
(https://lang.moodle.org) and become part of Moodle's official language packs.

## Copyright

bdecent gmbh, https://bdecent.de
