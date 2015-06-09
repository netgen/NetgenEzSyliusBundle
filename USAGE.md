# USAGE

# Legacy datatype

After installing and properly activating the extension, you can add the `Sylius product` datatype to your content type.
Next step would be to create `ngsyliusproduct.ini.append.php` where you can define mapping of eZ Publish content fields to Sylius product attributes. For now, only name and description are supported.

Example `ngsyliusproduct.ini.append.php` would look like this:

```
[product]
Name=title
Description=full_description
```

meaning that the fields with identifiers `title` and `description` will be used as product's name and description.

When creating/editing content which contains Sylius product datatype, several fields are available:
* name (with option to map from eZ Publish content)
* description (with option to map from eZ Publish content)
* price
* tax category (select box with tax categories configured in Sylius administration)
* available on date
* SKU (product number)
* weight
* height
* width
* depth

Publishing object in eZ Publish legacy administration will create new Sylius product if there is no connection yet, or update the existing Sylius product that is connected to the eZ object.

# Field type
The goal was to provide full Sylius product as value of the field type, but also to provide some kind of wrapper around it when creating or updating eZ content.

Because of that, there is special `CreateValue` PHP class that is used only for creating new content with Sylius product field type.
`CreateValue` holds associative array with values which will be used to create Sylius product (same fields as in legacy datatype).

## Creating content
Example:

```
$contentType = $contentTypeService->loadContentTypeByIdentifier( 'product' );
$contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
$contentCreateStruct->setField( 'name', 'Example product' );

$createArray = array(
    "name" => 'Example product',
    "description" => "Example description",
    "price" => 10
);
$syliusCreateValue = new CreateValue( $createArray );

$contentCreateStruct->setField( 'sylius_product', $syliusCreateValue );

$locationCreateStruct = $locationService->newLocationCreateStruct( 2 );
$draft = $contentService->createContent( $contentCreateStruct, array( $locationCreateStruct ) );

$content = $contentService->publishVersion( $draft->versionInfo );
```

## Translating content
When translating the content, you are free to change the value directly on the value received from the field. The field type will take care to save new value with appropriate locale:

```
$contentUpdateStruct = $contentService->newContentUpdateStruct();
$contentUpdateStruct->initialLanguageCode = 'cro-HR'; // set language for new version
$contentUpdateStruct->setField( 'name', 'Naziv proizvoda' );

$syliusValue = $content->getFieldValue( 'sylius_product' );
$syliusValue->product->setName( 'Hrvatski naziv proizvoda' );
$syliusValue->product->setDescription ( 'Opis na hrvatskom' );

$contentUpdateStruct->setField( 'sylius_product', $syliusValue );

$contentDraft = $contentService->updateContent( $contentDraft->versionInfo, $contentUpdateStruct );
$translatedContent = $contentService->publishVersion( $contentDraft->versionInfo );
```

## Viewing content
Content field template has also been provided, so it is possible to render the Sylius product field with `ez_render_field` twig function. If there are no additional parameters provided, all values that have been set will be shown, including `add to cart` form.

However, it is possible to specify which fields to show by adding `show` parameter to `ez_render_field`.
Example:

```
{{ ez_render_field(
    content,
    'sylius_product',
    {
        'parameters':
        {
            'show' : [ 'price', 'form' ]
        }
     }
) }}
```

This would only show product price and `add to cart` form.
