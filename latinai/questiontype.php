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
 * Question type class for the pattern-match question type.
 *
 * @package    qtype_latinai
 * @copyright  2021 Terus e-Learning
 * @author     Khairu Aqsara <khairu@teruselearning.co.uk>, Muhamad Ramadhan <rama@teruselearning.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\exception\moodle_exception;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/latinai/question.php');

/**
 * The pattern-match question type.
 */
class qtype_latinai extends question_type {
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
     * @param  object $question
     * @return ?object
     */
    public function save_question_options($question) {
        parent::save_question_options($question);
        return $this->save_answers($question);
    }

    /**
     * Save answers
     *
     * @param  object $question
     * @return stdClass
     */
    protected function save_answers($question) {
        global $DB;

        $oldanswers = $DB->get_records('question_answers', ['question' => $question->id], 'id ASC');

        $context = $question->context;
        $maxfraction = -1;

        // Insert all the new answers.
        foreach ($question->answer as $key => $answerdata) {
            // Check for, and ignore, completely blank answer from the form.
            if (trim($answerdata) == '' && $question->fraction[$key] == 0 &&
                    html_is_blank($question->feedback[$key]['text'])) {
                continue;
            }

            // Update an existing answer if possible.
            $answer = array_shift($oldanswers);
            if (!$answer) {
                $answer = new stdClass();
                $answer->question = $question->id;
                $answer->answer = '';
                $answer->feedback = '';
                $answer->id = $DB->insert_record('question_answers', $answer);
            }

            $answer->answer = trim($answerdata);

            $answer->fraction = $question->fraction[$key];
            $answer->feedback = $this->import_or_save_files(
                $question->feedback[$key],
                $context,
                'question',
                'answerfeedback',
                $answer->id
            );
            $answer->feedbackformat = $question->feedback[$key]['format'];
            $DB->update_record('question_answers', $answer);

            if ($question->fraction[$key] > $maxfraction) {
                $maxfraction = $question->fraction[$key];
            }
        }

        if (isset($question->otherfeedback) && !html_is_blank($question->otherfeedback['text'])) {
            $otheranswer = new stdClass();
            $otheranswer->answer = '*';
            $otheranswer->fraction = 0;
            $otheranswer->feedback = '';
            $otheranswer->question = $question->id;
            $oldotheranswer = array_shift($oldanswers);
            if (!$oldotheranswer) {
                $otheranswer->id = $DB->insert_record('question_answers', $otheranswer);
            } else {
                $otheranswer->id = $oldotheranswer->id;
            }
            $otheranswer->feedback = $this->import_or_save_files($question->otherfeedback,
                    $context, 'question', 'answerfeedback', $otheranswer->id);
            $otheranswer->feedbackformat = $question->otherfeedback['format'];
            $DB->update_record('question_answers', $otheranswer);
        }

        // Delete any left over old answer records.
        $fs = get_file_storage();
        foreach ($oldanswers as $oldanswer) {
            $fs->delete_area_files($context->id, 'question', 'answerfeedback', $oldanswer->id);
            $DB->delete_records('question_answers', ['id' => $oldanswer->id]);
        }

        // Perform sanity checks on fractional grades.
        if ($maxfraction != 1) {
            throw new moodle_exception(
                'fractionsnomax',
                'question',
                '',
                $maxfraction * 100
            );
        }

        return null;
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
     * @return int
     */
    public function get_random_guess_score($questiondata) {
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
            if ($answer->answer === '*') {
                $starfound = true;
            }
            $responses[$aid] = new question_possible_response($answer->answer, $answer->fraction);
        }
        if (!$starfound) {
            $responses[0] = new question_possible_response(get_string('didnotmatchanyanswer', 'question'), 0);
        }

        $responses[null] = question_possible_response::no_response();

        return [$questiondata->id => $responses];
    }
}
