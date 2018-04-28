Natty.Prototype.Sortable = function($el, options) {
    
    this.$context = null;
    
    this.indentWidth = false;
    
    this.maxNestingLevel = 5;
    
    this.options = {};
    
    this.constructor = function($el, options) {
        
        this.$context = $el;
        this.options = options;
        
        this._init();
        
        $el.data('nattySortable', this);
        
        return this;
        
    };
    
    this._init = function() {
        
        var $el = this.$context;
        $el.addClass('n-table-sortable');
        
        var $tbody = $el.find('tbody');

        // Determine indent width
        var $sample = jQuery('<tr><td><span class="n-indent"></span></td></tr>');
        $tbody.append($sample);
        this.indentWidth = $sample.find('.n-indent').width();
        $sample.remove();

        // Determine sortable items
        var $rows = $tbody.find('tr');
        if ( $rows.length <= 1 )
            return;
        
        // Add drag handles if not present
        $rows.each(function() {
            
            var $row = jQuery(this);
            
            if ( $row.hasClass('sortable-ignore') )
                return;
            
            var $handle = $row.find('.drag-handle');
            if ( 0 === $handle.length ) {
                $handle = jQuery('<span class="drag-handle fa fa-arrows"></span>');
                var $cell = $row.find('td:visible:first');
                // Do we have indents?
                var $indents = $cell.find('.n-indent');
                if ( $indents.length > 0 ) {
                    $indents.last().after($handle);
                }
                else {
                    $cell.prepend($handle);
                }
            }
            
        });

        // Initialize sortable
        $tbody.kendoSortable({
            autoScroll: true,
            container: $tbody,
            distance: 2,
            handler: '.drag-handle',
            holdToDrag: false,
            hint: function(element) {
                return false;
                var $tr = element.clone().addClass('n-draggable-hint');
                return $tr;
            },
            placeholder: function(element) {
                return element.clone().addClass('n-draggable-placeholder');
            }
        });
        
        // Add drag listener to maintain level
        var draggable = $tbody.data('kendoDraggable');
        draggable.bind('drag', this._handleItemDrag);
        draggable.bind('dragstart', this._handleItemDragStart);
        draggable.bind('dragend', this._handleItemDragEnd);
        
    };
    
    this._handleItemDragStart = function(e) {
        
        // Get the Sortable Object
        var $table = jQuery(e.currentTarget).closest('table');
        var nSortable = $table.data('nattySortable');
        
        // Get target row
        var $taRow = e.currentTarget;
        var $taCell = $taRow.find('.drag-handle').closest('td');
        
        // Get placeholder
        var $phRow = $table.find('.n-draggable-placeholder');
        
        // Detect next row
        var $nextRow = $taRow.next();
        if ( 1 == $nextRow.length ) {
            
            // If the item is a parent, it cannot be moved
            if ( $nextRow.find('input.prop-parentId').val() === $taRow.find('input.prop-id').val() ) {
                
                Natty.Func.notify({
                    content: 'Cannot move item with children.'
                });
                
                // Prevent movement
                e.preventDefault();
                $phRow.remove();
                $taRow.show();
                
            }
            
        }
        
    };
    
    this._handleItemDrag = function(e) {
        
        // Get the Sortable Object
        var $table = jQuery(e.currentTarget).closest('table');
        var nSortable = $table.data('nattySortable');
        
        // See if handling is in progress
        if ( nSortable._handlingDrag ) {
            return;
            clearTimeout(nSortable._handlingDragTimeout);
            var c = arguments.callee, t = this;
            nSortable.setTimeout(function() {
                c.call(t, e);
            }, 500);
            return;
        }
        nSortable._handlingDrag = true;
        
        // Get target row
        var $taRow = e.currentTarget;
        var $taCell = $taRow.find('.drag-handle').closest('td');
        
        // Get placeholder
        var $phRow = $table.find('.n-draggable-placeholder');
        var $phHandle = $phRow.find('.drag-handle');
        var $phCell = jQuery($phHandle.closest('td'));
        
        // Detect old level
        var phLevelOld = $phRow.find('input.prop-level').val();
        phLevelOld = parseInt(phLevelOld);
        
        // Maximum level allowed
        var phMinLevel = 0;
        var phMaxLevel = nSortable.maxNestingLevel;
        
        // If this is the first row, it cannot be indented
        var $prevRow = $phRow.prev();
        if ( 0 == $prevRow.length ) {
            phMaxLevel = 0;
        }
        else {
            
            var prevRowLevel = $prevRow.find('input.prop-level').val();
            prevRowLevel = parseInt(prevRowLevel);
            
            // If the previous row is the parent row, the current row cannot
            // be indented any further
            if ( $prevRow.find('input.prop-id').val() == $phRow.find('input.prop-parentId') ) {
                phMaxLevel = Math.min(prevRowLevel, phMaxLevel);
            }
            else {
                phMaxLevel = Math.min(prevRowLevel+1, phMaxLevel);
            }
            
        }
        
        // Cursor offset from left of the row
        var offsetX = e.pageX - $phRow.offset().left;
        
        // Detect new level
        var phLevelNew = Math.floor(offsetX / nSortable.indentWidth);
        phLevelNew = Math.min(phLevelNew, phMaxLevel);
        phLevelNew = Math.max(phLevelNew, phMinLevel);
//        console.log('Min: ' + phMinLevel, 'Max: ' + phMaxLevel, 'Old: ' + phLevelOld, 'New: ' + phLevelNew);

        // Change the number of placeholders
        var $phIndents = $phCell.find('.n-indent');
        var phLevelChange = phLevelNew - phLevelOld;
        if ( phLevelChange < 0 ) {
            phLevelChange = Math.abs(phLevelChange);
            $phIndents.slice(0, phLevelChange).remove();
        }
        else if ( phLevelChange > 0 ) {
            var mu = '<span class="n-indent"></span>'.repeat(phLevelChange);
            var $mu = jQuery(mu);
            $phCell.prepend($mu);
        }
        
        // Apply change of level and parentId
        // @todo Change the target row on drag stop
        if ( 0 !== phLevelChange ) {
            
            $phRow.find('.prop-level').val(phLevelNew);
            $taRow.find('.prop-level').val(phLevelNew);
            
            // Find the parent row
            var $paRow = $phRow;
            while ( 1 ) {
                
                $paRow = $paRow.prev();
                if ( 1 !== $paRow.length )
                    break;
                
                var paRowLevel = $paRow.find('input.prop-level').val();
                paRowLevel = parseInt(paRowLevel);
                
                if ( phLevelNew === paRowLevel+1 ) {
                    var parentId = $paRow.find('input.prop-id').val();
                    $phRow.find('input.prop-parentId').val(parentId);
                    $taRow.find('input.prop-parentId').val(parentId);
                    break;
                }
                
            }
            
            // Update indents in target row
            $taCell.find('.n-indent').remove();
            if ( phLevelNew > 0 ) {
                var mu = '<span class="n-indent"></span>'.repeat(phLevelNew);
                $taCell.prepend(mu);
            }
            
        }
        
        nSortable._handlingDrag = false;
        
    };
    
    this._handleItemDragEnd = function(e) {
        
        var $taRow = e.currentTarget;
        var $tbody = $taRow.parent();
        
        // Re-touch all items
        jQuery($tbody.find('> tr').get().reverse()).each(function() {
            
            var $thisRow = jQuery(this);
            var thisRowParentId = 0;
            var thisRowLevel = $thisRow.find('.prop-level').val();
            thisRowLevel = parseInt(thisRowLevel);
            
            var $prevRow = $thisRow;
            
            // Bubble up the tree and re-assign parent IDs where required
            while (1 && thisRowLevel > 0) {
                
                // Get the previous row
                $prevRow = $prevRow.prev();
                if ( 1 !== $prevRow.length )
                    break;
                var prevRowLevel = $prevRow.find('input.prop-level').val();
                prevRowLevel = parseInt(prevRowLevel);
                
                // Found the parent item?
                if ( thisRowLevel === prevRowLevel+1 ) {
                    thisRowParentId = $prevRow.find('input.prop-id').val();
                    break;
                }
                
                // We went 2 levels up? Then break!
                if ( thisRowLevel === prevRowLevel+2 ) {
                    console.log('We went too high. Scenario impossible!');
                    break;
                }
                
            };
            
            $thisRow.find('input.prop-parentId').val(thisRowParentId);
//            console.log($thisRow.find('.title').text() + ': Level ' + thisRowLevel + '; Parent: ' + $prevRow.find('.title').text() + ':' + $thisRow.find('input.prop-parentId').val());
            
        });
        
        // Update row orders
        var ooas = {};
        $tbody.find('> tr').each(function() {

            var $thisRow = jQuery(this);
            var thisRowParentId = $thisRow.find('.prop-parentId').val();

            // Start an ordering count for this parent object
            if ( 'undefined' == typeof ooas[thisRowParentId] )
                ooas[thisRowParentId] = 0;
            ooas[thisRowParentId] += 5;

            // Update ordering for this row
            $thisRow.find('input.prop-ooa').val(ooas[thisRowParentId]);
            
            console.log($thisRow.find('.title').text()
                    + ': Level ' + $thisRow.find('input.prop-level').val()
                    + '; Parent: ' + $thisRow.find('input.prop-parentId').val()
                    + '; OOA: ' + ooas[thisRowParentId]);
            
        });
        
    };
    
    return this.constructor.apply(this, arguments);
    
};

Natty.UI.initSortable = function($el) {
    
    if ( 'table' !== $el.prop('nodeName').toLowerCase() )
        return;
    
    new Natty.Prototype.Sortable($el);
    
};