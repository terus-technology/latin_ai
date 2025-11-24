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
 * Upgrade library code for the shortanswer question type.
 *
 * @package    qtype_shortanswergreek
 * @copyright  2021 Terus e-Learning
 * @author     Khairu Aqsara <khairu@teruselearning.co.uk>, Muhamad Ramadhan <rama@teruselearning.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Class for converting attempt data for shortanswer questions when upgrading
 * attempts to the new question engine.
 *
 * This class is used by the code in question/engine/upgrade/upgradelib.php.
 */
class qtype_shortanswergreek_qe2_attempt_updater extends question_qtype_attempt_updater {
    /**
     * Right answer
     *
     * @return ?string
     */
    public function right_answer() {
        foreach ($this->question->options->answers as $ans) {
            if ($ans->fraction > 0.999) {
                return $ans->answer;
            }
        }
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
     * Response summary
     *
     * @param  object $state
     * @return ?string
     */
    public function response_summary($state) {
        if (!empty($state->answer)) {
            return $state->answer;
        } else {
            return null;
        }
    }

    /**
     * Set first step data elements
     *
     * @param  object $state
     * @param  array $data
     * @return void
     */
    public function set_first_step_data_elements($state, &$data) {
        // No action.
    }

    /**
     * Supply missing first step data
     *
     * @param  array $data
     * @return void
     */
    public function supply_missing_first_step_data(&$data) {
        // No action.
    }

    /**
     * Set data elements for step
     *
     * @param  object $state
     * @param  array $data
     * @return void
     */
    public function set_data_elements_for_step($state, &$data) {
        if (!empty($state->answer)) {
            $data['answer'] = $state->answer;
        }
    }
}
