<?php

namespace Natty;

defined('NATTY') or die;

/**
 * An extension to PHPs native DateTime Object
 * @author JigaR Mehta | Greenpill Productions
 */
class Time extends \DateTime {
    
    public function __toString() {
        return self::render(true);
    }

    public function asPointInTime() {
        
        $now = new DateTime();
        $diff = $this->diff($now);
        $keys = array (
            'y' => 'year',
            'm' => 'month',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second'
        );
        
        foreach ( $keys as $notation => $unit ):
            if ( 's' == $notation || 0 != $diff->$notation ):
                
                // Difference not in seconds
                if ( 's' != $notation ):
                    $result = $diff->$notation . ' ' . ( 1 == abs($diff->$notation) ? $unit : $unit . 's' );
                    break;
                endif;
                
                // Differences below 10 seconds are as good as "now"
                if ( $diff->s <= 10 ):
                    return 'just now';
                    break;
                endif;
                
                // Ignore differences below 30 seconds
                if ( $diff->s <= 30 ):
                    $result = 'few seconds';
                    break;
                endif;
                
                // Differences below 60 seconds
                if ( $diff->s < 60 ):
                    $result = 'under a minute';
                    break;
                endif;
                
            endif;
        endforeach;
        
         $result .= $diff->invert ? ' from now' : ' ago';
         return $result;
        
    }
    
    /**
     * Generates an abbreviation of the time in the specified format
     * with the full date as a tool-tip to it
     * @param bool $return_output Whether to return the output
     * @param string $format [optional] The format to render in; Defaults
     * to NConfig::dateFormat
     * @return string Generated markup if $return_output is true
     */
    public function render($return_output = false, $format = null) {
        $format = natty_vod($format, NConfig::dateFormat);
        $mu = '<abbr title="' . $this->format(NConfig::dateFormat . ' \a\t ' . NConfig::timeFormat) . '">' . $this->format($format) . '</abbr>';
        if ($return_output)
            return $mu;
        echo $mu;
    }

    /**
     * Tells whether the Time occurs in a Leap Year or not
     * @return bool True if a leap year
     */
    public function isLeapYear() {
        return (bool) $this->format('L');
    }

    /**
     * Returns the number of days in a given month of a given year
     * @param int $month Month number from 1 through 12
     * @param int $year Year being refered to (required for February)
     * @return int Number of days in the month, i.e. 28 through 31
     */
    public static function getDaysInMonth($month, $year = null) {

        $month = (int) $month;
        $month_days = array(
            1 => 31,
            2 => 28,
            3 => 31,
            4 => 30,
            5 => 31,
            6 => 31,
            7 => 31,
            8 => 31,
            9 => 30,
            10 => 31,
            11 => 30,
            12 => 31
        );

        $result = $month_days[$month];

        // Adjustment for February
        if (2 == $month && !is_null($year) && 366 == self::getDaysInYear($year)) {
            $result++;
        }

        return $result;
    }

    /**
     * Returns the number of days in a given year
     * @param int $year 2-digit or 4-digit year
     * @return int Number of days, 365 or 366
     */
    public static function getDaysInYear($year) {
        $year = (int) $year;
        return (0 == $year % 4) ? 366 : 365;
    }

}
