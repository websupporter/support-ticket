Feature: Default assignment of tickets

  @javascript
  Scenario: Change the default assignee to tickets
    Given I am logged in with the name admin and the password abc
    Given I am on "wp-admin/admin.php?page=sts-settings"
    Then I follow "User settings"
    Then I select "Ticket Agent" from "standard-ticket-agent"
    Then I press "update-user"
    Then I should see "Settings updated."

    Then I follow "New Ticket"
    Then I fill in "You are autoassigned now" for "ticket-subject"
    Then I fill in "Message" for "ticket-message"
    Then I press "Send"

    Then 2 should be selected in "#ticket-agent"

    Given I am on "wp-admin/admin.php?page=sts"
    Then I check "ticket[]"
    Then I select "Delete" from "bulk-action-selector-top"
    Then I press "Apply"
    Given I am on "wp-admin/admin.php?page=sts-settings"
    Then I follow "User settings"
    Then I select "admin" from "standard-ticket-agent"
    Then I press "update-user"
    Then I should see "Settings updated."
