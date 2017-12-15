Feature: The backend for the ticket user

  Scenario: Dashboard
    Given I am logged in with the name "ticket-user" and the password "abc"
    Given I am on "/wp-admin"
    Then I should see "Howdy, Ticket User" in "#wp-admin-bar-my-account"
    Then I should see "Dashboard" in "#adminmenu"
    Then I should see "Tickets" in "#adminmenu"
    Then I should see "Profile" in "#adminmenu"
    Then I should see "New Ticket" in "#adminmenu"
    Then I should not see "Posts" in "#adminmenu"
    Then I should not see "Pages" in "#adminmenu"
    Then I should not see "Media" in "#adminmenu"
    Then I should not see "Comments" in "#adminmenu"
    Then I should not see "Appearance" in "#adminmenu"
    Then I should not see "Plugins" in "#adminmenu"
    Then I should not see "Users" in "#adminmenu"
    Then I should not see "Tools" in "#adminmenu"
    Then I should not see "Settings" in "#adminmenu"

  Scenario: My tickets
    Given I am logged in with the name "ticket-user" and the password "abc"
    Given I am on "wp-admin/admin.php?page=sts"
    Then I should see "Keep this ticket" in ".wp-list-table"
    Then I should see "Ticket gets assigned to ticket-agent from admin" in ".wp-list-table"
    Then I should only see 2 "tr" elements in "#the-list"

  Scenario: My ticket
    Given I am logged in with the name "ticket-user" and the password "abc"
    Given I am on "wp-admin/admin.php?page=sts"
    Then I follow "Keep this ticket"
    Then I should see "Keep this ticket"
    Then I should see "Current status: Open"
    Then I should see "Current agent: Ticket Agent"
    Then element ".ticket-privatenote textarea" should not exist
    Then element "select[name='t[ticket-status]']" should not exist
    Then element "select[name='t[ticket-agent]']" should not exist

  Scenario: Create a new ticket in backend required fields
    Given I am logged in with the name "ticket-user" and the password "abc"
    Given I am on "wp-admin/admin.php?page=sts-new"
    Then I press "Send"
    Then I should see "Please enter a subject."
    Then I fill in "ticket-subject" with "Ticket from backend"
    Then I press "Send"
    Then I should see "Please enter a message."

  @javascript
  Scenario: Create a new ticket in backend and delete the ticket as admin
    Given I am logged in with the name "ticket-user" and the password "abc"
    Given I am on "wp-admin/admin.php?page=sts-new"
    Then I fill in "ticket-subject" with "Ticket from backend"
    Then I fill in "ticket-message" with "This ticket has been created in the backend."
    Then I press "Send"
    Then I should see "Ticket created."
    Then I should see "Ticket from backend" in "h2"
    Then I should see "This ticket has been created in the backend." in ".ticket-content"

    Given I am logged in with the name "admin" and the password "abc"
    Given I am on "wp-admin/admin.php?page=sts"
    Then I should see "Ticket from backend"
    Then I check "ticket[]"
    Then I select "Delete" from "bulk-action-selector-top"
    Then I press "Apply"
    Then I should not see "Ticket from backend"
