Feature: NodeCreation

  Background:
    Given using no content dimensions
    And using the following node types:
    """yaml
    'Neos.ContentRepository:Root': {}
    'Neos.Neos:Sites':
      superTypes:
        'Neos.ContentRepository:Root': true
    'Neos.Neos:Document':
      properties:
        title:
          type: string
        uriPathSegment:
          type: string
        hiddenInMenu:
          type: bool
    'Neos.Neos:Site':
      superTypes:
        'Neos.Neos:Document': true
    'Neos.Neos:Content':
      properties:
        title:
          type: string
    'Neos.Neos:ContentCollection': {}

    'Neos.Demo:Document.Homepage':
      superTypes:
        'Neos.Neos:Site': true

    'Neos.Demo:Document.Blog':
      superTypes:
        'Neos.Neos:Document': true
    'Neos.Demo:Document.BlogCategory':
      superTypes:
        'Neos.Neos:Document': true
    'Neos.Demo:Document.BlogPosting':
      superTypes:
        'Neos.Neos:Document': true
      properties:
        hiddenInMenu:
          defaultValue: true
        abstract:
          type: string
        datePublished:
          scope: nodeAggregate
          type: DateTime
        authorName:
          type: string
      references:
        categories:
          constraints:
            nodeTypes:
              '*': false
              'Neos.Demo:Document.BlogCategory': true
    """

    And using identifier "default", I define a content repository
    And I am in content repository "default"

    And the command CreateRootWorkspace is executed with payload:
      | Key                | Value           |
      | workspaceName      | "live"          |
      | newContentStreamId | "cs-identifier" |
    And I am in workspace "live" and dimension space point {}
    And the command CreateRootNodeAggregateWithNode is executed with payload:
      | Key             | Value             |
      | nodeAggregateId | "sites"           |
      | nodeTypeName    | "Neos.Neos:Sites" |

    And the following CreateNodeAggregateWithNode commands are executed:
      | nodeAggregateId    | parentNodeAggregateId | nodeTypeName                    | initialPropertyValues | originDimensionSpacePoint | nodeName |
      | demo-neos-homepage | sites                 | Neos.Demo:Document.Homepage     | {"title": "home"}     | {}                        | site-a   |
      | demo-neos-blog     | demo-neos-homepage    | Neos.Demo:Document.Blog         | {"title": "blog"}     | {}                        |          |
      | demo-neos-barcamp  | demo-neos-blog        | Neos.Demo:Document.BlogCategory | {"title": "Barcamp"}  | {}                        |          |
      | demo-neos-neos-9   | demo-neos-blog        | Neos.Demo:Document.BlogCategory | {"title": "Neos 9"}   | {}                        |          |

  Scenario: Create Node

    When I import the contents into blog "demo-neos-blog"
      | id       | language | headline                     | abstract                                                            | datePublished             | author         | about     |
      | 20241028 |          | "Neos Barcamp 2024: A Recap" | "On 25 October 2024, the first Neos Barcamp took place in Dresden." | 2024-10-28T12:00:00+00:00 | "Marika Hauke" | "Barcamp" |

    Then I expect the node aggregate "demo-neos-20241028" to exist
    And I expect this node aggregate to be of type "Neos.Demo:Document.BlogPosting"
    And I expect this node aggregate to occupy dimension space points [{}]
    And I expect this node aggregate to cover dimension space points [{}]
    And I expect this node aggregate to have the parent node aggregates ["demo-neos-blog"]

  Scenario: Node with properties

    When I import the contents into blog "demo-neos-blog"
      | id       | language | headline                     | abstract                                                            | datePublished             | author         | about     |
      | 20241028 |          | "Neos Barcamp 2024: A Recap" | "On 25 October 2024, the first Neos Barcamp took place in Dresden." | 2024-10-28T12:00:00+00:00 | "Marika Hauke" | "Barcamp" |

    When I am in workspace "live" and dimension space point {}
    Then I expect node aggregate identifier "demo-neos-20241028" to lead to node cs-identifier;demo-neos-20241028;{}

    And I expect this node to have the following serialized properties:
      | Key           | Type              | Value                                                               |
      | title         | string            | "Neos Barcamp 2024: A Recap"                                        |
      | abstract      | string            | "On 25 October 2024, the first Neos Barcamp took place in Dresden." |
      | datePublished | DateTimeImmutable | "2024-10-28T12:00:00+00:00"                                         |
      | authorName    | string            | "Marika Hauke"                                                      |

    And I expect this node to have the following properties:
      | Key           | Value                                                               |
      | title         | "Neos Barcamp 2024: A Recap"                                        |
      | abstract      | "On 25 October 2024, the first Neos Barcamp took place in Dresden." |
      | datePublished | Date:2024-10-28T12:00:00+00:00                                      |
      | authorName    | "Marika Hauke"                                                      |
