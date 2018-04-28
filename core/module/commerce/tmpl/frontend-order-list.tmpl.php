<?php defined('NATTY') or die; ?>
<table class="n-table n-table-border-outer commerce-order-list">
    <thead>
        <tr>
            <th class="cont-image"></th>
            <th>Description</th>
            <th class="size-medium">Value</th>
            <th class="size-small">Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if (0 === sizeof($orderColl))
        echo '<tr><td colspan="4"><div class="n-emptytext">There are no items to display here.</div></td></tr>'; ?>
    <?php foreach ($orderColl as $oid => $order): ?>
        <tr class="thead">
            <td>&nbsp;</td>
            <td>
                Order <a href="<?php echo $order->url; ?>"><?php echo $order->ocode; ?></a> created on  
                <?php echo natty_format_datetime($order->dtCreated); ?>
                <div class="prop-status">
                    Status: <?php echo $order->visibleStatusName; ?>
                </div>
            </td>
            <td><?php echo natty_format_money($order->amountFinal, array (
                'idCurrency' => $order->idCurrency,
            )); ?></td>
            <td><a href="<?php echo $order->url; ?>" class="k-button">Details</a></td>
        </tr>
        <?php foreach ($orderitemColl[$oid] as $orderitem): ?>
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
                <div class="prop-amountFinal">
                    <span class="prop-label">Value</span>
                    <span class="prop-value">
                    <?php echo natty_format_money($orderitem->rate, array (
                        'currency' => $order->idCurrency,
                        'symbol' => FALSE,
                    )) . ' x ' . $orderitem->quantity . ' = ';
                    echo natty_format_money($orderitem->amountFinal, array (
                        'currency' => $order->idCurrency,
                    )); ?>
                    </span>
                </div>
            </td>
            <td>
                
            </td>
            <td>
                
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>
    </tbody>
</table>