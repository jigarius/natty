var ModEasex = ModEasex || {};

ModEasex.$drawer = null;
ModEasex.$toolbar = null;

ModEasex.initDashmenu = function() {
    
    // Build easex menu
    var $drawer = jQuery('#easex-drawer');
    ModEasex.$drawer = $drawer;
    
    var $dashmenu = $drawer.find('.easex-dashmenu');
    
    // Init dashmenu items
    var $items = $dashmenu.find('li');
    $items.each(function() {
        var $item = jQuery(this);
        var $submenu = $item.find('> ul');
        if ( 1 == $submenu.length ) {
            $item.addClass('expandable');
        }
    });
    
    // Activate triggers
    $dashmenu.find('a').click(ModEasex.handleDashmenuClick);
    jQuery('a.dashmenu-trigger').click(function() {
        ModEasex.toggleDrawer();
        return !1;
    });
    
    // Drawer collapses on blur
    jQuery(document).click(function(e) {
        var $target = jQuery(e.target);
        if ( 1 !== $target.closest('#dashmenu-drawer').length ) {
            if ( $dashmenu.is(':visible') )
                ModEasex.toggleDrawer();
        }
    });
    
};

ModEasex.initToolbar = function() {
    
    var $toolbar = jQuery('#easex-toolbar');
    var $body = jQuery(document.body);
    
    var pT = parseInt($body.css('padding-top'));
    pT += $toolbar.outerHeight();
    $body.css('padding-top', pT);
    
};

ModEasex.init = function() {
    
    // Toolbar enabled?
    if ( !ModEasex.showToolbar )
        return;
    
    // Retrieve menu content
    jQuery.ajax({
        type: "POST",
        url: Natty.url('easex/dashmenu'),
        data: {
            referer: Natty.command,
        },
        success: function(r) {
            
            if ( 0 == r.length )
                return;
            
            var $response = jQuery(r);
            jQuery(document.body).append($response);
            
            ModEasex.initDashmenu();
            ModEasex.initToolbar();
            
        },
    });
    
};

ModEasex.toggleDrawer = function() {
    
    var $drawer = ModEasex.$drawer;
    if ( $drawer.hasClass('expanded') ) {
        $drawer.removeClass('expanded');
        $drawer.find('li').show().removeClass('expanded');
    }
    else {
        $drawer.addClass('expanded');
    }
    
};

ModEasex.handleDashmenuClick = function() {
    
    var $link = jQuery(this);
    var $leaf = $link.closest('li');
    var $siblings = $leaf.siblings();
    var $submenu = $leaf.find('> ul');
    
    // No sub-menu? Go to desired page
    if ( 1 != $submenu.length ) {
        return true;
    }
    // Show sub-menu
    else {
        if ( $leaf.hasClass('expanded') ) {
            $leaf.removeClass('expanded');
            // Show hidden children
            $leaf.find('li').removeClass('expanded').show();
            $siblings.show();
        }
        else {
            $leaf.addClass('expanded');
            $siblings.hide();
        }
        return !1;
    }
    
};

jQuery(document).ready(ModEasex.init);