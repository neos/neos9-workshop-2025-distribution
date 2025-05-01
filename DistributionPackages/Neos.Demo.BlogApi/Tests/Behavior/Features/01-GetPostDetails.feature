Feature: 01-GetPostDetails

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
      | features        | homepage              | Neos.Demo:Document.Page     | {"title": "features"} | {"language": "en_US"}     |          |
      | feature-a       | features              | Neos.Demo:Document.Page     | {"title": "a"}        | {"language": "en_US"}     |          |
      | feature-b       | features              | Neos.Demo:Document.Page     | {"title": "b"}        | {"language": "en_US"}     |          |

    And the command CreateNodeVariant is executed with payload:
      | Key             | Value                |
      | nodeAggregateId | "homepage"           |
      | sourceOrigin    | {"language":"en_US"} |
      | targetOrigin    | {"language":"de"}    |

    And the command CreateNodeVariant is executed with payload:
      | Key             | Value                |
      | nodeAggregateId | "features"           |
      | sourceOrigin    | {"language":"en_US"} |
      | targetOrigin    | {"language":"de"}    |

    And the command CreateNodeVariant is executed with payload:
      | Key             | Value                |
      | nodeAggregateId | "feature-a"          |
      | sourceOrigin    | {"language":"en_US"} |
      | targetOrigin    | {"language":"de"}    |

    And the command SetNodeProperties is executed with payload:
      | Key                       | Value                      |
      | nodeAggregateId           | "features"                 |
      | originDimensionSpacePoint | {"language": "de"}         |
      | propertyValues            | {"title": "features (de)"} |

    And the command SetNodeProperties is executed with payload:
      | Key                       | Value               |
      | nodeAggregateId           | "feature-a"         |
      | originDimensionSpacePoint | {"language": "de"}  |
      | propertyValues            | {"title": "a (de)"} |

  Scenario: GetPostDetails for homepage
    When I issue the following query to "http://127.0.0.1:8081/get-blog-details":
      | Key  | Value                                                                                                                          |
      | node | {"contentRepositoryId":"default","workspaceName":"live","dimensionSpacePoint":{"language":"en_US"},"aggregateId":"homepage"}   |
    Then I expect the following query response:
      """json
      {
          "success": {
              "uri": "node://homepage"
          }
      }
      """
