Natty.ModCatalog = Natty.ModCatalog || {};

Natty.ModCatalog.initProductForm = function() {
    
    // Read form
    var $form = jQuery('#catalog-product-form');
    if ( 1 !== $form.length )
        return;
    
    // Get categories field
    var $fi_cids = $form.find('.form-item-categoryIds');
    var $fw_cid = $form.find('.form-item-cid select');
    
    $fi_cids.find('input').change(function() {
        
        var cid = $fw_cid.val();
        if ( !this.checked && this.getAttribute('value') == cid ) {
            $fw_cid.val('');
        }
        
    });
    
    $fw_cid.change(function() {
        
        var cid = jQuery(this).val();
        var $inputs = $fi_cids.find('input[value="' + cid + '"]').attr('checked', 'checked');
        
    }).change();
    
};

jQuery(document).ready(function() {
    
    Natty.ModCatalog.initProductForm();
    
});