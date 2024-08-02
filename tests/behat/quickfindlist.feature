@block @block_quickfindlist
Feature: Quickfind List
    As an admin
    In order to navigate to user pages quickly
    I need to be able to search for users by name

    Background:
        Given the following "users" exist:
            | username   | firstname | lastname     |
            | aarnoldson | Arnold    | Arnoldson    |
            | bbryson    | Bryan     | Bryson       |
            | cchristoph | Chris     | Christophski |
            | ddanson    | Dan       | Danson       |
            | eedmonson  | Ed        | Edmonson     |
        And the following "system role assigns" exist:
            | user       | role     |
            | aarnoldson | student  |
            | bbryson    | student  |
            | cchristoph | student  |
            | ddanson    | manager  |
            | eedmonson  | manager  |
        When I log in as "admin"
        And I am on site homepage
        And I turn editing mode on
        And I add the "Quickfind List" block

    @block_quickfindlist_default
    Scenario: Search with default settings
        Given I should see "All Users List" in the "block_quickfindlist" "block"

        When I set the field "quickfindlistsearch-1" to "son"
        And I press "quickfindsubmit-1"
        Then I should see "Arnold Arnoldson" in the "block_quickfindlist" "block"
        And I should see "Bryan Bryson" in the "block_quickfindlist" "block"
        And I should not see "Chris Christophski" in the "block_quickfindlist" "block"
        And I should see "Dan Danson" in the "block_quickfindlist" "block"
        And I should see "Ed Edmonson" in the "block_quickfindlist" "block"

        When I set the field "quickfindlistsearch-1" to "ski"
        And I press "quickfindsubmit-1"
        Then I should not see "Arnold Arnoldson" in the "block_quickfindlist" "block"
        And I should not see "Bryan Bryson" in the "block_quickfindlist" "block"
        And I should see "Chris Christophski" in the "block_quickfindlist" "block"
        And I should not see "Dan Danson" in the "block_quickfindlist" "block"
        And I should not see "Ed Edmonson" in the "block_quickfindlist" "block"

        When I set the field "quickfindlistsearch-1" to "an Da"
        And I press "quickfindsubmit-1"
        Then I should not see "Arnold Arnoldson" in the "block_quickfindlist" "block"
        And I should not see "Bryan Bryson" in the "block_quickfindlist" "block"
        And I should not see "Chris Christophski" in the "block_quickfindlist" "block"
        And I should see "Dan Danson" in the "block_quickfindlist" "block"
        And I should not see "Ed Edmonson" in the "block_quickfindlist" "block"

        When I click on "Dan Danson" "link" in the "block_quickfindlist" "block"
        Then I should see "Dan Danson" in the "h1" "css_element"
        And I should see "User details"

    @block_quickfindlist_role
    Scenario: Search a single role
        Given I configure the "block_quickfindlist" block
        And I set the field "config_role" to "Student"
        And I press "Save changes"
        And I turn editing mode off
        Then I should see "Student List" in the "block_quickfindlist" "block"

        When I set the field "quickfindlistsearch5" to "son"
        And I press "quickfindsubmit5"
        Then I should see "Arnold Arnoldson" in the "block_quickfindlist" "block"
        And I should see "Bryan Bryson" in the "block_quickfindlist" "block"
        And I should not see "Chris Christophski" in the "block_quickfindlist" "block"
        And I should not see "Dan Danson" in the "block_quickfindlist" "block"
        And I should not see "Ed Edmonson" in the "block_quickfindlist" "block"

        When I set the field "quickfindlistsearch5" to "ski"
        And I press "quickfindsubmit5"
        Then I should not see "Arnold Arnoldson" in the "block_quickfindlist" "block"
        And I should not see "Bryan Bryson" in the "block_quickfindlist" "block"
        And I should see "Chris Christophski" in the "block_quickfindlist" "block"
        And I should not see "Dan Danson" in the "block_quickfindlist" "block"
        And I should not see "Ed Edmonson" in the "block_quickfindlist" "block"

        When I set the field "quickfindlistsearch5" to "an Da"
        And I press "quickfindsubmit5"
        Then I should not see "Arnold Arnoldson" in the "block_quickfindlist" "block"
        And I should not see "Bryan Bryson" in the "block_quickfindlist" "block"
        And I should not see "Chris Christophski" in the "block_quickfindlist" "block"
        And I should not see "Dan Danson" in the "block_quickfindlist" "block"
        And I should not see "Ed Edmonson" in the "block_quickfindlist" "block"

    @block_quickfindlist_display
    Scenario: Change displayed name
        Given I configure the "block_quickfindlist" block
        And I set the field "config_userfields" to "[[username]]: [[lastname]]"
        And I press "Save changes"
        And I turn editing mode off

        When I set the field "quickfindlistsearch-1" to "an Da"
        And I press "quickfindsubmit-1"
        Then I should not see "Dan Danson" in the "block_quickfindlist" "block"
        And I should see "ddanson: Danson" in the "block_quickfindlist" "block"

    @block_quickfindlist_customlink
    Scenario: Custom link
        Given I configure the "block_quickfindlist" block
        And I set the field "config_url" to "/mod/forum/user.php?"
        And I press "Save changes"
        And I turn editing mode off

        When I set the field "quickfindlistsearch-1" to "an Da"
        And I press "quickfindsubmit-1"
        When I click on "Dan Danson" "link" in the "block_quickfindlist" "block"
        Then I should see "Dan Danson" in the "h1" "css_element"
        And I should see "Dan Danson has made no posts"
        And I should not see "User details"