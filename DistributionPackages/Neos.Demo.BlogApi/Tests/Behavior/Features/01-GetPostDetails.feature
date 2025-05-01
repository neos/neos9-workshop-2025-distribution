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
      | nodeAggregateId | parentNodeAggregateId | nodeTypeName                   | initialPropertyValues                                                                                                                                                           | originDimensionSpacePoint | nodeName |
      | homepage        | sites                 | Neos.Demo:Document.Homepage    | {"title": "home"}                                                                                                                                                               | {"language": "en_US"}     | site-a   |
      | some-page       | homepage              | Neos.Demo:Document.Page        | {"title": "some page"}                                                                                                                                                          | {"language": "en_US"}     |          |
      | blog            | homepage              | Neos.Demo:Document.Blog        | {"title": "blog"}                                                                                                                                                               | {"language": "en_US"}     |          |
      | post-a          | blog                  | Neos.Demo:Document.BlogPosting | {"title": "a", "abstract": "<p>This is <strong>my</strong> blog post</p>", "authorName": "Marc Henry", "datePublished":{"__type": "DateTimeImmutable", "value": "2025-04-03"} } | {"language": "en_US"}     |          |
      | post-b          | blog                  | Neos.Demo:Document.BlogPosting | {"title": "b"}                                                                                                                                                                  | {"language": "en_US"}     |          |

    And the command CreateNodeVariant is executed with payload:
      | Key             | Value                |
      | nodeAggregateId | "homepage"           |
      | sourceOrigin    | {"language":"en_US"} |
      | targetOrigin    | {"language":"de"}    |

    And the command CreateNodeVariant is executed with payload:
      | Key             | Value                |
      | nodeAggregateId | "blog"               |
      | sourceOrigin    | {"language":"en_US"} |
      | targetOrigin    | {"language":"de"}    |

    And the command CreateNodeVariant is executed with payload:
      | Key             | Value                |
      | nodeAggregateId | "post-a"             |
      | sourceOrigin    | {"language":"en_US"} |
      | targetOrigin    | {"language":"de"}    |

    And the command SetNodeProperties is executed with payload:
      | Key                       | Value                      |
      | nodeAggregateId           | "post-a"                   |
      | originDimensionSpacePoint | {"language": "de"}         |
      | propertyValues            | {"title": "features (de)"} |

    And the command SetNodeProperties is executed with payload:
      | Key                       | Value               |
      | nodeAggregateId           | "post-a"            |
      | originDimensionSpacePoint | {"language": "de"}  |
      | propertyValues            | {"title": "a (de)"} |

  Scenario: GetPostDetails for homepage
    When I issue the following query to "http://127.0.0.1:8081/get-blog-details":
      | Key  | Value                                                                                                                                          |
      | node | "{\"contentRepositoryId\":\"default\",\"workspaceName\":\"live\",\"dimensionSpacePoint\":{\"language\":\"en_US\"},\"aggregateId\":\"post-a\"}" |
    Then I expect the following query response:
      """json
      {
          "success": {
              "title": "a",
              "abstract": "This is my blog post",
              "authorName": "Marc Henry",
              "datePublished": "2025-04-03T00:00:00+00:00"
          }
      }
      """
