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

    And A site exists for node name "neosdemo" and domain "http://localhost" and package Neos.Demo

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
              "blogUri": "http://127.0.0.1:8081/en/blog.html",
              "postings": [
                  {
                     "id": "post-a",
                     "title": "a",
                     "api": "/get-blog-details?node=%7B%22contentRepositoryId%22%3A%22default%22%2C%22workspaceName%22%3A%22live%22%2C%22dimensionSpacePoint%22%3A%7B%22language%22%3A%22en_US%22%7D%2C%22aggregateId%22%3A%22post-a%22%7D"
                  },
                  {
                     "id": "post-b",
                     "title": "b",
                     "api": "/get-blog-details?node=%7B%22contentRepositoryId%22%3A%22default%22%2C%22workspaceName%22%3A%22live%22%2C%22dimensionSpacePoint%22%3A%7B%22language%22%3A%22en_US%22%7D%2C%22aggregateId%22%3A%22post-b%22%7D"
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
              "blogUri": "http://127.0.0.1:8081/de/blog.html",
              "postings": [
                  {
                     "id": "post-a",
                     "title": "a (de)",
                     "api": "/get-blog-details?node=%7B%22contentRepositoryId%22%3A%22default%22%2C%22workspaceName%22%3A%22live%22%2C%22dimensionSpacePoint%22%3A%7B%22language%22%3A%22de%22%7D%2C%22aggregateId%22%3A%22post-a%22%7D"
                  }
              ]
          }
      }
      """
