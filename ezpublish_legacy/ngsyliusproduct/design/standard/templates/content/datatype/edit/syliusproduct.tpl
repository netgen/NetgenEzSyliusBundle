{default $attribute_base='ContentObjectAttribute'}

{def $taxes = fetch('sylius', 'tax_categories')}
{def $products = fetch('sylius', 'products')}

{if $attribute.has_content}
    <input type="hidden"
           name="{$attribute_base}_data_sylius_id_{$attribute.id}"
            value="{$attribute.content.sylius_id}" />
{/if}

<table class="list" width="100%" cellspacing="0" cellpadding="0" border="0">
    {* disabled for now - no need for this in current use-case
    <tbody id="existing_product-{$attribute_id}">
    <tr>
        <td>
            <label>{'Use existing product'|i18n( 'design/standard/content/datatype/syliusproduct' )}:</label>
            <select name="{$attribute_base}_data_product_{$attribute.id}"
                    class="product-select"
                    id="{$attribute_base}_data_product_{$attribute.id}">
                <option value="0" disabled="disabled" selected>{'Select product'|i18n( 'design/standard/content/datatype/syliusproduct' )}</option>
                {foreach $products as $key => $value}
                    <option value="{$key}" {if $attribute.content.sylius_id|eq($key)}selected="selected"{/if}>
                        {$value}
                    </option>>
                {/foreach}
            </select>
        </td>
    </tr>
    <tr>
        <td>
            <a class="new-product" id="new_product-{$attribute.id}">{'Create new product'|i18n( 'design/standard/content/datatype/syliusproduct' )}</a>
        </td>
    </tr>
    </tbody>
    *}
    <tr>
        <th>
            {'Sylius id'|i18n( 'design/standard/content/datatype/syliusproduct' )}:
            {if $attribute.has_content}
                {$attribute.content.sylius_id}
            {else}
                {'This object is not linked to any Sylius products'|i18n( 'design/standard/content/datatype/syliusproduct' )}.
            {/if}
            {*
            {'Remove'|i18n( 'design/standard/content/datatype/syliusproduct' )}
            <input name="{$attribute_base}_data_unlink_{$attribute.id}"
                   class="checkbox_unlink"
                   id="checkbox_unlink-{$attribute.id}"
                   type="checkbox"
                    {if $attribute.has_content|eq(false())}disabled{/if} />
            *}
        </th>
    </tr>
    <tbody id="main_data-{$attribute.id}">
    <tr>
        <td>
            <label for="input_name-{$attribute.id}">{'Name'|i18n( 'design/standard/content/datatype/syliusproduct' )}:</label>
            <input name="{$attribute_base}_data_string_{$attribute.id}"
                   class="input_name"
                   id="input_name-{$attribute.id}"
                   type="text"
                   value="{$attribute.content.name|wash()}" />

            <input name="{$attribute_base}_data_ez_name_{$attribute.id}"
                   class="checkbox_name"
                   id="checkbox_name-{$attribute.id}"
                   type="checkbox"/>{'Use eZ data'|i18n( 'design/standard/content/datatype/syliusproduct' )}
        </td>
    </tr>
    <tr>
        <td>
            <label for="input_desc-{$attribute.id}">{'Description'|i18n( 'design/standard/content/datatype/syliusproduct' )}:</label>
            <textarea name="{$attribute_base}_data_desc_{$attribute.id}"
                   class="input_desc"
                   id="input_desc-{$attribute.id}"
                   style="width: 33%;"
                   >{$attribute.content.description|wash()}</textarea>

            <input name="{$attribute_base}_data_ez_desc_{$attribute.id}"
                   class="checkbox_desc"
                   id="checkbox_desc-{$attribute.id}"
                   type="checkbox"/>{'Use eZ data'|i18n( 'design/standard/content/datatype/syliusproduct' )}
        </td>
    </tr>
    <tr>
        <td width="10%">
            <label for="{$attribute_base}_data_integer_{$attribute.id}">{'Price'|i18n( 'design/standard/content/datatype/syliusproduct' )}:</label>
            <input name="{$attribute_base}_data_integer_{$attribute.id}"
                   id="{$attribute_base}_data_integer_{$attribute.id}"
                   type="integer"
                   value="{$attribute.content.price|wash()}"/>
            <label for="{$attribute_base}_data_tax_category_{$attribute.id}">{'Tax category'|i18n( 'design/standard/content/datatype/syliusproduct' )}:</label>
            <select name="{$attribute_base}_data_tax_category_{$attribute.id}"
                    class="tax-select"
                    id="{$attribute_base}_data_tax_category_{$attribute.id}">
                <option value="0">{'No tax'|i18n( 'design/standard/content/datatype/syliusproduct' )}</option>
                {foreach $taxes as $tax}
                    <option value="{$tax}" {if $attribute.content.tax_category|eq($tax)}selected="selected"{/if}>
                        {$tax}
                    </option>>
                {/foreach}
            </select>
        </td>
    </tr>
    <tr>
        <td>
            <label>{'Available on'|i18n( 'design/standard/content/datatype/syliusproduct' )}:</label>
            {'Date'|i18n( 'design/standard/content/datatype/syliusproduct' )}:
            <input name="{$attribute_base}_data_available_d_{$attribute.id}"
                   id="{$attribute_base}_data_available_d_{$attribute.id}"
                   type="text"
                   placeholder="dd"
                   size=2 />
            <input name="{$attribute_base}_data_available_m_{$attribute.id}"
                   id="{$attribute_base}_data_available_m_{$attribute.id}"
                   type="text"
                   placeholder="mm"
                   size=2 />
            <input name="{$attribute_base}_data_available_y_{$attribute.id}"
                   id="{$attribute_base}_data_available_y_{$attribute.id}"
                   type="text"
                   placeholder="yyyy"
                   size=2 />
            {'Time'|i18n( 'design/standard/content/datatype/syliusproduct' )}:
            <input name="{$attribute_base}_data_available_h_{$attribute.id}"
                   id="{$attribute_base}_data_available_h_{$attribute.id}"
                   type="text"
                   placeholder="hh"
                   size=2 />h
            <input name="{$attribute_base}_data_available_min_{$attribute.id}"
                   id="{$attribute_base}_data_available_min_{$attribute.id}"
                   type="text"
                   placeholder="mm"
                   size=2 />min
            <input name="{$attribute_base}_data_ez_cur_date_{$attribute.id}"
                   class="checkbox_date"
                   id="checkbox_date-{$attribute.id}"
                   type="checkbox"/>({'Use current time'|i18n( 'design/standard/content/datatype/syliusproduct' )})
        </td>
    </tr>
    {*<tr>
        <td>
            <a class="additional-info" id="additionalinfo-{$attribute.id}">Additional info</a>
        </td>
    </tr>*}
    </tbody>
    <tbody id="additional_data-{$attribute.id}" >
    <tr>
        <td>
            <label for="{$attribute_base}_data_sku_{$attribute.id}">{'SKU'|i18n( 'design/standard/content/datatype/syliusproduct' )}:</label>
            <input name="{$attribute_base}_data_sku_{$attribute.id}"
                   id="{$attribute_base}_data_sku_{$attribute.id}"
                   type="text"
                   value="{$attribute.content.sku|wash()}"/>
        </td>
    </tr>
    <tr>
        <td>
            <label for="input_weight-{$attribute.id}">{'Weight'|i18n( 'design/standard/content/datatype/syliusproduct' )}:</label>
            <input name="{$attribute_base}_data_weight_{$attribute.id}"
                   class="input_weight"
                   id="input_weight-{$attribute.id}"
                   type="text"
                   value="{$attribute.content.weight}" />
        </td>
    </tr>
    <tr>
        <td>
            <label for="input_height-{$attribute.id}">{'Height'|i18n( 'design/standard/content/datatype/syliusproduct' )}:</label>
            <input name="{$attribute_base}_data_height_{$attribute.id}"
                   class="input_height"
                   id="input_height-{$attribute.id}"
                   type="text"
                   value="{$attribute.content.height}" />
        </td>
    </tr>
    <tr>
        <td>
            <label for="input_width-{$attribute.id}">{'Width'|i18n( 'design/standard/content/datatype/syliusproduct' )}:</label>
            <input name="{$attribute_base}_data_width_{$attribute.id}"
                   class="input_width"
                   id="input_width-{$attribute.id}"
                   type="text"
                   value="{$attribute.content.width}" />
        </td>
    </tr>
    </tbody>

</table>

{literal}
    <script>

        $(function() {
            var base = {/literal}"{$attribute_base}"{literal};
            var attribute_id = {/literal}"{$attribute.id}"{literal};
            var datetime = {/literal}"{$attribute.content.available_on}"{literal};

            //$("#main_data-"+attribute_id+" input").prop("disabled", true);
            //$("#input_desc-"+attribute_id).prop("disabled", true);

            if (datetime) {
                datetime = datetime.split(' ');
                var date = datetime[0];
                var time = datetime[1];
                date = date.split('-');
                time = time.split(':');
                $("#" + base + "_data_available_d_" + attribute_id).val(date[0]);
                $("#" + base + "_data_available_m_" + attribute_id).val(date[1]);
                $("#" + base + "_data_available_y_" + attribute_id).val(date[2]);
                $("#" + base + "_data_available_h_" + attribute_id).val(time[0]);
                $("#" + base + "_data_available_min_" + attribute_id).val(time[1]);
            }

            $(".checkbox_name").click(function(){
                var attr_id = $(this).attr('id');
                attr_id = attr_id.split('-')[1];

                if (this.checked) {
                    $("#input_name-" + attr_id).attr('disabled', true).hide();
                }else {
                    $("#input_name-" + attr_id).attr('disabled', false).show();
                }
            });

            $(".checkbox_desc").click(function(){
                var attr_id = $(this).attr('id');
                attr_id = attr_id.split('-')[1];

                if (this.checked) {
                    $("#input_desc-" + attr_id).attr('disabled', true).hide();
                }else {
                    $("#input_desc-" + attr_id).attr('disabled', false).show();
                }
            });

            $("#checkbox_date-"+attribute_id).click(function(){
                var attr_id = $(this).attr('id');
                attr_id = attr_id.split('-')[1];

                if (this.checked) {
                    var current_datetime = new Date();
                    var day = current_datetime.getDate();
                    var month = current_datetime.getMonth()+1;
                    var year = current_datetime.getFullYear();
                    var hour = current_datetime.getHours();
                    var min = current_datetime.getMinutes();

                    $("#" + base + "_data_available_d_" + attribute_id).val(day).attr('readonly', true);
                    $("#" + base + "_data_available_m_" + attribute_id).val(month).attr('readonly', true);
                    $("#" + base + "_data_available_y_" + attribute_id).val(year).attr('readonly', true);
                    $("#" + base + "_data_available_h_" + attribute_id).val(hour).attr('readonly', true);
                    $("#" + base + "_data_available_min_" + attribute_id).val(min).attr('readonly', true);
                }else {
                    $("#" + base + "_data_available_d_" + attribute_id).attr('readonly', false);
                    $("#" + base + "_data_available_m_" + attribute_id).attr('readonly', false);
                    $("#" + base + "_data_available_y_" + attribute_id).attr('readonly', false);
                    $("#" + base + "_data_available_h_" + attribute_id).attr('readonly', false);
                    $("#" + base + "_data_available_min_" + attribute_id).attr('readonly', false);
                }
            });

            $(".checkbox_unlink").click(function() {
                var attr_id = $(this).attr('id');
                attr_id = attr_id.split('-')[1];

                if (this.checked) {
                    $("#main_data-"+attribute_id+" input").prop("disabled", true);
                    $("#main_data-"+attribute_id+" select").prop("disabled", true);
                    $("#additional_data-"+attribute_id+" input").prop("disabled", true);
                    $("#input_desc-"+attribute_id).prop("disabled", true);
                }
                else{
                    $("#main_data-"+attribute_id+" input").prop("disabled", false);
                    $("#main_data-"+attribute_id+" select").prop("disabled", false);
                    $("#additional_data-"+attribute_id+" input").prop("disabled", false);
                    $("#input_desc-"+attribute_id).prop("disabled", false);
                }
            });

            $('#additionalinfo-'+attribute_id).click(function(event) {
                event.preventDefault();
                var id = $(this).attr('id');
                id = id.split('-')[1];
                $('#additional_data-'+id).toggle();
            });

            /*$('.new-product').click(function(event) {
                event.preventDefault();
                var id = $(this).attr('id');
                id = id.split('-')[1];
                $('#'+base+'_data_product_'+attribute_id+' option:eq(0)').prop('selected', true);
                $("#main_data-"+attribute_id+" input").prop("disabled", false);
                $("#input_desc-"+attribute_id).prop("disabled", false);
                $('#main_data-'+id).show();
                $("#main_data-"+attribute_id+" input").prop("disabled", false);
                $("#main_data-"+attribute_id+" select").prop("disabled", false);
                $("#additional_data-"+attribute_id+" input").prop("disabled", false);
                $("#input_desc-"+attribute_id).prop("disabled", false);
            });

            $(".product-select").change(function(event){
                $("#main_data-"+attribute_id+" input").prop("disabled", true);
                $("#main_data-"+attribute_id+" select").prop("disabled", true);
                $("#additional_data-"+attribute_id+" input").prop("disabled", true);
                $("#input_desc-"+attribute_id).prop("disabled", true);
                //$('#main_data-'+id).toggle();
            });*/
        })
    </script>
{/literal}