Feature: frontend

  Scenario: Tests are running
    Given I am on "/new-ticket/"
    Then I should see "Name"
    Then I should see "Email"
    Then I should see "Subject"
    Then I should see "Message"
    Then I should not see "[create_ticket]"

  Scenario: Tests are running
    Given I am logged in as admin
    And I am on "/new-ticket/"
    Then I should see "Hello admin"
    Then I should see "Subject"
    Then I should see "Message"
    Then I should not see "[ticket_create]"

  Scenario: Create ticket flow as existing user who is not logged in
    Given I am on "/new-ticket/"
    Then I fill in "ticket-user" with "My Name"
    Then I fill in "ticket-email" with "ticket-user@wpsupportticket.com"
    Then I fill in "ticket-subject" with "My Subject"
    Then I fill in "ticket-message" with "My Message"
    Then I press "Send"
    Then I should see "It seems, you have already an account registered with your mail address ticket-user@wpsupportticket.com Please log in before you proceed."
    Then I follow "Please log in before you proceed."
    Then I should be on "http://localhost/tickets/wp-login.php?redirect_to=http%3A%2F%2Flocalhost%2Ftickets%2Fnew-ticket%2F"
    Then I fill in "user_login" with "ticket-user@wpsupportticket.com"
    Then I fill in "user_pass" with "abc"
    Then I press "Log In"
    Then I should see "Hello Ticket User"
    Then the input field "#ticket-subject" should contain "My Subject"
    Then the textarea field "#ticket-message" should contain "My Message"

  @javascript
  Scenario: Create ticket as existing user
    Given I am logged in with the name "ticket-user@wpsupportticket.com" and the password "abc"
    Given I am on "/new-ticket/"
    Then I fill in "ticket-subject" with "My Subject"
    Then I fill in "ticket-message" with "My Message"
    Then I press "Send"
    Then I should see "We have received your ticket and will contact you as soon as possible"
    Then I follow "Click here to see your ticket."
    Then I should see "Ticket #"
    Then I should see "My Subject"
    Then I should see "My Message"

    Given I am logged in with the name "admin" and the password "abc"
    Given I am on "wp-admin/admin.php?page=sts"
    Then I check "ticket[]"
    Then I select "Delete" from "bulk-action-selector-top"
    Then I press "Apply"

  Scenario: Create ticket check required fields
    Given I am on "/new-ticket/"
    Then I press "Send"
    Then I should see "Please enter your name."
    Then I fill in "My Name" for "ticket-user"
    Then I press "Send"
    Then I should see "Please enter your email address."
    Then I fill in "address@example.com" for "ticket-email"
    Then I press "Send"
    Then I should see "Please enter a subject."
    Then I fill in "this ticket has not been created." for "ticket-subject"
    Then I press "Send"
    Then I should see "Please enter a message."
    Then I fill in "a message" for "ticket-message"
    Then I fill in "" for "ticket-user"
    Then I press "Send"
    Then I should see "Please enter your name."

    Given I am logged in with the name "admin" and the password "abc"
    Given I am on "wp-admin/admin.php?page=sts"
    Then I should not see "this ticket has not been created."
