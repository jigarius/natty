<?php defined('NATTY') or die; ?>
<div class="etid-commerce--order">
    <?php if (isset ($form)) echo natty_render($form); ?>
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4 class="n-heading-block">Order <?php echo $order->ocode; ?></h4>
            <div class="prop prop-dtCreated label-inline">
                <span class="prop-label">Order date:</span>
                <span class="prop-value"><?php echo natty_format_datetime($order->dtCreated, array (
                    'format' => 'datetime',
                )); ?></span>
            </div>
            <div class="prop prop-status label-inline">
                <span class="prop-label">Order status:</span>
                <span class="prop-value"><?php echo $order->visibleStatus->name; ?></span>
            </div>
        </div>
        <div class="col-xs-12 col-md-6">
            <h4 class="n-heading-block">Customer Information</h4>
            <div class="prop prop-name label-inline">
                <span class="prop-label">Name:</span>
                <span class="prop-value"><?php echo $order->customer->name; ?></span>
            </div>
            <div class="prop prop-email label-inline">
                <span class="prop-label">Email:</span>
                <span class="prop-value"><?php echo $order->customer->email; ?></span>
            </div>
            <div class="prop prop-mobile label-inline">
                <span class="prop-label">Mobile:</span>
                <span class="prop-value"><?php echo natty_vod($order->customer->mobile, '-'); ?></span>
            </div>
        </div>
    </div>
    <br />
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4 class="n-heading-block">Billing information</h4>
            <strong><?php echo $order->billingName; ?></strong>
            <?php echo $order->billingAddress->render(); ?>
        </div>
        <div class="col-xs-12 col-md-6">
            <h4 class="n-heading-block">Shipping information</h4>
            <strong><?php echo $order->shippingName; ?></strong>
            <?php echo $order->shippingAddress->render(); ?>
        </div>
    </div>
    <br />
    <div class="row">
        <div class="col-xs-12 col-md-6 col-payment-info">
            <h4 class="n-heading-block">Payment information</h4>
            <table class="n-table">
                <tbody>
                    <?php

                    foreach ($tranColl as $tran):

                        $tran_url = \Natty::url('backend/payrec/trans/' . $tran->tid);

                        $tran->statusText = 'Incomplete';
                        if ($tran->status < 0)
                            $tran->statusText = 'Failed';
                        if ($tran->status > 0)
                            $tran->statusText = 'Succeeded';

                        echo '<tr>'
                                . '<td class="size-medium">' . natty_format_datetime($tran->dtCreated) . '</td>'
                                . '<td>'
                                    . '<a class="prop-title" href="' . $tran_url . '">' . natty_text($tran->name, $tran->variables) . '</a>'
                                    . '<div class="prop-description">' . $tran->statusText . '</div>'
                                . '</td>'
                                . '<td class="n-ta-ri">' . natty_format_money($tran->amount, array (
                                    'currency' => $tran->idCurrency,
                                )) . '</td>'
                            . '</tr>';

                    endforeach;

                    ?>
                    <td colspan="3" class="n-ta-ce"><a href="#" class="k-button">Add payment</a></td>
                </tbody>
            </table>
        </div>
        <div class="col-xs-12 col-md-6">
            <h4 class="n-heading-block">Packages dispatched</h4>
            <table class="n-table">
                <tbody>
                    <?php
                    
                    foreach ($shipmentColl as $shipment):

                        $shipment_url = \Natty::url('backend/commerce/orders/' . $shipment->oid . '/shipments/' . $shipment->sid);

                        $shipment->statusText = 'Incomplete';
                        if ($shipment->status < 0)
                            $shipment->statusText = 'Failed';
                        if ($shipment->status > 0)
                            $shipment->statusText = 'Delivered';

                        echo '<tr>'
                                . '<td class="size-medium">' . natty_format_datetime($shipment->dtCreated) . '</td>'
                                . '<td>'
                                    . '<div class="prop-title">' . $shipment->description . '</div>'
                                    . '<div class="prop-description">';
                        if ($shipment->dtVerified)
                            echo natty_format_datetime($shipment->dtVerified) . ', ';
                        echo $shipment->statusText
                                    . '</div>'
                                . '</td>'
                            . '</tr>';

                    endforeach;
                    
                    ?>
                    <tr>
                        <td colspan="2" class="n-ta-ce"><a href="<?php echo \Natty::url('backend/commerce/orders/' . $order->oid . '/shipments/create', array (
                            'bounce' => TRUE,
                        )); ?>" class="k-button">Add dispatch</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <table class="n-table n-table-border-outer item-list">
        <thead>
            <tr>
                <th class="cont-image"></th>
                <th>Description</th>
                <th class="size-small n-ta-ri">Rate</th>
                <th class="size-small n-ta-ri">Qty</th>
                <th class="size-small n-ta-ri">Product total</th>
                <th class="size-small n-ta-ri">Line total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order->items as $orderitem): ?>
            <tr>
                <td><?php if ($orderitem->image):
                    echo '<img src="' . $orderitem->image . '" alt="" class="prop-image" />';
                endif;
                ?></td>
                <td>
                    <a href="<?php echo $orderitem->productUrl; ?>" class="prop-title" target="_blank"><?php echo $orderitem->name; ?></a>
                    <?php if ( $orderitem->description ): ?>
                    <div class="prop-description"><?php echo $orderitem->description; ?></div>
                    <?php endif; ?>
                </td>
                <td class="n-ta-ri"><?php echo natty_format_money($orderitem->rate, array (
                        'currency' => $order->idCurrency,
                    )); ?></td>
                <td class="n-ta-ri"><?php echo $orderitem->quantity; ?></td>
                <td class="n-ta-ri"><?php echo natty_format_money($orderitem->amountProduct, array (
                    'currency' => $order->idCurrency,
                )); ?></td>
                <td class="n-ta-ri"><?php echo natty_format_money($orderitem->amountFinal, array (
                    'currency' => $order->idCurrency,
                )); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if ($order->amountFinal != $order->amountProduct): ?>
                <tr class="overline amount-product">
                    <td></td>
                    <td class="n-ta-ri">Gross amount</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="n-ta-ri"><?php echo natty_format_money($order->amountProduct, array (
                        'currency' => $order->idCurrency,
                    )); ?></td>
                </tr>
                <?php if ($order->amountDiscount > 0): ?>
                    <tr class="amount-discount">
                        <td></td>
                        <td class="n-ta-ri"><em>Less:</em> Discount</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="n-ta-ri"><?php echo natty_format_money($order->amountDiscount, array (
                            'currency' => $order->idCurrency,
                        )); ?></td>
                    </tr>
                <?php endif; ?>
                <?php if ($order->amountShipping > 0): ?>
                    <tr class="amount-shipping">
                        <td></td>
                        <td class="n-ta-ri"><em>Add:</em> Shipping</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="n-ta-ri"><?php echo natty_format_money($order->amountShipping, array (
                            'currency' => $order->idCurrency,
                        )); ?></td>
                    </tr>
                <?php endif; ?>
                <?php if ($order->amountTax > 0): ?>
                    <tr class="amount-tax">
                        <td></td>
                        <td class="n-ta-ri"><em>Add:</em> Taxes</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="n-ta-ri"><?php echo natty_format_money($order->amountTax, array (
                            'currency' => $order->idCurrency,
                        )); ?></td>
                    </tr>
                <?php endif; ?>
                <tr class="overline amount-final">
                    <td></td>
                    <td class="n-ta-ri">Net amount</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="n-ta-ri"><?php echo natty_format_money($order->amountFinal, array (
                        'currency' => $order->idCurrency,
                    )); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>