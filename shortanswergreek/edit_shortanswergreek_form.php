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
 * Defines the editing form for the short answer greek question type.
 *
 * @package    qtype_shortanswergreek
 * @copyright  2021 Terus e-Learning
 * @author     Khairu Aqsara <khairu@teruselearning.co.uk>, Muhamad Ramadhan <rama@teruselearning.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Short answer question editing form definition.
 */
class qtype_shortanswergreek_edit_form extends question_edit_form {
    /**
     * Add question-type specific form fields.
     *
     * @param  object $mform the form being built.
     * @return void
     */
    protected function definition_inner($mform) {
        $menu = [
            get_string('caseno', 'qtype_shortanswergreek'),
            get_string('caseyes', 'qtype_shortanswergreek'),
        ];
        $mform->addElement('select', 'usecase', get_string('casesensitive', 'qtype_shortanswergreek'), $menu);

        $mform->addElement(
            'static',
            'answersinstruct',
            get_string('correctanswers', 'qtype_shortanswergreek'),
            get_string('filloutoneanswer', 'qtype_shortanswergreek')
        );
        $mform->closeHeaderBefore('answersinstruct');

        $this->add_per_answer_fields($mform, get_string('answerno', 'qtype_shortanswergreek', '{no}'), question_bank::fraction_options());

        $this->add_interactive_settings();
    }

    /**
     * Get more choices string
     *
     * @return string
     */
    protected function get_more_choices_string() {
        return get_string('addmoreanswerblanks', 'qtype_shortanswergreek');
    }

    /**
     * Data preprocessing
     *
     * @param  object $question
     * @return object
     */
    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question);
        $question = $this->data_preprocessing_hints($question);

        return $question;
    }

    /**
     * Validation
     *
     * @param  array $data
     * @param  array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $answers = $data['answer'];
        $answercount = 0;
        $maxgrade = false;
        foreach ($answers as $key => $answer) {
            $trimmedanswer = trim($answer);
            if ($trimmedanswer !== '') {
                $answercount++;
                if ($data['fraction'][$key] == 1) {
                    $maxgrade = true;
                }
            } else if ($data['fraction'][$key] != 0 ||
                    !html_is_blank($data['feedback'][$key]['text'])) {
                $errors["answeroptions[{$key}]"] = get_string('answermustbegiven', 'qtype_shortanswergreek');
                $answercount++;
            }
        }
        if ($answercount == 0) {
            $errors['answeroptions[0]'] = get_string('notenoughanswers', 'qtype_shortanswergreek', 1);
        }
        if ($maxgrade == false) {
            $errors['answeroptions[0]'] = get_string('fractionsnomax', 'question');
        }
        return $errors;
    }

    /**
     * Qtype
     *
     * @return string
     */
    public function qtype() {
        return 'shortanswer';
    }
}
