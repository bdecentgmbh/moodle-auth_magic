@auth @auth_magic
Feature: Login to user for magic authentication.
  In order login user actions properly for magic auth.

  Background:
    Given I log in as "admin"
    And I navigate to "Plugins > Authentication > Manage authentication" in site administration
    And I click on "Enable" "link" in the "Magic authentication" "table_row"
    Then the following "users" exist:
      | username | firstname | lastname | email             | auth |
      | user_01  | user_01   | user_01  | user_01@gmail.com | magic|
      | user_02  | user_02   | user_02  | user_02@gmail.com | magic|
      | user_03  | user_03   | user_03  | user_03@gmail.com | manual|
    Then I log out

  @javascript
  Scenario: Check the magic user login checks.
    Given I am on homepage
    And I expand navigation bar
    And I click on "Log in" "link" in the ".logininfo" "css_element"
    #And I should see "Get a magic link via email"
    When I set the field "Username" to "user_01@gmail.com"
    And I click on "Get a magic link via email" "link"
    Then I should see "If you supplied a correct email address, an email containing a magic login link should have been sent to you."
    Then I set the field "Username" to "user_10@gmail.com"
    And I click on "Get a magic link via email" "link"
    And I should see "Doesn't exist user email"
    And I click on "Get a magic link via email" "link"
    And I should see "Invalid email address"
