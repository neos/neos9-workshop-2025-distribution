Feature: Consecutive import

  Background:
    And I initialize content repository "default"
    And I am in content repository "default"
    And the command CreateRootWorkspace is executed with payload:
      | Key                | Value           |
      | workspaceName      | "live"          |
      | newContentStreamId | "cs-identifier" |
    And I am in workspace "live" and dimension space point {"language": "en_US"}
    And the command CreateRootNodeAggregateWithNode is executed with payload:
      | Key             | Value             |
      | nodeAggregateId | "sites"           |
      | nodeTypeName    | "Neos.Neos:Sites" |

    And the following CreateNodeAggregateWithNode commands are executed:
      | nodeAggregateId | parentNodeAggregateId | nodeTypeName                | initialPropertyValues | originDimensionSpacePoint | nodeName |
      | homepage        | sites                 | Neos.Demo:Document.Homepage | {"title": "home"}     | {"language": "en_US"}     | site-a   |
      | blog            | homepage              | Neos.Demo:Document.Blog     | {"title": "blog"}     | {"language": "en_US"}     |          |

    And the command CreateNodeVariant is executed with payload:
      | Key             | Value                |
      | nodeAggregateId | "homepage"           |
      | sourceOrigin    | {"language":"en_US"} |
      | targetOrigin    | {"language":"de"}    |

    And the command CreateNodeVariant is executed with payload:
      | Key             | Value                |
      | nodeAggregateId | "blog"           |
      | sourceOrigin    | {"language":"en_US"} |
      | targetOrigin    | {"language":"de"}    |

  Scenario: First import
    When I import file "sample1" into blog "blog"

    # @todo write test steps

  Scenario: Second import
    When I import file "sample1" into blog "blog"
    When I import file "sample2" into blog "blog"

    # @todo write test steps

