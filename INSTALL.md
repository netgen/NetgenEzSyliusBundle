Add to parameters.yml:
    netgen_ez_sylius_content_type_identifiers: [ contentTypeIdentifier1, contentTypeIdentifier2 ]
with contentTypeId being the content type identifier of the class with sylius_product field type

    netgen_ez_sylius.field_definition_identifier_mappings:
        [name of the class]:
            name: [field identifier which will be maped to name]
            description: [field identifier which will be maped to description]
        product:
            name: name
            description: description


Enable twig template in your override yml (ezpublish.yml):
your_group:
    field_templates:
        - {template: "NetgenEzSyliusBundle:fields:syliusproduct.html.twig"}

SyliusProduct data type comes with event that updates slug in sylius database when the object has been moved or swaped.
To enable this feature, you have to do the following
1) In your extension settings, you have to edit (or add if it isn't already there) workflow.ini.append.php:
- in it, enable content move trigger:
[OperationSettings]
AvailableOperationList[]=content_move

2) Set up a workflow for content move AFTER trigger. In that workflow, choose "Update Sylius slug" event.
3) Set up a workflow for content swap AFTER trigger. In that workflow, choose "Update Sylius slug" event.

That's it! The event will make sure the slug is properly updated after certain actions in the eZ administration.
