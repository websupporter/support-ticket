Feature: Backend basics

  Scenario: Dashboard
    Given I am logged in with the name "ticket-agent" and the password "abc"
    Given I am on "/wp-admin"
    Then I should see "Howdy, Ticket Agent" in "#wp-admin-bar-my-account"
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

  Scenario: No tickets assigned
    Given I am logged in with the name "ticket-agent-with-no-tickets" and the password "abc"
    Given I am on "wp-admin/admin.php?page=sts"
    Then I should see "No tickets found."

  Scenario: Tickets assigned
    Given I am logged in with the name "ticket-agent" and the password "abc"
    Given I am on "wp-admin/admin.php?page=sts"
    Then I should not see "No tickets found."
    Then I should see "Keep this ticket"
    Then I should see "Ticket User"
    Then I should see "Open" in "td.column-status"
    Then I follow "Keep this ticket"
    Then I should be on "wp-admin/admin.php?page=sts&action=single&ID=16"

  Scenario: Change Ticket status
    Given I am logged in with the name "ticket-agent" and the password "abc"
    Given I am on "wp-admin/admin.php?page=sts&action=single&ID=16"
    Then I select "Pending" from "ticket-status"
    Then I press "Update"
    Then I am on "wp-admin/admin.php?page=sts"
    Then I should see "Pending" in "tr.status-pending"
    Then I am on "wp-admin/admin.php?page=sts&action=single&ID=16"
    Then I select "Open" from "ticket-status"
    Then I press "Update"

  @javascript
  Scenario: Add a private note
    Given I am logged in with the name "ticket-agent" and the password "abc"
    Given I am on "wp-admin/admin.php?page=sts&action=single&ID=16"
    Then I fill in "t[privatenote]" with "asshole"
    Then I press "Update"
    Then I should see "asshole" in ".ticket-privatenote textarea"
    Given I am logged in with the name "ticket-user" and the password "abc"
    Given I am on "wp-admin/admin.php?page=sts&action=single&ID=16"
    Then I should not see "asshole"
    Then element ".ticket-privatenote textarea" should not exist
    Given I am logged in with the name "admin" and the password "abc"
    Given I am on "wp-admin/admin.php?page=sts&action=single&ID=16"
    Then I should see "asshole" in ".ticket-privatenote textarea"
    Then I fill in "t[privatenote]" with ""
    Then I press "Update"

    @javascript
  Scenario: Admin assigns ticket to ticket agent
    Given I am logged in with the name "admin" and the password "abc"
    Given I am on "wp-admin/admin.php?page=sts"
    Then I should see "Ticket gets assigned to ticket-agent from admin"
    Given I follow "Ticket gets assigned to ticket-agent from admin"
    Then element "select[name='t[ticket-status]']" should exist
    Then element "select[name='t[ticket-agent]']" should exist
    Then I select "Ticket Agent" from "ticket-agent"
    Then I press "Update"
    Given I am on "wp-admin/admin.php?page=sts"
    Then I should see "Ticket gets assigned to ticket-agent from admin"
    Given I am logged in with the name "ticket-agent" and the password "abc"
    Given I am on "wp-admin/admin.php?page=sts"
    Then I should see "Ticket gets assigned to ticket-agent from admin"
    Given I am logged in with the name "admin" and the password "abc"
    Given I am on "wp-admin/admin.php?page=sts"
    Given I follow "Ticket gets assigned to ticket-agent from admin"
    Then I select "admin" from "ticket-agent"
    Then I press "Update"
    Given I am logged in with the name "ticket-agent" and the password "abc"
    Given I am on "wp-admin/admin.php?page=sts"
    Then I should not see "Ticket gets assigned to ticket-agent from admin"
