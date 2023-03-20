# Magic authentication

Moodle authentication plugin which enables users to login using magic links (instead of passwords)

# Requirements

This plugin requires Moodle 3.9+

# Motivation for this plugin

We believe it is important that there are no barriers and obstacles between the learner and the course. One of the most common problems is that people forget their password. While Moodle already has a very easy way to reset the password, not all users understand that it exists and how to use it. The logical consequence is to avoid the necessity of passwords anyway. Not every target group has email access, but since it's a requirement for a moodle account, we think it's an assumption that is ok to set. 

# Installation

Install the plugin like any other plugin to folder /auth/magic
See http://docs.moodle.org/en/Installing_plugins for details on installing Moodle plugins

# Initial Configuration

Admins need to enable Magic Authentication under Site Administration > Plugins > Authentication > Manage authentication by clicking the eye icon.
And they should review the expiration times to match their requirements.

# How to use

On the login page, users have to enter their email address. 
Then, they can either click a button to receive an email with a magic link. When the user clicks on that link, she/he is automatically logged in. We do support deep links, i.e. the user will be taken to the URL that the user originally tried to access.
Or, they can instead directly type in their password.

# Theme support

This plugin is developed and tested on Moodle Core's Boost theme. It should also work with Boost child themes, including Moodle Core's Classic theme. However, we can't support any other theme than Boost.

# Plugin repositories

This plugin will be published and regularly updated in the Moodle plugins repository: https://moodle.org/plugins/auth_magic 
The latest development version can be found on Github: https://github.com/bdecentgmbh/moodle-auth_magic

# Bug and problem reports / Support requests

This plugin is carefully developed and thoroughly tested, but bugs and problems can always appear. Please report bugs and problems on Github: https://github.com/bdecentgmbh/moodle-auth_magic/issues We will do our best to solve your problems, but please note that due to limited resources we can't always provide per-case support.

# Feature proposals

Please issue feature proposals on Github: https://github.com/bdecentgmbh/moodle-auth_magic/issues Please create pull requests on Github: https://github.com/bdecentgmbh/moodle-mod_whiteboard/pulls We are always interested to read about your feature proposals or even get a pull request from you, but please accept that we can handle your issues only as feature proposals and not as feature requests.

# Moodle release support

This plugin is maintained for the two most recent major releases of Moodle as well as the most recent LTS release of Moodle. If you are running a legacy version of Moodle, but want or need to run the latest version of this plugin, you can get the latest version of the plugin, remove the line starting with $plugin->requires from version.php and use this latest plugin version then on your legacy Moodle. However, please note that you will run this setup completely at your own risk. We can't support this approach in any way and there is an undeniable risk for erratic behavior.

# Translating this plugin

This Moodle plugin is shipped with an english language pack only. All translations into other languages must be managed through AMOS (https://lang.moodle.org) by what they will become part of Moodle's official language pack.

# Copyright

bdecent gmbh
bdecent.de
