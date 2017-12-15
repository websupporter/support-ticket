Feature: Metafields

  @javascript
  Scenario: Create fields
    Given I am logged in with the name admin and the password abc
    Given I am on "wp-admin/admin.php?page=sts-settings"
    Then I follow "Ticket settings"
    Then I follow "Add a new form field"
    Then I should see "Add a new form field"
    Then I fill in "Field Label" for "label"
    Then I fill in "field-label" for "metakey"
    Then I select "Input field" from "tag"
    Then I press "Add"
    Then I should see "Field Label" in ".ticket-field-list"

    Then I follow "Add a new form field"
    Then I should see "Add a new form field"
    Then I fill in "Select Label" for "label"
    Then I select "Selectbox" from "tag"
    Then I wait 1 seconds
    Then I should see "Choices:"
    Then I fill in "choice a" for "choices"
    Then I press "Add"
    Then I should see "Select Label" in ".ticket-field-list"

    Then I wait 1 seconds
    Then I press "update-ticket"
    Then I should see "Settings updated."
    Then I should see "Select Label" in ".ticket-field-list"
    Then I should see "Field Label" in ".ticket-field-list"

  @javascript
  Scenario: Edit fields
    Given I am logged in with the name admin and the password abc
    Given I am on "wp-admin/admin.php?page=sts-settings#ticket"
    Then I follow "Edit"
    Then I should see "field-label" in "span#edit_metakey_display"
    Then I fill in "Field edit" for "edit_label"
    Then I press "Edit"
    Then I should see "Field edit" in ".ticket-field-list"

    Then I press "update-ticket"
    Then I should see "Settings updated."
    Then I should see "Select Label" in ".ticket-field-list"
    Then I should not see "Field Label" in ".ticket-field-list"
    Then I should see "Field edit" in ".ticket-field-list"

  @javascript
  Scenario: Create a ticket with those fields
    Given I am logged in with the name admin and the password abc
    Given I am on "new-ticket"
    Then I fill in "Metafields" for "ticket-subject"
    Then I fill in "This ticket has metafields" for "ticket-message"
    Then I fill in "content-of-the-metafield" for "field-label"
    Then I select "choice a" from "select-label"
    Then I press "Send"
    Then I follow "Click here to see your ticket"
    Then I should see "Field edit" in ".wp-list-table"
    Then I should see "content-of-the-metafield" in ".wp-list-table"
    Then I should see "Select Label" in ".wp-list-table"
    Then I should see "choice a" in ".wp-list-table"

    Given I am on "wp-admin/admin.php?page=sts"
    Then I check "ticket[]"
    Then I select "Delete" from "bulk-action-selector-top"
    Then I press "Apply"

  @javascript
  Scenario: Delete the meta fields
    Given I am logged in with the name admin and the password abc
    Given I am on "wp-admin/admin.php?page=sts-settings#ticket"
    Then I follow "Trash"
    Then I follow "Trash"
    Then I press "update-ticket"
    Then I should not see "Select Label" in ".ticket-field-list"
    Then I should not see "Field edit" in ".ticket-field-list"
