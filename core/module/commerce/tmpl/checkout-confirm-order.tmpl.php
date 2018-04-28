<?php defined('NATTY') or die; ?>
<table class="n-table n-table-border-outer checkout-address-table">
    <thead>
        <tr>
            <th>Delivery Info <a href="<?php echo $addressSetupUrl; ?>"><i class="n-icon n-icon-edit"></i> Edit</a></th>
            <th>Billing Info <a href="<?php echo $addressSetupUrl; ?>"><i class="n-icon n-icon-edit"></i> Edit</a></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="width: 50%;">
                <strong><?php echo $shippingName; ?></strong>
                <?php echo $shippingAddress->render(); ?>
            </td>
            <td>
                <strong><?php echo $billingName; ?></strong>
                <?php echo $billingAddress->render(); ?>
            </td>
        </tr>
    </tbody>
</table>
<table class="n-table n-table-border-outer">
    <thead>
        <tr>
            <th>Description</th>
            <th class="size-small n-ta-ri">Rate</th>
            <th class="size-small n-ta-ri">Qty</th>
            <th class="size-small n-ta-ri">Amt&nbsp;(<?php echo $currency->unitSymbol; ?>)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($cart['items'] as $cartitem): ?>
        <tr>
            <td>
                <div class="prop-title"><?php echo $cartitem->name; ?></div>
                <?php if ($cartitem->description): ?><div class="prop-title"><?php echo $cartitem->description; ?></div><?php endif; ?>
            </td>
            <td class="n-ta-ri"><?php echo natty_format_money($cartitem->rate, array (
                'symbol' => FALSE,
            )); ?></td>
            <td class="n-ta-ri"><?php echo $cartitem->quantity; ?></td>
            <td class="n-ta-ri"><?php echo natty_format_money($cartitem->amountProduct, array (
                'symbol' => FALSE,
            )); ?></td>
        </tr>
        <?php endforeach; ?>
        <?php
        
        // Gross total
        if ( $cart['amountProduct'] != $cart['amountFinal'] ): ?>
        <tr class="overline amount-product">
            <td class="n-ta-ri">Gross amount</td>
            <td></td>
            <td></td>
            <td class="n-ta-ri"><?php echo natty_format_money($cart['amountProduct'], array (
                'symbol' => FALSE,
            )); ?></td>
        </tr>
        <?php endif;

        // Discounts?
        if ($cart['amountDiscount'] > 0): ?>
        <tr class="amount-discount">
            <td class="n-ta-ri"><em>Less:</em> Discount</td>
            <td></td>
            <td></td>
            <td class="n-ta-ri"><?php echo natty_format_money($cart['amountDiscount'], array (
                'symbol' => FALSE,
            )); ?></td>
        </tr>
        <?php endif;

        // Shipping charges?
        if ( $cart['amountShipping'] > 0 ): ?>
        <tr class="amount-shipping">
            <td class="n-ta-ri"><em>Add:</em> Shipping charges</td>
            <td></td>
            <td></td>
            <td class="n-ta-ri"><?php echo natty_format_money($cart['amountShipping'], array (
                'symbol' => FALSE,
            )); ?></td>
        </tr>
        <?php endif;

        // Taxes?
        if ( $cart['amountTax'] > 0 ): ?>
        <tr class="amount-tax">
            <td class="n-ta-ri"><em>Add:</em> Taxes</td>
            <td></td>
            <td></td>
            <td class="n-ta-ri"><?php echo natty_format_money($cart['amountTax'], array (
                'symbol' => FALSE,
            )); ?></td>
        </tr>
        <?php endif; ?>
        <tr class="overline amount-final">
            <td class="n-ta-ri">Net amount</td>
            <td></td>
            <td></td>
            <td class="n-ta-ri"><?php echo natty_format_money($cart['amountFinal'], array (
                'symbol' => TRUE,
            )); ?></td>
        </tr>
    </tbody>
</table>
<?php echo natty_render($form);