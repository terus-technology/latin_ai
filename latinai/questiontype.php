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
 * @package   qtype_latinai
 * @copyright 2021 Terus E-Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/latinai/question.php');


/**
 * The pattern-match question type.
 *
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_latinai extends question_type {

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_answers($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_answers($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }

    public function save_question_options($question) {
        parent::save_question_options($question);
        return $this->save_answers($question);
    }

    protected function save_answers($question) {
        global $DB;
        $oldanswers = $DB->get_records('question_answers',
                                            array('question' => $question->id), 'id ASC');

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
            $answer->feedback = $this->import_or_save_files($question->feedback[$key],
                    $context, 'question', 'answerfeedback', $answer->id);
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
            $DB->delete_records('question_answers', array('id' => $oldanswer->id));
        }

        // Perform sanity checks on fractional grades.
        if ($maxfraction != 1) {
            $result = new stdClass();
            $result->noticeyesno = get_string('fractionsnomax', 'question', $maxfraction * 100);
            return $result;
        } else {
            return null;
        }
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $this->initialise_question_answers($question, $questiondata);
    }

    public function get_random_guess_score($questiondata) {
        return 0;
    }

    public function get_possible_responses($questiondata) {
        $responses = array();

        $starfound = false;
        foreach ($questiondata->options->answers as $aid => $answer) {
            if ($answer->answer === '*') {
                $starfound = true;
            }
            $responses[$aid] = new question_possible_response($answer->answer,
                    $answer->fraction);
        }
        if (!$starfound) {
            $responses[0] = new question_possible_response(get_string('didnotmatchanyanswer', 'question'), 0);
        }

        $responses[null] = question_possible_response::no_response();

        return array($questiondata->id => $responses);
    }
}
