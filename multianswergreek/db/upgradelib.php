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
 * Upgrade library code for the multianswergreek question type.
 *
 * @package    qtype_multianswergreek
 * @copyright  2021 Terus e-Learning
 * @author     Khairu Aqsara <khairu@teruselearning.co.uk>, Muhamad Ramadhan <rama@teruselearning.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Class for converting attempt data for multianswergreek questions when upgrading
 * attempts to the new question engine.
 *
 * This class is used by the code in question/engine/upgrade/upgradelib.php.
 */
class qtype_multianswergreek_qe2_attempt_updater extends question_qtype_attempt_updater {
    /**
     * Question summary
     *
     * @return string
     */
    public function question_summary() {
        $summary = $this->to_text($this->question->questiontext);
        foreach ($this->question->options->questions as $i => $subq) {
            switch ($subq->qtype) {
                case 'multichoice':
                    $choices = [];
                    foreach ($subq->options->answers as $ans) {
                        $choices[] = $this->to_text($ans->answer);
                    }
                    $answerbit = '{' . implode('; ', $choices) . '}';
                    break;
                case 'numerical':
                case 'shortanswer':
                    $answerbit = '_____';
                    break;
                default:
                    $answerbit = '{ERR unknown sub-question type}';
            }
            $summary = str_replace('{#' . $i . '}', $answerbit, $summary);
        }
        return $summary;
    }

    /**
     * Right answer
     *
     * @return string
     */
    public function right_answer() {
        $right = [];

        foreach ($this->question->options->questions as $i => $subq) {
            foreach ($subq->options->answers as $ans) {
                if ($ans->fraction > 0.999) {
                    $right[$i] = $ans->answer;
                    break;
                }
            }
        }

        return $this->display_response($right);
    }

    /**
     * Explode answer
     *
     * @param  string $answer
     * @return array
     */
    public function explode_answer($answer) {
        $response = [];

        foreach (explode(',', $answer) as $part) {
            list($index, $partanswer) = explode('-', $part, 2);
            $response[$index] = str_replace(['&#0044;', '&#0045;'], [",", "-"], $partanswer);
        }

        return $response;
    }

    /**
     * Display response
     *
     * @param  array $response
     * @return string
     */
    public function display_response($response) {
        $summary = [];
        foreach ($this->question->options->questions as $i => $subq) {
            $a = new stdClass();
            $a->i = $i;
            $a->response = $this->to_text($response[$i]);
            $summary[] = get_string('subqresponse', 'qtype_multianswergreek', $a);
        }

        return implode('; ', $summary);
    }

    /**
     * Response summary
     *
     * @param  object $state
     * @return string
     */
    public function response_summary($state) {
        $response = $this->explode_answer($state->answer);
        foreach ($this->question->options->questions as $i => $subq) {
            if ($response[$i] && $subq->qtype == 'multichoice') {
                $response[$i] = $subq->options->answers[$response[$i]]->answer;
            }
        }
        return $this->display_response($response);
    }

    /**
     * Was answered?
     *
     * @param  object $state
     * @return bool
     */
    public function was_answered($state) {
        return !empty($state->answer);
    }

    /**
     * Set first step data elements
     *
     * @param  object $state
     * @param  array $data
     * @return void
     */
    public function set_first_step_data_elements($state, &$data) {
        foreach ($this->question->options->questions as $i => $subq) {
            switch ($subq->qtype) {
                case 'multichoice':
                    $data[$this->add_prefix('_order', $i)] = implode(',', array_keys($subq->options->answers));
                    break;
                case 'numerical':
                    $data[$this->add_prefix('_separators', $i)] = '.$,';
                    break;
            }
        }
    }

    /**
     * Supply missing first step data
     *
     * @param  array $data
     * @return void
     */
    public function supply_missing_first_step_data(&$data) {
        // Currently no action.
    }

    /**
     * Set data elements for step
     *
     * @param  object $state
     * @param  array $data
     * @return void
     */
    public function set_data_elements_for_step($state, &$data) {
        $response = $this->explode_answer($state->answer);
        foreach ($this->question->options->questions as $i => $subq) {
            if (empty($response[$i])) {
                continue;
            }

            switch ($subq->qtype) {
                case 'multichoice':
                    $choices = [];
                    $order = 0;
                    foreach ($subq->options->answers as $ans) {
                        if ($ans->id == $response[$i]) {
                            $data[$this->add_prefix('answer', $i)] = $order;
                        }
                        $order++;
                    }
                    $answerbit = '{' . implode('; ', $choices) . '}';
                    break;
                case 'numerical':
                case 'shortanswer':
                    $data[$this->add_prefix('answer', $i)] = $response[$i];
                    break;
            }
        }
    }

    /**
     * Add prefix
     *
     * @param  string $field
     * @param  string $i
     * @return string
     */
    public function add_prefix($field, $i) {
        $prefix = 'sub' . $i . '_';
        if (substr($field, 0, 2) === '!_') {
            return '-_' . $prefix . substr($field, 2);
        } else if (substr($field, 0, 1) === '-') {
            return '-' . $prefix . substr($field, 1);
        } else if (substr($field, 0, 1) === '_') {
            return '_' . $prefix . substr($field, 1);
        } else {
            return $prefix . $field;
        }
    }
}
