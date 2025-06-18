Feature: NodeCreation

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
      | nodeAggregateId    | parentNodeAggregateId | nodeTypeName                    | initialPropertyValues | originDimensionSpacePoint | nodeName |
      | demo-neos-homepage | sites                 | Neos.Demo:Document.Homepage     | {"title": "home"}     | {"language": "en_US"}     | site-a   |
      | demo-neos-blog     | demo-neos-homepage    | Neos.Demo:Document.Blog         | {"title": "blog"}     | {"language": "en_US"}     |          |
      | demo-neos-barcamp  | demo-neos-blog        | Neos.Demo:Document.BlogCategory | {"title": "Barcamp"}  | {"language": "en_US"}     |          |
      | demo-neos-neos-9   | demo-neos-blog        | Neos.Demo:Document.BlogCategory | {"title": "Neos 9"}   | {"language": "en_US"}     |          |

    And the command CreateNodeVariant is executed with payload:
      | Key             | Value                |
      | nodeAggregateId | "demo-neos-homepage" |
      | sourceOrigin    | {"language":"en_US"} |
      | targetOrigin    | {"language":"de"}    |

    And the command CreateNodeVariant is executed with payload:
      | Key             | Value                |
      | nodeAggregateId | "demo-neos-blog"     |
      | sourceOrigin    | {"language":"en_US"} |
      | targetOrigin    | {"language":"de"}    |

    And the command CreateNodeVariant is executed with payload:
      | Key             | Value                |
      | nodeAggregateId | "demo-neos-barcamp"  |
      | sourceOrigin    | {"language":"en_US"} |
      | targetOrigin    | {"language":"de"}    |

    And the command CreateNodeVariant is executed with payload:
      | Key             | Value                |
      | nodeAggregateId | "demo-neos-neos-9"   |
      | sourceOrigin    | {"language":"en_US"} |
      | targetOrigin    | {"language":"de"}    |

  Scenario: Create single Node

    When I import the contents into blog "demo-neos-blog"
      | id       | language | headline                     | abstract                                                            | datePublished             | author         | about     |
      | 20241028 | en_US    | "Neos Barcamp 2024: A Recap" | "On 25 October 2024, the first Neos Barcamp took place in Dresden." | 2024-10-28T12:00:00+00:00 | "Marika Hauke" | "Barcamp" |

    Then I expect the node aggregate "demo-neos-20241028" to exist
    And I expect this node aggregate to be of type "Neos.Demo:Document.BlogPosting"
    And I expect this node aggregate to occupy dimension space points [{"language": "en_US"}]
    And I expect this node aggregate to cover dimension space points [{"language": "en_US"}, {"language": "en_UK"}]
    And I expect this node aggregate to have the parent node aggregates ["demo-neos-blog"]

  Scenario: Create Node with properties

    When I import the contents into blog "demo-neos-blog"
      | id       | language | headline                     | abstract                                                            | datePublished             | author         | about     |
      | 20241028 | en_US    | "Neos Barcamp 2024: A Recap" | "On 25 October 2024, the first Neos Barcamp took place in Dresden." | 2024-10-28T12:00:00+00:00 | "Marika Hauke" | "Barcamp" |

    Then I expect the node aggregate "demo-neos-20241028" to exist
    And I expect this node aggregate to be of type "Neos.Demo:Document.BlogPosting"
    And I expect this node aggregate to occupy dimension space points [{"language": "en_US"}]
    And I expect this node aggregate to cover dimension space points [{"language": "en_US"}, {"language": "en_UK"}]
    And I expect this node aggregate to have the parent node aggregates ["demo-neos-blog"]

    When I am in workspace "live" and dimension space point {"language": "en_US"}
    Then I expect node aggregate identifier "demo-neos-20241028" to lead to node cs-identifier;demo-neos-20241028;{"language": "en_US"}

    And I expect this node to have the following properties:
      | Key           | Value                                                               |
      | title         | "Neos Barcamp 2024: A Recap"                                        |
      | abstract      | "On 25 October 2024, the first Neos Barcamp took place in Dresden." |
      | datePublished | Date:2024-10-28T12:00:00+00:00                                      |
      | authorName    | "Marika Hauke"                                                      |
