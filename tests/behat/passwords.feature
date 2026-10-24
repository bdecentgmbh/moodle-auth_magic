@auth @auth_magic
Feature: Passwords for magic accounts
  In order to choose between links and passwords
  As an admin
  I need magic accounts to have passwords only if the plugin supports them

  Background:
    Given I enable "magic" "auth" plugin
    And the following "users" exist:
      | username | firstname | lastname | email               | auth  |
      | user_01  | User      | One      | user_01@example.com | magic |

  Scenario: Without password support a magic account can't log in or change a password
    Given I visit "/login/index.php"
    When I set the field "Username" to "user_01"
    And I set the field "Password" to "user_01"
    And I press "Log in"
    And I visit "/user/profile.php"
    Then I should not see "User One"
    And I log in with the magic login link of "user_01"
    And I follow "Preferences" in the user menu
    And I should see "Edit profile"
    And I should not see "Change password"

  Scenario: With password support a magic account logs in and changes its password
    Given the following config values are set as admin:
      | supportpassword | 1 | auth_magic |
    And I visit "/login/index.php"
    When I set the field "Username" to "user_01"
    And I set the field "Password" to "user_01"
    And I press "Log in"
    And I follow "Preferences" in the user menu
    And I follow "Change password"
    And I set the following fields to these values:
      | Current password     | user_01         |
      | New password         | Magic-Passw0rd! |
      | New password (again) | Magic-Passw0rd! |
    And I press "Save changes"
    Then I should see "Password has been changed"
    And I log out
    And I visit "/login/index.php"
    And I set the field "Username" to "user_01"
    And I set the field "Password" to "Magic-Passw0rd!"
    And I press "Log in"
    And I visit "/user/profile.php"
    And I should see "User One"

  @javascript
  Scenario: Admins can set the password of a magic account only with password support
    Given I log in as "admin"
    When I am on the "user_01" "user > editing" page
    Then the "New password" "field" should be disabled
    And the following config values are set as admin:
      | supportpassword | 1 | auth_magic |
    And I am on the "user_01" "user > editing" page
    And the "New password" "field" should be enabled
