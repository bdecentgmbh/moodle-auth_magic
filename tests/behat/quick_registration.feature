@auth @auth_magic
Feature: Quic registrer method to magic authentication.
  In order create a user properly and enrolled to course.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | First        | teacher1@example.com |
      | student1 | Student   | First       | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format | coursedisplay | numsections | Enable completion tracking |
      | Course 1 | C1        | topics | 0             | 5           | yes                      |
      | Course 2 | C2        | topics | 0             | 5           | yes                      |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | teacher1 | C2     | editingteacher |
      | student1 | C2     | student        |
    Then I log in as "admin"
    And I navigate to "Plugins > Authentication > Manage authentication" in site administration
    And I click on "Enable" "link" in the "Magic authentication" "table_row"

  @javascript
  Scenario: Check the quick registration users.
    Given I log in as "admin"
    When I navigate to "Users > Accounts > Quick registration" in site administration
    Then I should see "Quick registration"
    And I set the following fields to these values:
        | First name    | user_01 |
        | Surname       | user_01 |
        | Email address | user_01@gmail.com |
    Then I press "Create user"
    Then I should see "Confirmation"
    And I click on ".modal-header button.close" "css_element"
    When I navigate to "Users > Accounts > List of magic keys" in site administration
    Then I should see "1 Users"
    Then I should see "user_01@gmail.com"
    When I navigate to "Users > Accounts > Quick registration" in site administration
    And I set the following fields to these values:
        | First name    | user_02 |
        | Surname       | user_02 |
        | Email address | user_02@gmail.com |
    Then I press "Create user"
    And I click on ".modal-header button.close" "css_element"
    When I navigate to "Users > Accounts > List of magic keys" in site administration
    Then I should see "2 Users"
    Then I should see "user_02@gmail.com"
    When I navigate to "Users > Accounts > Quick registration" in site administration
    And I set the following fields to these values:
        | First name    | user_03 |
        | Surname       | user_03 |
        | Email address | user_03@gmail.com |
    Then I press "Create user"
    And I click on ".modal-header button.close" "css_element"
    When I navigate to "Users > Accounts > List of magic keys" in site administration
    Then I should see "3 Users"
    Then I should see "user_03@gmail.com"

  @javascript
  Scenario: Check the register users assign to courses.
    Given I log in as "admin"
    When I navigate to "Users > Accounts > Quick registration" in site administration
    Then I should see "Quick registration"
    And I set the following fields to these values:
        | First name    | user_01 |
        | Surname       | user_01 |
        | Email address | user_01@gmail.com |
        | Course       | Course 1          |
    Then I press "Create user"
    Then I should see "Confirmation"
    And I should see "user_01 user_01"
    And I click on ".modal-header button.close" "css_element"
    When I navigate to "Users > Accounts > Quick registration" in site administration
    Then I should see "Quick registration"
    And I set the following fields to these values:
        | First name    | user_02           |
        | Surname       | user_02           |
        | Email address | user_02@gmail.com |
    Then I press "Create user"
    Then I should see "Confirmation"
    And I should see "user_02 user_02"
    And I click on ".modal-header button.close" "css_element"
    When I navigate to "Users > Accounts > List of magic keys" in site administration
    Then I should see "2 Users"
    Then I should see "user_01@gmail.com"
    Then I should see "user_02@gmail.com"
    And I should see "Course 1" in the "user_01 user_01" "table_row"
    And I should see "Course 2" in the "user_02 user_02" "table_row"
    And I am on "Course 1" course homepage
    And I navigate course enroll page "Course 1"
    Then I should see "user_01 user_01"
    And I am on "Course 2" course homepage
    And I navigate course enroll page "Course 2"
    Then I should see "user_02 user_02"

  @javascript
  Scenario: Can able to teacher added user in magic authentication
    Given I log in as "admin"
    Given the following "roles" exist:
      | name                | shortname |
      | Custom parent role  | parent    |
    And I set the following system permissions of "Custom parent role" role:
      | capability | permission |
      | auth/magic:viewchildloginlinks | Allow |
    And I set the following system permissions of "Teacher" role:
      | capability | permission |
      | auth/magic:cancoursequickregistration | Allow |
    When I navigate to "Plugins > Authentication > Magic authentication" in site administration
    Then I set the following fields to these values:
      | Owner account role | parent |
    And I press "Save changes"
    Then I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to course participants
    And I press "Quick registration"
    Then I should see "Course 1"
    Then I should see "Quick registration"
    And I set the following fields to these values:
        | First name    | user_01           |
        | Surname       | user_01           |
        | Email address | user_01@gmail.com |
        | enrolmentduration[number] | 24    |
        | enrolmentduration[timeunit]|hours |
    Then I press "Create user"
    Then I should see "Confirmation"
    And I click on ".modal-header button.close" "css_element"
    And I am on "Course 1" course homepage
    And I navigate to course participants
    Then I should see "user_01 user_01" in the "participants" "table"
    And I click on "Manual enrolments" "icon" in the "user_01 user_01" "table_row"
    Then I should see "##now##%A, %d %B %Y, %I:%M %p##" in the "Enrolment starts" "table_row"
    And I should see "##+1day##%A, %d %B %Y, %I:%M %p##" in the "Enrolment ends" "table_row"
    And I click on "Cancel" "button" in the "Enrolment details" "dialogue"
    And I follow "Profile" in the user menu
    Then I should see "Magic authentication"
    And I should see "List of magic keys"
    And I click on "List of magic keys" "link"
    Then I should see "user_01 user_01"
    And I should see "1 Users"
