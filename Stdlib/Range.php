<?php
/**
 * Description of Range
 *
 * @author huyi
 */
class Eos_Stdlib_Range implements Eos_Stdlib_Comparable {
    protected $_low;
    
    protected $_high;
        
    protected $_excludedLow = true;
    
    protected $_excludedHigh = false;
    
    
    /**
     * Default: (a, b]
     */
    public function __construct($low, $high, 
                                 $excludedLow = true, $excludedHigh = false) {
        $low = (float) $low;
        $high = (float) $high;        
                
        if ($low >= $high) {
            throw new RangeException("Invalid Range: {$this}");
        }
        
        $this->_low = $low;
        $this->_high = $high;
        $this->_excludedLow = (bool) $excludedLow;
        $this->_excludedHigh = (bool) $excludedHigh;
    }

    public function getHigh() {
        return $this->_high;
    }
    
    public function __toString() {
        $string = '';
        if ((bool) $this->_excludedLow) {
            $string .= "({$this->_low}, ";            
        } else {
            $string .= "[{$this->_low}, ";                  
        }
        
        if ((bool) $this->_excludedHigh) {
            $string .= "{$this->_high})";         
        } else {
            $string .= "{$this->_high}]";         
        }
        
        return $string;
    }
    
    /**
     * @return -1(left), 0(in), 1(right)
     */
    public function inRange($x) {
        $x = (float) $x;
        
        if ($x < $this->_low) {
            return -1;
        } else if ($x === $this->_low) {
            return ($this->_excludedLow) ? -1 : 0;
        } else if ($x === $this->_high) {
            return ($this->_excludedHigh) ? 1 : 0;
        } else if ($x > $this->_high) {
            return 1;
        } else {
            return 0;
        }        
    }

    public static function compare(Eos_Stdlib_Comparable $range1, 
                                    Eos_Stdlib_Comparable $range2) {
        return $range1->_high - $range2->_high;
    }
}