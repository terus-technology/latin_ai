<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Smith Waterman Algorithm implementation.
 * https://en.wikipedia.org/wiki/Smith-Waterman_algorithm
 *
 * @package    qtype_latinai
 * @copyright  2021 Terus e-Learning
 * @author     Khairu Aqsara <khairu@teruselearning.co.uk>, Muhamad Ramadhan <rama@teruselearning.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Smith Waterman Gotoh class
 */
class smith_waterman_gotoh {
    /**
     * gapvalue
     *
     * @var float
     */
    private $gapvalue;

    /**
     * substitution
     *
     * @var mixed
     */
    private $substitution;

    /**
     * Constructs a new Smith Waterman metric.
     *
     * @param int   $gapvalue gapvalue a non-positive gap penalty
     * @param mixed $substitution a substitution function
     */
    public function __construct($gapvalue = -0.5, $substitution = null) {
        if ($gapvalue > 0.0) {
            throw new Exception("gapvalue must be <= 0");
        }
        if (empty($substitution)) {
            $this->substitution = new smith_waterman_match_mismatch(1.0, -2.0);
        } else {
            $this->substitution = $substitution;
        }
        $this->gapvalue = $gapvalue;
    }

    /**
     * Compare
     *
     * @param  string $a
     * @param  string $b
     * @return float
     */
    public function compare($a, $b) {
        $a = trim(mb_strtolower($a));
        $b = trim(mb_strtolower($b));

        if ($a === '' && $b === '') {
            return 1.0;
        }

        if ($a === '' || $b === '') {
            return 0.0;
        }

        if ($a === $b) {
            return 1.0;
        }

        $maxdistance = min(mb_strlen($a), mb_strlen($b)) * max($this->substitution->max(), abs($this->gapvalue));
        return $this->compute_score($a, $b) / $maxdistance;
    }

    /**
     * Smith waterman gotoh
     *
     * @param  string $s
     * @param  string $t
     * @return int
     */
    private function compute_score($s, $t) {
        $v0 = [];
        $v1 = [];
        $tlen = mb_strlen($t);
        $max = $v0[0] = max(0, $this->gapvalue, $this->substitution->compare($s, 0, $t, 0));

        for ($j = 1; $j < $tlen; $j++) {
            $v0[$j] = max(0, $v0[$j - 1] + $this->gapvalue,
                $this->substitution->compare($s, 0, $t, $j));

            $max = max($max, $v0[$j]);
        }

        // Find max.
        for ($i = 1; $i < mb_strlen($s); $i++) {
            $v1[0] = max(0, $v0[0] + $this->gapvalue, $this->substitution->compare($s, $i, $t, 0));

            $max = max($max, $v1[0]);

            for ($j = 1; $j < $tlen; $j++) {
                $v1[$j] = max(0, $v0[$j] + $this->gapvalue, $v1[$j - 1] + $this->gapvalue,
                    $v0[$j - 1] + $this->substitution->compare($s, $i, $t, $j));

                $max = max($max, $v1[$j]);
            }

            for ($j = 0; $j < $tlen; $j++) {
                $v0[$j] = $v1[$j];
            }
        }

        return $max;
    }
}

/**
 * Smith Waterman Match Mismatch class
 */
class smith_waterman_match_mismatch {
    /**
     * Match value
     *
     * @var int
     */
    private $matchvalue;

    /**
     * Mismatch value
     *
     * @var int
     */
    private $mismatchvalue;

    /**
     * Constructs a new match-mismatch substitution function. When two
     * characters are equal a score of <code>matchvalue</code> is assigned. In
     * case of a mismatch a score of <code>mismatchvalue</code>. The
     * <code>matchvalue</code> must be strictly greater then
     * <code>mismatchvalue</code>
     *
     * @param int $matchvalue value when characters are equal
     * @param int $mismatchvalue value when characters are not equal
     */
    public function __construct($matchvalue, $mismatchvalue) {
        if ($matchvalue <= $mismatchvalue) {
            throw new Exception("matchvalue must be > mismatchvalue");
        }

        $this->matchvalue = $matchvalue;
        $this->mismatchvalue = $mismatchvalue;
    }

    /**
     * Compare
     *
     * @param  array $a
     * @param  int $aindex
     * @param  array $b
     * @param  int $bindex
     * @return int
     */
    public function compare($a, $aindex, $b, $bindex) {
        return ($a[$aindex] === $b[$bindex] ? $this->matchvalue : $this->mismatchvalue);
    }

    /**
     * Max value
     *
     * @return int
     */
    public function max() {
        return $this->matchvalue;
    }

    /**
     * Min value
     *
     * @return int
     */
    public function min() {
        return $this->mismatchvalue;
    }
}
