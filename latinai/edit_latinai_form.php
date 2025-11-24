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
 * Defines the editing form for the latinai question type.
 *
 * @package    qtype_latinai
 * @copyright  2021 Terus e-Learning
 * @author     Khairu Aqsara <khairu@teruselearning.co.uk>, Muhamad Ramadhan <rama@teruselearning.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The editing form class for the latinai question type.
 */
class qtype_latinai_edit_form extends question_edit_form {
    /**
     * Add question-type specific form fields.
     *
     * @param object $mform the form being built.
     */
    protected function definition_inner($mform) {
        $modalid = md5(date('ymhis'));
        global $PAGE;
        $PAGE->requires->js_call_amd('qtype_latinai/latinjavascript', 'showModalQuestion', [$modalid]);
        $PAGE->requires->css('/question/type/latinai/assets/css/datatables.bootstrap5.css', true);

        $this->add_per_answer_fields($mform, get_string('answerno', 'qtype_latinai', '{no}'), question_bank::fraction_options());
        $this->add_interactive_settings();
    }

    /**
     * Add per answer fields
     *
     * @param  object $mform
     * @param  string $label
     * @param  array $gradeoptions
     * @param  ?int $minoptions
     * @param  ?int $addoptions
     * @return void
     */
    protected function add_per_answer_fields(&$mform, $label, $gradeoptions, $minoptions = 11, $addoptions = QUESTION_NUMANS_ADD) {
        parent::add_per_answer_fields($mform, $label, $gradeoptions, $minoptions, $addoptions);
        $answersinstruct = $mform->createElement(
            'static',
            'answersinstruct',
            get_string('correctanswers', 'qtype_latinai'),
            get_string('filloutoneanswer', 'qtype_latinai')
        );
        $mform->insertElementBefore($answersinstruct, 'answer[0]');
    }

    /**
     * Get more choices string
     *
     * @return string
     */
    protected function get_more_choices_string() {
        return get_string('addmoreanswerblanks', 'qtype_latinai');
    }

    /**
     * Get per answer fields
     *
     * @param  object $mform
     * @param  string $label
     * @param  array $gradeoptions
     * @param  array $repeatedoptions
     * @param  array $answersoption
     * @return array
     */
    protected function get_per_answer_fields($mform, $label, $gradeoptions, &$repeatedoptions, &$answersoption) {
        $repeated = [];
        $repeated[] = $mform->createElement('textarea', 'answer', $label, ['rows' => '4', 'cols' => '60', 'style' => 'width:100%']);
        $repeated[] = $mform->createElement('select', 'fraction', get_string('grades'), $gradeoptions);
        $repeated[] = $mform->createElement(
            'editor',
            'feedback',
            get_string('feedback', 'question'),
            ['rows' => 5],
            $this->editoroptions
        );

        $repeatedoptions['answer']['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption = 'answers';
        return $repeated;
    }

    /**
     * Data preprocessing
     *
     * @param  object $question
     * @return object
     */
    public function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question);
        return $question;
    }

    /**
     * Question type
     *
     * @return string
     */
    public function qtype() {
        return 'latinai';
    }
}
