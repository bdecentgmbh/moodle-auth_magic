@auth @auth_magic
Feature: List of user magic links to magic authentication.
  In order view the list of user actions properly.
  Background:
    Given I log in as "admin"
    And I navigate to "Plugins > Authentication > Manage authentication" in site administration
    And I click on "Enable" "link" in the "Magic authentication" "table_row"
    Then the following "users" exist:
      | username | firstname | lastname | email             | auth |
      | user_01  | user_01   | user_01  | user_01@gmail.com | magic|
      | user_02  | user_02   | user_02  | user_02@gmail.com | magic|
      | user_03  | user_03   | user_03  | user_03@gmail.com | magic|
      | user_04  | user_04   | user_04  | user_04@gmail.com | magic|
      | user_05  | user_05   | user_05  | user_05@gmail.com | magic|
      | user_06  | user_06   | user_06  | user_06gmail.com  | magic|
      | user_07  | user_07   | user_07  | user_07@gmail.com | magic|

  @javascript
  Scenario: Check the magic user actions.
    Given I log in as "admin"
    When I navigate to "Users > Accounts > List of magic keys" in site administration
    Then I should see "7 Users"
    And I follow "user_01 user_01"
    And I should see "Edit profile"
    And I should see "user_01@gmail.com"
    When I navigate to "Users > Accounts > List of magic keys" in site administration
    And I set the following fields to these values:
      | email | user_01@gmail.com |
    And I press "Add filter"
    And I should see "user_01@gmail.com"
    And I click on "Delete" "link"
    And I press "Delete"
    Then I press "Remove all filters"
    Then I should not see "user_01 user_01"
    Then I should see "6 Users"
    And I click on ".icon[title=Edit]" "css_element" in the "user_02@gmail.com" "table_row"
    Then I should see "user_02 user_02"
    And I set the following fields to these values:
      | Email address   | demouser_02@gmail.com |
    Then I press "Update profile"
    When I navigate to "Users > Accounts > List of magic keys" in site administration
    Then I should see "demouser_02@gmail.com" in the "user_02 user_02" "table_row"
    And I should see "user_02 user_02"

  @javascript
  Scenario: Check the sent invitation for magic users.
    Given I log in as "admin"
    When I navigate to "Users > Accounts > List of magic keys" in site administration
    And I click on ".icon[title=\"Send the magic link to the user\"]" "css_element" in the "user_01@gmail.com" "table_row"
    Then I should see "Sent the invitation link to the mail"
