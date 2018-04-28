var ModCms = ModCms || {};

ModCms.init = function() {
    
    var $menu_coll = jQuery('.n-menu');
    $menu_coll.each(function() {
        
        var $menu = jQuery(this);
        
        // Already initialized?
        if ( $menu.data('natty.menu') )
            return;
        $menu.data('natty.menu', {
            init: 1
        });
        
        $menu.find('a').each(function() {

            var $a = jQuery(this);
            var $li = $a.parent();

            if ( 1 !== $li.prev().length )
                $li.addClass('first');
            if ( 1 !== $li.next().length )
                $li.addClass('last');
            
        });
        
        $menu.find('li')
                .on('focus mouseenter', function() {
                    jQuery(this).addClass('focused');
                }).on('blur mouseleave', function() {
                    jQuery(this).removeClass('focused');
                });
        
    });
    
};