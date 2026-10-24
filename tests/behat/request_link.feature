@auth @auth_magic
Feature: Ask for a magic login link
  In order to log in without a password
  As a user
  I need to get a magic link for my account

  Background:
    Given the following config values are set as admin:
      | auth | magic |
    And the following "users" exist:
      | username | firstname | lastname | email               | auth   |
      | user_01  | User      | One      | user_01@example.com | magic  |
      | user_03  | User      | Three    | user_03@example.com | manual |

  @javascript
  Scenario: The answer is the same whether the account exists or not
    Given I visit "/login/index.php"
    When I set the field "Username" to "user_01@example.com"
    And I click on the magic link button
    Then I should see "If you supplied a correct email address, an email containing a magic login link should have been sent to you."
    And I set the field "Username" to "nobody@example.com"
    And I click on the magic link button
    And I should see "If you supplied a correct email address, an email containing a magic login link should have been sent to you."
    And I should not see "not found"
    And I set the field "Username" to "user_01"
    And I click on the magic link button
    And I should see "Invalid email address"

  @javascript
  Scenario: Without a username the button asks for the email address
    Given I visit "/login/index.php"
    When I click on the magic link button
    Then I should see "Log in with a magic link"
    And I should see "Enter the email address of your account."
    And I set the field "Email address" to "user_01@example.com"
    And I press "Get a magic link via email"
    And I should see "If you supplied a correct email address, an email containing a magic login link should have been sent to you."

  Scenario: Ask for a link without JavaScript
    Given I visit "/auth/magic/login.php"
    When I set the field "Email address" to "user_01@example.com"
    And I press "Get a magic link via email"
    Then I should see "If you supplied a correct email address, an email containing a magic login link should have been sent to you."

  @javascript
  Scenario: Usernames are accepted if the plugin allows them
    Given the following config values are set as admin:
      | loginoption | 1 | auth_magic |
    And I visit "/login/index.php"
    When I set the field "Username" to "user_01"
    And I click on the magic link button
    Then I should see "If you supplied a correct username, an email containing a magic login link should have been sent to your email address."
    And I visit "/auth/magic/login.php"
    And I should see "Enter the email address or the username of your account."
    And I set the field "Username or email" to "nobody"
    And I press "Get a magic link via email"
    And I should see "If you supplied a correct username, an email containing a magic login link should have been sent to your email address."

  Scenario: Unknown accounts are named if the site doesn't protect usernames
    Given the following config values are set as admin:
      | protectusernames | 0 |
    And I visit "/auth/magic/login.php"
    When I set the field "Email address" to "nobody@example.com"
    And I press "Get a magic link via email"
    Then I should see "The email address was not found in the database"

  @javascript
  Scenario: A password login that returns to the link page doesn't show an error
    Given I visit "/auth/magic/login.php"
    And I click on "Back to the login page" "link"
    When I set the field "Username" to "user_03"
    And I set the field "Password" to "user_03"
    And I press "Log in"
    Then I should not see "A required parameter"
    And I should not see "Log in with a magic link"
    And I visit "/auth/magic/login.php"
    And I should not see "A required parameter"
    And I should not see "Log in with a magic link"
