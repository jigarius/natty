<?php

defined('NATTY') or die;

?>
<form action="" method="post" id="commerce-cart-form">
    <table class="n-table n-table-border-outer commerce-cartitem-list">
        <thead>
            <tr>
                <th>Description</th>
                <th class="size-small n-ta-ri">Rate</th>
                <th class="size-small n-ta-ri">Qty</th>
                <th class="size-small n-ta-ri">Amt&nbsp;(<?php echo $currency->unitSymbol; ?>)</th>
                <th class="context-menu">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php

            if ( 0 === sizeof($cart_data['items']) )
                echo '<tr><td colspan="5"><div class="n-emptytext">There are no items to display here.</div></td></tr>';

            foreach ( $cart_data['items'] as $cartitem ): ?>
            <tr>
                <td>
                    <div class="prop-title"><?php echo $cartitem->name; ?></div>
                    <?php if ( $cartitem->description ):
                        echo '<div class="prop-description">' . $cartitem->description . '</div>';
                    endif; ?>
                </td>
                <td class="n-ta-ri"><?php echo natty_format_money($cartitem->rate, array (
                    'symbol' => FALSE,
                )); ?></td>
                <td class="n-ta-ri"><input type="number" name="<?php echo 'items[' . $cartitem->ciid . '][quantity]'; ?>" value="<?php echo $cartitem->quantity; ?>" min="1" max="99" class="n-ta-ri" /></td>
                <td class="n-ta-ri"><?php echo natty_format_money($cartitem->amountProduct, array (
                    'symbol' => FALSE,
                )); ?></td>
                <td><a href="<?php echo \Natty::url('cart/action', array (
                    'do' => 'delete',
                    'with' => $cartitem->ciid,
                )); ?>">Delete</a></td>
            </tr>
            <?php endforeach;

            // If the cart has items, show totals
            if ( sizeof($cart_data['items']) > 0 ):

                // Gross total
                if ( $cart_data['amountProduct'] != $cart_data['amountFinal'] ):

                    echo '<tr class="overline amount-product">'
                        . '<td class="n-ta-ri"><div class="prop-label">Gross amount</div></td>'
                        . '<td></td>'
                        . '<td></td>'
                        . '<td class="n-ta-ri">' . natty_format_money($cart_data['amountProduct'], array (
                            'symbol' => FALSE,
                        )) . '</td>'
                        . '<td></td>'
                    . '</tr>';

                endif;

                // Discounts?
                if ( $cart_data['amountDiscount'] > 0 ):

                    echo '<tr class="overline amount-discount">'
                        . '<td class="n-ta-ri"><div class="prop-label">Discounts</div></td>'
                        . '<td></td>'
                        . '<td></td>'
                        . '<td class="n-ta-ri">' . natty_format_money($cart_data['amountDiscount'], array (
                            'symbol' => FALSE,
                        )) . '</td>'
                        . '<td></td>'
                    . '</tr>';

                endif;

                // Shipping charges?
                if ( $cart_data['amountShipping'] > 0 ): ?>
                    <tr class="amount-shipping">
                        <td class="n-ta-ri">
                            <div class="prop-label">Shipping charges</div>
                            <div class="prop-description">
                            <?php if ($shipment_destination['idAddress']) { ?>
                                Estimated for your address <em><?php echo $shipment_destination['name']; ?></em>.
                            <?php } elseif ($shipment_destination['name']) { ?>
                                Estimated for <em><?php echo $shipment_destination['name']; ?></em>.
                            <?php } ?>
                                Update your <a href="<?php echo \Natty::url('cart/shipment-destination'); ?>">shipment destination</a> for a better estimate.
                            </div>
                        </td>
                        <td></td>
                        <td></td>
                        <td class="n-ta-ri"><?php
                            echo natty_format_money($cart_data['amountShipping'], array (
                                'symbol' => FALSE,
                            )); ?>
                        </td>
                        <td></td>
                    </tr>
                <?php endif;

                // Taxes?
                if ( $cart_data['amountTax'] > 0 ):

                    echo '<tr class="amount-tax">'
                        . '<td class="n-ta-ri"><div class="prop-label">Taxes</div></td>'
                        . '<td></td>'
                        . '<td></td>'
                        . '<td class="n-ta-ri">' . natty_format_money($cart_data['amountTax'], array (
                            'symbol' => FALSE,
                        )) . '</td>'
                        . '<td></td>'
                    . '</tr>';

                endif;
                
                // Final amount
                echo '<tr class="overline amount-net">'
                    . '<td class="n-ta-ri"><div class="prop-label">Net amount</div></td>'
                    . '<td></td>'
                    . '<td></td>'
                    . '<td class="n-ta-ri">' . natty_format_money($cart_data['amountFinal']) . '</td>'
                    . '<td></td>'
                . '</tr>';

            endif;

            ?>
        </tbody>
    </table>
    <?php
    
    echo natty_render($form);
    
    ?>
    <div class="system-actions">
        <input type="submit" name="update" value="Update cart" class="k-button" />
        <a href="<?php echo \Natty::url('catalog'); ?>" class="k-button">Buy more items</a>
        <input type="submit" name="checkout" value="Checkout now" class="k-button k-primary pull-right" />
    </div>
</form>