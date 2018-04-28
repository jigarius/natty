<?php

namespace Natty\Helper;

/**
 * Listing and Paging Helper
 * @author JigaR Mehta | Greenpill Productions
 * @package natty
 */
class PagingHelper
extends \Natty\StdClass {

    /**
     * List interactivity options
     * @var array
     */
    protected $parameters = array ();

    /**
     * Structured query for retrieving list items
     * @var \Natty\DBAL\SelectQuery
     */
    protected $query = FALSE;

    /**
     * Creates a Database Record List
     * @param string $query The query to fetch records
     */
    public function __construct( \Natty\DBAL\SelectQuery $query ) {
        
        $this->query = $query;
        
        // Set default parameters
        $this->setParameters(array ());
        
    }
    
    /**
     * Loads list data as per set criterions which satisfy a given condition
     * @param array An array of execution options
     * @return bool True on success or false on failure
     */
    public function execute(array $options = array ()) {
        
        // Fallback to default options
        $options = array_merge(array(
            'fetch' => array (\PDO::FETCH_ASSOC),
            'aggregate' => TRUE,
            'parameters' => array (),
        ), $options);
        
        // Paramterers for query placeholders
        $request = \Natty::getRequest();
        $state = array ();
        
        // Prepare a query after considering list options
        $data_query = $this->query->getClone();
        $data_query_params = $options['parameters'];
        $info_query = $this->query->getClone();
        $info_query_params = $options['parameters'];
        
        // Attach parameters and their interactivities
        foreach ( $this->parameters as $key => $definition ):
            
            // Starting index: Record index to start from
            if ( 'si' === $key ):
                
                if ( !isset ($definition['_value']) )
                    $definition['_value'] = $definition['_default'];
                
                if ( !$definition['_isLocked'] ):
                    $definition['_value'] = $request->getInt($key, $definition['_default']);
                endif;
                
                $definition['_value'] = max($definition['_value'], 0);
                $data_query->offset($definition['_value']);
                
                $state[$key] = $definition['_value'];
                $this->parameters[$key] = $definition;
                continue;
                
            endif;
            
            // Chunk size: Records to display per page
            if ( 'cs' === $key ):
                
                if ( !isset ($definition['_value']) )
                    $definition['_value'] = 24;
                
                if ( !$definition['_isLocked'] ):
                    $definition['_value'] = $request->getInt($key, $definition['_value']);
                    $definition['_value'] = min($definition['_value'], 100);
                endif;
                
                if ( $definition['_value'] > 0 )
                    $data_query->limit($definition['_value']);
                
                $state[$key] = $definition['_value'];
                $this->parameters[$key] = $definition;
                continue;
                
            endif;
            
            // Add filter, if enabled
            if ( $definition['_filterEnabled'] ):
                
                if ( !isset ($definition['_value']) )
                    $definition['_value'] = NULL;
                
                if ( !$definition['_filterMethodLocked'] ):
                    $definition['_filterMethod'] = $request->getString($key . '-op', 'includes');
                    $state[$key . '-op'] = $definition['_value'];
                endif;
                
                if ( !$definition['_filterLocked'] ):
                    $definition['_value'] = $request->getString($key);
                endif;
                
                if ( 0 !== strlen($definition['_value']) ):
                    switch ( $definition['_filterMethod'] ):
                        case 'includes':
                            if ( !$definition['_column'] )
                                throw new \RuntimeException('Required index "_column" has invalid value.');
                            $definition['_filterCondition'] = "{$definition['_column']} = :fl_{$key}";
                            $data_query_params['fl_' . $key] = $definition['_value'];
                            $info_query_params['fl_' . $key] = $definition['_value'];
                            break;
                        case 'excludes':
                            if ( !$definition['_column'] )
                                throw new \RuntimeException('Required index "_column" has invalid value.');
                            $definition['_filterCondition'] = "{$definition['_column']} != :fl_{$key}";
                            $data_query_params['fl_' . $key] = $definition['_value'];
                            $info_query_params['fl_' . $key] = $definition['_value'];
                            break;
                        case 'moreThan':
                            if ( !$definition['_column'] )
                                throw new \RuntimeException('Required index "_column" has invalid value.');
                            $definition['_filterCondition'] = "{$definition['_column']} > :fl_{$key}";
                            $data_query_params['fl_' . $key] = $definition['_value'];
                            $info_query_params['fl_' . $key] = $definition['_value'];
                            break;
                        case 'lessThan':
                            if ( !$definition['_column'] )
                                throw new \RuntimeException('Required index "_column" has invalid value.');
                            $definition['_filterCondition'] = "{$definition['_column']} < :fl_{$key}";
                            $data_query_params['fl_' . $key] = $definition['_value'];
                            $info_query_params['fl_' . $key] = $definition['_value'];
                            break;
                        default:
                            if ( !$definition['_filterCondition'] )
                                $definition['_filterCondition'] = array ($definition['_column'], '=', ':' . $key);
                            $data_query_params[$key] = $definition['_value'];
                            $info_query_params[$key] = $definition['_value'];
                            break;
                    endswitch;
                    
                    $data_query->addComplexCondition('AND', $definition['_filterCondition']);
                    $info_query->addComplexCondition('AND', $definition['_filterCondition']);
                    
                    $state[$key] = $definition['_value'];
                    
                endif;
                
            endif;
            
            // Add sorts
            if ( $definition['_sortEnabled'] && $definition['_column'] ):
                
                if ( !$definition['_sortLocked'] ):
                    
                    if ( $definition['_sortMethodLocked'] ) {
                        if ( $key == $request->getString('sb') ) {
                            $state['sb'] = $key;
                        }
                        else {
                            $definition['_sortEnabled'] = 0;
                        }
                    }
                    else {
                        switch ( $request->getString('sb') ):
                            case $key . ':a':
                                $definition['_sortMethod'] = 'asc';
                                $state['sb'] = $key . ':a';
                                break;
                            case $key . ':d':
                                $definition['_sortMethod'] = 'desc';
                                $state['sb'] = $key . ':d';
                                break;
                            default:
                                $definition['_sortEnabled'] = 0;
                                break;
                        endswitch;
                    }
                    
                endif;
                
                if ( $definition['_sortEnabled'] && $definition['_sortMethod'] ):
                    $data_query->orderBy($definition['_column'], $definition['_sortMethod']);
                endif;
                
                $this->parameters[$key] = $definition;
                continue;
                
            endif;
            
            // Add exposed variables to state
            if ( !$definition['_isLocked'] && 0 !== strlen($definition['_value']) )
                $state[$key] = $definition['_value'];
            
        endforeach;
        
        $output = array ();
        
        // Fetch entities
        if ( 'entity' === $options['fetch'][0] ) {
            $entity_handler = \Natty::getHandler($options['fetch'][1]);
            $data_query_opts = $options;
            $data_query_opts['parameters'] = $data_query_params;
            unset ($data_query_opts['fetch']);
            $output['items'] = $entity_handler->execute($data_query, $data_query_opts);
        }
        // Fetch regular
        else {
            $stmt = $data_query->execute($data_query_params);
            $output['items'] = call_user_func_array(array ($stmt, 'fetchAll'), $options['fetch']);
        }
        
        $output['parameters'] = $this->parameters;
        $output['state'] = $state;
        $output['item_count'] = sizeof($output['items']);
        
        // Add item indexes, if available
        if ( $state['cs'] > 0 ):
            $output['first_item'] = $state['si']+1;
            $output['last_item'] = $output['first_item'] + $output['item_count'];
            $output['current_page'] = ceil($output['first_item'] / $state['cs']);
            $output['current_page'] = max($output['current_page'], 1);
        endif;
        
        // Query for fetching aggregate data
        if ( $options['aggregate'] && $this->parameters['cs']['_value'] > 0 ):
            
            $info_query
                ->offset(FALSE)
                ->limit(FALSE);
            
            $info_query = "SELECT COUNT(1) {count} FROM (" . $info_query . ") {d} WHERE 1=1";
            $info_query = $this->query->getDbo()->prepare($info_query);
            
            // Retrieve additional information
            $output['total_items'] = $info_query_params
                    ? $info_query->execute($info_query_params)
                    : $info_query->execute();
            $output['total_items'] = $output['total_items']->fetchColumn();
            
            if ( $state['cs'] > 0 )
                $output['total_pages'] = ceil($output['total_items'] / $state['cs']);
            
        endif;
        
        return $output;
        
    }

    /**
     * Returns interactivity parameters.
     * @return array
     */
    public function getParameters() {
        return $this->parameters;
    }
    
    /**
     * Prepare column definitions for the list
     * @todo Rename method to something else as the word "parameters" refers
     * to query parameters for prepared statements.
     */
    public function setParameters(array $data) {
        
        $this->parameters = array ();
        
        $data = array_merge(array (
            'si' => array (
                '_default' => 0,
                '_isLocked' => 0,
            ),
            'cs' => array (
                '_default' => 24,
            ),
        ), $data);
        
        $default_definition = array (
            '_column' => NULL,
            '_sortEnabled' => 0,
            '_sortLocked' => 1,
            '_sortMethod' => 'asc',
            '_sortMethodLocked' => 1,
            '_filterEnabled' => 1,
            '_filterLocked' => 0,
            '_filterMethod' => 'includes',
            '_filterMethodLocked' => 1,
            '_filterCondition' => NULL,
            '_isLocked' => 1,
            '_default' => NULL,
            '_value' => NULL,
        );
        
        foreach ( $data as $alias => $definition ):
            $definition = array_merge($default_definition, $definition);
            $this->parameters[$alias] = $definition;
        endforeach;
        
    }

}