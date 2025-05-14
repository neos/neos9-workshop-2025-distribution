@flowEntities
Feature: DEMO

  Background:
    And I initialize content repository "default"
    And I am in content repository "default"
    And the command CreateRootWorkspace is executed with payload:
      | Key                | Value           |
      | workspaceName      | "live"          |
      | newContentStreamId | "cs-identifier" |

  Scenario: DEMO
    When I run this scenario
