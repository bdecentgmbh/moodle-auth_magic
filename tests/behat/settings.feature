@auth @auth_magic
Feature: Settings of magic authentication
  In order to adapt magic links to my site
  As an admin
  I need to change the settings of the plugin

  Background:
    Given I enable "magic" "auth" plugin
    And the following "users" exist:
      | username | firstname | lastname | email               | auth   |
      | user_01  | User      | One      | user_01@example.com | magic  |
      | user_03  | User      | Three    | user_03@example.com | manual |

  Scenario: Settings saved on the settings page take effect
    Given I log in as "admin"
    And I navigate to "Plugins > Authentication > Magic authentication" in site administration
    When I set the following fields to these values:
      | Supports password                | 1               |
      | s_auth_magic_loginexpiry[v]      | 30              |
      | s_auth_magic_requestinterval[v]  | 5               |
      | Configure login key link         | Until it expires |
      | Allow Username to get magic link | 1               |
      | Supported authentication method  | Any method      |
    And I press "Save changes"
    Then I should see "Changes saved"
    And the field "s_auth_magic_loginexpiry[v]" matches value "30"
    And the field "s_auth_magic_requestinterval[v]" matches value "5"
    And I log out
    # Supports password: the magic account logs in with its password.
    And I visit "/login/index.php"
    And I set the field "Username" to "user_01"
    And I set the field "Password" to "user_01"
    And I press "Log in"
    And I visit "/user/profile.php"
    And I should see "User One"
    And I log out
    # Allow Username and Any method: the manual account asks with its username and logs in with the link.
    And I visit "/auth/magic/login.php"
    And I set the field "Username or email" to "user_03"
    And I press "Get a magic link via email"
    And I should see "If you supplied a correct username, an email containing a magic login link should have been sent to your email address."
    And I log in with the magic login link of "user_03"
    And I visit "/user/profile.php"
    And I should see "User Three"
    # Until it expires: the same link logs in again.
    And I log out
    And I log in with the last magic login link again
    And I visit "/user/profile.php"
    And I should see "User Three"

  Scenario: Going back to magic only on the settings page removes the links of other login methods
    Given the following config values are set as admin:
      | authmethod   | 1    | auth_magic |
      | loginkeytype | more | auth_magic |
    And I visit "/auth/magic/login.php"
    And I set the field "Email address" to "user_03@example.com"
    And I press "Get a magic link via email"
    And I log in with the magic login link of "user_03"
    And I log out
    And I log in as "admin"
    And I navigate to "Plugins > Authentication > Magic authentication" in site administration
    When I set the field "Supported authentication method" to "Magic only"
    And I press "Save changes"
    And I log out
    And I open the last magic login link again
    # Deleted when the setting was saved, so the link is unknown, not merely refused.
    Then I should see "This magic link is no longer valid."
    And I visit "/auth/magic/login.php"
    And I set the field "Email address" to "user_03@example.com"
    And I press "Get a magic link via email"
    And I should see "If you supplied a correct email address, an email containing a magic login link should have been sent to you."
