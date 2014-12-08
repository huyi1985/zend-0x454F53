<?php

/**
 * Description of RangeSet
 *
 * @author huyi
 */
class Eos_Stdlib_RangeSet {

    /**
     * @var array[Eos_Stdlib_Range]
     */
    private $_ranges;

    /**
     * @var boolean  Needs to be re-arranged?
     */
    private $_sorted = false;

    public function addRange(Eos_Stdlib_Range $range) {
        $this->_ranges[] = $range;
        $this->_sorted = false;
    }

    /**
     * @return array
     */
    public function getRanges() {
        return $this->_ranges;
    }

    /**
     * @return Eos_Stdlib_Range|null
     * @throws Exception    When number not found
     */
    public function search($number, $throwsException = true) {
        if (!$this->_sorted) {
            usort($this->_ranges, array('Eos_Stdlib_Range', 'compare'));
            $this->_sorted = true;
        }

        $left = 0;
        $right = count($this->_ranges) - 1;
        $middle = (int) (count($this->_ranges) / 2);

        $index = null;
        // Classical Binary Search Algorithm
        while ($left <= $right) {
            $middleRange = $this->_ranges[$middle];
            $result = $middleRange->inRange($number);
            if ($middleRange->inRange($number) === 0) {
                $index = $middle;
                break;
            } else if ($middleRange->inRange($number) === -1) {
                $right = $middle - 1;
            } else {
                $left = $middle + 1;
            }

            $middle = (int) (($left + $right) / 2);
        }

        if ($index !== null) {
            return $this->_ranges[$index];
        } else if (!$throwsException) {
            return null;
        } else {
            throw new Exception("{$number} not found");
        }
    }

}
