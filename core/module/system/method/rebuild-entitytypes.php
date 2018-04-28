<?php

$etype_handler = \Natty::getHandler('system--entitytype');

$output = $etype_handler->read(array (
    'conditions' => '1=1'
));

// Read existing models
$fresh_etypes = array();
\Natty::trigger('system/entitytypeDeclare', $fresh_etypes);
\Natty::trigger('system/entitytypeRevise', $fresh_etypes);

// Update existing models
foreach ($output as $etype):

    $etid = $etype->etid;

    // Entity-type was deleted?
    if (!isset($fresh_etypes[$etid])):
        $etype->delete();
        unset($output[$etid]);
        continue;
    endif;

    // Entity-type was updated!
    $record = $fresh_etypes[$etid];
    $etype->setState($record);
    $etype->save();

    unset($fresh_etypes[$etid]);

endforeach;

// Insert new models
foreach ($fresh_etypes as $etid => $record):
    $record['etid'] = $etid;
    $etype = $etype_handler->create($record);
    $etype->isNew = TRUE;
    $etype->save();
    $output[$etid] = $etype;
endforeach;