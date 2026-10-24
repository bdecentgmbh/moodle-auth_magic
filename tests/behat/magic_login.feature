@auth @auth_magic
Feature: Log in with a magic link
  In order to log in without a password
  As a user
  I need to open the magic link I was sent

  Background:
    Given I enable "magic" "auth" plugin
    And the following "users" exist:
      | username | firstname | lastname | email               | auth   |
      | user_01  | User      | One      | user_01@example.com | magic  |
      | user_02  | User      | Two      | user_02@example.com | magic  |

  Scenario: Opening a link only asks, so that mail scanners don't use it up
    When I open the magic login link of "user_01"
    Then I should see "Do you want to log in to Acceptance test site as User One?"
    And I open the last magic login link again
    And I press "Log in"
    And I visit "/user/profile.php"
    And I should see "User One"

  Scenario: A single-use link logs in once
    When I log in with the magic login link of "user_01"
    And I visit "/user/profile.php"
    Then I should see "User One"
    And I log out
    And I open the last magic login link again
    And I should see "This magic link is no longer valid."
    And I should see "Log in to"

  Scenario: A reusable link logs in until it expires
    Given the following config values are set as admin:
      | loginkeytype | more | auth_magic |
    When I log in with the magic login link of "user_01"
    And I log out
    And I log in with the last magic login link again
    And I visit "/user/profile.php"
    Then I should see "User One"

  Scenario: An expired link sends a new one
    Given the magic login link of "user_01" has expired
    When I log in with the magic login link of "user_01"
    Then I should see "The magic login link has expired. A new magic login link has been sent to your email address."
    And I should see "Log in to"
    And I log in with the magic login link of "user_01"
    And I visit "/user/profile.php"
    And I should see "User One"

  Scenario: A link of another account asks before switching accounts
    Given I log in with the magic login link of "user_02"
    When I open the magic login link of "user_01"
    Then I should see "You are logged in as User Two. Do you want to log out and log in as User One?"
    And I press "Cancel"
    And I visit "/user/profile.php"
    And I should see "User Two"
    And I open the magic login link of "user_01"
    And I press "Continue"
    And I should see "Do you want to log in to Acceptance test site as User One?"
    And I press "Log in"
    And I visit "/user/profile.php"
    And I should see "User One"

  Scenario: The own link of a logged-in user goes on to the site
    Given the following config values are set as admin:
      | loginkeytype | more | auth_magic |
    And I log in with the magic login link of "user_01"
    When I open the last magic login link again
    Then I should not see "Do you want to"
    And I visit "/user/profile.php"
    And I should see "User One"

  Scenario: Other login methods use links only while the plugin supports them
    Given the following "users" exist:
      | username | firstname | lastname | email               | auth   |
      | user_03  | User      | Three    | user_03@example.com | manual |
    And the following config values are set as admin:
      | authmethod   | 1    | auth_magic |
      | loginkeytype | more | auth_magic |
    And I visit "/auth/magic/login.php"
    And I set the field "Email address" to "user_03@example.com"
    And I press "Get a magic link via email"
    When I log in with the magic login link of "user_03"
    And I visit "/user/profile.php"
    Then I should see "User Three"
    And I log out
    And the following config values are set as admin:
      | authmethod | 0 | auth_magic |
    And I open the last magic login link again
    And I should see "Your account can no longer log in with a magic link."

  Scenario: With "Only for single-use links" reusable links log in as soon as they are opened
    Given the following config values are set as admin:
      | confirmlogin | once | auth_magic |
      | loginkeytype | more | auth_magic |
    When I open the magic login link of "user_01"
    Then I should not see "Do you want to"
    And I visit "/user/profile.php"
    And I should see "User One"
    And I log out
    And the following config values are set as admin:
      | loginkeytype | once | auth_magic |
    And I open the magic login link of "user_02"
    And I should see "Do you want to log in to Acceptance test site as User Two?"

  Scenario: With "Never" links log in as soon as they are opened, but switching accounts still asks
    Given the following config values are set as admin:
      | confirmlogin | never | auth_magic |
    When I open the magic login link of "user_02"
    And I visit "/user/profile.php"
    Then I should see "User Two"
    And I open the magic login link of "user_01"
    And I should see "You are logged in as User Two. Do you want to log out and log in as User One?"
    And I press "Continue"
    And I visit "/user/profile.php"
    And I should see "User One"
