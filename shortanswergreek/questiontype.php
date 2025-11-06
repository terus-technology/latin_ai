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
 * Question type class for the short answer question type.
 *
 * @package    qtype_shortanswergreek
 * @copyright  2021 Terus e-Learning
 * @author     Khairu Aqsara <khairu@teruselearning.co.uk>, Muhamad Ramadhan <rama@teruselearning.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\exception\moodle_exception;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/shortanswer/question.php');

/**
 * The short answer question type.
 */
class qtype_shortanswergreek extends question_type {
    /**
     * Extra question fields
     *
     * @return array
     */
    public function extra_question_fields() {
        return ['qtype_shortansgr_options', 'usecase'];
    }

    /**
     * Move files
     *
     * @param  int $questionid
     * @param  int $oldcontextid
     * @param  int $newcontextid
     * @return void
     */
    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_answers($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }

    /**
     * Delete files
     *
     * @param  int $questionid
     * @param  int $contextid
     * @return void
     */
    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_answers($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }

    /**
     * Save question options
     *
     * @param  mixed $question
     * @return void
     */
    public function save_question_options($question) {
        // Perform sanity checks on fractional grades.
        $maxfraction = -1;
        foreach ($question->answer as $key => $answerdata) {
            if ($question->fraction[$key] > $maxfraction) {
                $maxfraction = $question->fraction[$key];
            }
        }

        if ($maxfraction != 1) {
            throw new moodle_exception(
                'fractionsnomax',
                'question',
                '',
                $maxfraction * 100
            );
        }

        parent::save_question_options($question);

        $this->save_question_answers($question);

        $this->save_hints($question);
    }

    /**
     * Fill answer fields
     *
     * @param  object $answer
     * @param  object $questiondata
     * @param  int $key
     * @param  object $context
     * @return object
     */
    protected function fill_answer_fields($answer, $questiondata, $key, $context) {
        $answer = (object) parent::fill_answer_fields($answer, $questiondata, $key, $context);
        $answer->answer = trim($answer->answer);
        return $answer;
    }

    /**
     * Initialise question instance
     *
     * @param  question_definition $question
     * @param  object $questiondata
     * @return void
     */
    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $this->initialise_question_answers($question, $questiondata);
    }

    /**
     * Get random guess score
     *
     * @param  object $questiondata
     * @return float
     */
    public function get_random_guess_score($questiondata) {
        foreach ($questiondata->options->answers as $aid => $answer) {
            if ('*' == trim($answer->answer)) {
                return $answer->fraction;
            }
        }
        return 0;
    }

    /**
     * Get possible responses
     *
     * @param  object $questiondata
     * @return array
     */
    public function get_possible_responses($questiondata) {
        $responses = [];

        $starfound = false;
        foreach ($questiondata->options->answers as $aid => $answer) {
            $responses[$aid] = new question_possible_response($answer->answer, $answer->fraction);
            if ($answer->answer === '*') {
                $starfound = true;
            }
        }

        if (!$starfound) {
            $responses[0] = new question_possible_response(get_string('didnotmatchanyanswer', 'question'), 0);
        }

        $responses[null] = question_possible_response::no_response();

        return [$questiondata->id => $responses];
    }
}
