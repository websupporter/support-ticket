Feature: Capabilities

  @javascript
  Scenario: change admin capabilities "create ticket"
    Given I am logged in with the name admin and the password abc
    Given I am on "wp-admin/admin.php?page=sts-settings"
    Then I follow "User settings"
    Then I uncheck "user[roles][read_own_tickets][administrator]"
    Then I press "update-user"

    Given I am on "wp-admin/admin.php?page=sts"
    Then I should see "Sorry, you are not allowed to access this page."
    Given I am on "wp-admin/admin.php?page=sts-new"
    Then I should see "Sorry, you are not allowed to access this page."
    Given I am on "wp-admin/admin.php?page=sts-settings#user"
    Then I check "user[roles][read_own_tickets][administrator]"
    Then I press "update-user"
    Given I am on "wp-admin/admin.php?page=sts"
    Then I should see "Tickets"

  @javascript
  Scenario: change admin capabilities "read assigned tickets & read_other_tickets"
    Given I am logged in with the name admin and the password abc
    Given I am on "wp-admin/admin.php?page=sts-settings"
    Then I follow "User settings"
    Then I uncheck "user[roles][read_other_tickets][administrator]"
    Then I uncheck "user[roles][read_assigned_tickets][administrator]"
    Then I press "update-user"

    Given I am on "wp-admin/admin.php?page=sts"
    Then I should see "No tickets found."

    Then I follow "New Ticket"
    Then I fill in "But I can see my tickets" for "ticket-subject"
    Then I fill in "Message" for "ticket-message"
    Then I press "Send"
    Given I am on "wp-admin/admin.php?page=sts"
    Then I should see "But I can see my tickets"
    Then I check "ticket[]"
    Then I select "Delete" from "bulk-action-selector-top"
    Then I press "Apply"

    Given I am on "wp-admin/admin.php?page=sts-settings#user"
    Then I check "user[roles][read_other_tickets][administrator]"
    Then I check "user[roles][read_assigned_tickets][administrator]"
    Then I press "update-user"
    Given I am on "wp-admin/admin.php?page=sts"
    Then I should see "Keep this ticket"

  @javascript
  Scenario: change admin capabilities "assign agents to tickets"
    Given I am logged in with the name admin and the password abc
    Given I am on "wp-admin/admin.php?page=sts-settings"
    Then I follow "User settings"
    Then I uncheck "user[roles][assign_agent_to_ticket][administrator]"
    Then I press "update-user"

    Given I am on "wp-admin/admin.php?page=sts&action=single&ID=16"
    Then element "select[name='t[ticket-agent]']" should not exist
    Given I am on "wp-admin/admin.php?page=sts-settings#user"
    Then I check "user[roles][assign_agent_to_ticket][administrator]"
    Then I press "update-user"

  @javascript
  Scenario: change admin capabilities "delete tickets"
    Given I am logged in with the name admin and the password abc
    Given I am on "wp-admin/admin.php?page=sts-settings"
    Then I follow "User settings"
    Then I uncheck "user[roles][delete_other_tickets][administrator]"
    Then I press "update-user"

    Given I am on "wp-admin/admin.php?page=sts"
    Then element "#bulk-action-selector-top" should not exist
    Given I am on "wp-admin/admin.php?page=sts-settings#user"
    Then I check "user[roles][delete_other_tickets][administrator]"
    Then I press "update-user"