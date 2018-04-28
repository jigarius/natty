<?php

namespace Natty\Helper;

class ProfilingHelper {
    
    /**
     * An identifier for the profiler
     * @var string
     */
    protected $id;
    
    /**
     * Details of all registered checkpoints
     * @var array
     */
    protected $checkpoints = array ();
    
    /**
     * Create a profiler with a given ID
     * @param string $id
     */
    public function __construct( $id ) {
        $this->id = (string) $id;
        $this->mark('Start');
    }
    
    public function __toString() {
        return $this->render();
    }
    
    /**
     * Records an event / checkpoint
     * @param string $title
     */
    public function mark( $title ) {
        $entry = array (
            'title' => $title,
            'time' => microtime(true),
            'memory' => memory_get_usage()
        );
        $this->checkpoints[] = $entry;
    }
    
    /**
     * Renders a report in tabular format
     * @return string
     */
    public function render() {
        
        $start_time = $this->checkpoints[0]['time'];
        
        echo '<table cellspacing="0" cellpadding="0" style="width: 100%;" border="1">';
        echo '<caption>' . $this->id . '</caption>';
        echo '<thead>'
                . '<tr>'
                    . '<th>Title</th>'
                    . '<th>Time (s)</th>'
                    . '<th>Memory (kb)</th>'
                . '</tr>'
            . '</thead>';
        foreach ( $this->checkpoints as $checkpoint ):
            // Time since start
            $tss = number_format($checkpoint['time'] - $start_time, 3);
            // Memory in Kb
            $mik = number_format($checkpoint['memory']/1024, 3);
            echo '<tr>'
                    . '<td>' . $checkpoint['title'] . '</td>'
                    . '<td>' . $tss . '</td>'
                    . '<td>' . $mik . '</td>'
                .'</tr>';
        endforeach;
        echo '</table>';
        
    }
    
}