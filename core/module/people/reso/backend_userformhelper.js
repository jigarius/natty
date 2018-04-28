var UserFormHelper = {};

UserFormHelper.$form = null;

UserFormHelper.init = function() {
    
    var $form = jQuery('#people-user-edit-form');
    UserFormHelper.$form = $form;
    
    $form.find('.form-item-password input')
            .on('input', UserFormHelper.handlePasswordChange)
            .trigger('input');
    
    
};

UserFormHelper.handlePasswordChange = function() {
    
    var $fw_pword = jQuery(this);
    var $fi_pword_conf = UserFormHelper.$form.find('.form-item-password_conf');
    
    if ( $fw_pword.val().length > 0 ) {
        $fi_pword_conf.show();
    }
    else {
        $fi_pword_conf.hide();
    }
    
};

jQuery(document).ready(function() {
    UserFormHelper.init();
});