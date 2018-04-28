<?php defined('NATTY') or die; ?>
<div class="<?php $classes; ?>">
<?php if (!$options['page']):
    echo $heading;
endif; ?>
    <table class="n-table n-table-border-all">
        <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Reference</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo $entity->tid; ?></td>
                <td><a href="<?php echo \Natty::url($entity->contextUrl); ?>" target="_blank"><?php echo $entity->contextLabel; ?></a></td>
                <td><?php echo natty_format_money($entity->amount, array (
                    'currency' => $entity->idCurrency,
                )); ?></td>
                <td><?php
        
                switch ($entity->status):
                    case -1:
                        echo 'Failed';
                        break;
                    case 0:
                        echo 'In progress';
                        break;
                    case 1:
                        echo 'Succeeded';
                        break;
                endswitch;

                ?></td>
            </tr>
        </tbody>
    </table>
</div>