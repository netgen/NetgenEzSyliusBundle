<p>{"Name"|i18n( 'extension/ngsyliusproduct/datatypes' )}: {$attribute.content.name|wash}</p>
<p>{"Description"|i18n( 'extension/ngsyliusproduct/datatypes' )}: {$attribute.content.description|wash}</p>
<p>{"Price"|i18n( 'extension/ngsyliusproduct/datatypes' )}: {$attribute.content.price|wash}</p>
<p>{"Tax category"|i18n( 'extension/ngsyliusproduct/datatypes' )}: {if $attribute.content.tax_category}{$attribute.content.tax_category|wash}{else}No tax applied{/if}</p>
<p>{"Sylius product id"|i18n( 'extension/ngsyliusproduct/datatypes' )}: {$attribute.content.product_id|wash}</p>
<p>{"Available on"|i18n( 'extension/ngsyliusproduct/datatypes' )}: {$attribute.content.available_on|wash}</p>
<p>{"SKU"|i18n( 'extension/ngsyliusproduct/datatypes' )}: {$attribute.content.sku|wash}</p>
