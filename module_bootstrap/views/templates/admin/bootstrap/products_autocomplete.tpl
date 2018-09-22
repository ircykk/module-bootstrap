<input type="text" name="products" id="products" class="ac_input_2 ac_input" autocomplete="off">
<br>
<div id="selected-items">
    {if !empty($products)}
        {foreach from=$products item=product}
            <div>
                <input type="hidden" name="products[]" value="{$product.id_product|intval}">
                <button class="btn btn-default"><i class="icon-remove text-danger"></i></button>&nbsp;&nbsp;
                <span class="name">
                    {$product.name|escape:'htmlall'}
                    {if $product.reference|escape:'htmlall'}(ref: {$product.reference|escape:'htmlall'}){/if}
                </span>
            </div>
        {/foreach}
    {/if}
</div>
<hr>

<script>
// Autocomplete
$(document).ready(function () {
    $('.ac_input_2').autocomplete(
        'ajax_products_list.php?exclude_packs=0&excludeVirtuals=0&excludeIds=-1', { // @todo excluded
            minChars: 2,
            max: 50
        }
    ).result(function (event, data, selected){
        if (data[1] !== undefined) {
            var html ='';
            html += '<div>';
            html += '<input type="hidden" name="products[]" value="'+data[1]+'">';
            html += '<button class="btn btn-default"><i class="icon-remove text-danger"></i></button>&nbsp;&nbsp;';
            html += '<span class="name">'+data[0]+'</span>';
            html += '</div>';

            $('#selected-items').append(html);
            $(this).val('');
        }
    });

    // Delete item
    $(document).on('click', '#selected-items button', function(e) {
        e.preventDefault();
        $(this).parent().remove();
    });
})
</script>