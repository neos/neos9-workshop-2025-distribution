@flowEntities
Feature: 02-GetPostListing

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

    And A site exists for node name "neosdemo" and domain "http://localhost" and package Vendor.Site

    And the following CreateNodeAggregateWithNode commands are executed:
      | nodeAggregateId | parentNodeAggregateId | nodeTypeName                   | initialPropertyValues                                                                                                                                                                                        | originDimensionSpacePoint | nodeName |
      | homepage        | sites                 | Neos.Demo:Document.Homepage    | {"title": "home"}                                                                                                                                                                                            | {"language": "en_US"}     | neosdemo |
      | some-page       | homepage              | Neos.Demo:Document.Page        | {"title": "some page"}                                                                                                                                                                                       | {"language": "en_US"}     |          |
      | blog            | homepage              | Neos.Demo:Document.Blog        | {"title": "My blog", "uriPathSegment": "blog"}                                                                                                                                                                  | {"language": "en_US"}     |          |
      | post-a          | blog                  | Neos.Demo:Document.BlogPosting | {"title": "a", "uriPathSegment": "my-post", "abstract": "<p>This is <strong>my</strong> blog post</p>", "authorName": "Marc Henry", "datePublished":{"__type": "DateTimeImmutable", "value": "2025-04-03"} } | {"language": "en_US"}     |          |
      | post-b          | blog                  | Neos.Demo:Document.BlogPosting | {"title": "b"}                                                                                                                                                                                               | {"language": "en_US"}     |          |

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
      | Key                       | Value                  |
      | nodeAggregateId           | "blog"                 |
      | originDimensionSpacePoint | {"language": "de"}     |
      | propertyValues            | {"title": "Mein Blog"} |

    And the command SetNodeProperties is executed with payload:
      | Key                       | Value               |
      | nodeAggregateId           | "post-a"            |
      | originDimensionSpacePoint | {"language": "de"}  |
      | propertyValues            | {"title": "a (de)"} |

  Scenario: GetPostListing for post in english
    When I issue the following query to "http://127.0.0.1:8081/get-blog-listing":
      | Key      | Value   |
      | blogId   | "blog"  |
      | language | "en_US" |
    Then I expect the following query response:
      """json
      {
          "success": {
              "title": "My blog",
              "postings": [
                  {
                     "id": "post-a",
                     "title": "a"
                  },
                  {
                     "id": "post-b",
                     "title": "b"
                  }
              ]
          }
      }
      """

  Scenario: GetPostListing for post in german
    When I issue the following query to "http://127.0.0.1:8081/get-blog-listing":
      | Key      | Value   |
      | blogId   | "blog"  |
      | language | "de" |
    Then I expect the following query response:
      """json
      {
          "success": {
              "title": "Mein Blog",
              "postings": [
                  {
                     "id": "post-a",
                     "title": "a (de)"
                  }
              ]
          }
      }
      """
