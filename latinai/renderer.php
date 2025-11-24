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
 * Renderer
 *
 * @package    qtype_latinai
 * @copyright  2021 Terus e-Learning
 * @author     Khairu Aqsara <khairu@teruselearning.co.uk>, Muhamad Ramadhan <rama@teruselearning.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Generates the output for pattern-match questions.
 *
 * @copyright  2021 Terus E-Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_latinai_renderer extends qtype_renderer {
    /**
     * Feedback class custom
     *
     * @param  mixed $fraction
     * @param  object $state
     * @return string
     */
    public function feedback_class_custom($fraction, $state) {
        return $state->get_feedback_class();
    }

    /**
     * Feedback image custom
     *
     * @param  mixed $fraction
     * @param  object $state
     * @return string
     */
    protected function feedback_image_custom($fraction, $state) {
        $feedbackclass = $state->get_feedback_class();
        return $this->output->pix_icon('i/grade_' . $feedbackclass, get_string($feedbackclass, 'question'));
    }

    /**
     * Formulation and controls
     *
     * @param  question_attempt $qa
     * @param  question_display_options $options
     * @return string
     */
    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        $question = $qa->get_question();
        $currentanswer = $qa->get_last_qt_var('answer');

        $inputname = $qa->get_qt_field_name('answer');

        $attributes = [
            'class' => 'answerinputfield',
            'name' => $inputname,
            'id' => $inputname,
            'aria-labelledby' => $inputname . '-label',
        ];

        if ($options->readonly) {
            $attributes['readonly'] = 'readonly';
        }

        $feedbackimg = '';
        if ($options->correctness) {
            list($fraction, $state, $gradestate, $correct) = $question->grade_response(['answer' => $currentanswer]);
            $attributes['class'] .= ' '.$this->feedback_class_custom($fraction, $state);
            $feedbackimg = $this->feedback_image_custom($fraction,  $state);
        }

        $htmlresponse = false;
        $questiontext = $question->format_questiontext($qa);
        $placeholder = false;

        $attributes['rows'] = 2;
        $attributes['cols'] = 50;

        if ($htmlresponse && $options->readonly) {
            $input = html_writer::tag('span', $currentanswer, $attributes) . $feedbackimg;
        } else {
            $input = html_writer::tag('textarea', $currentanswer, $attributes) . $feedbackimg;
        }

        $result = html_writer::start_tag('div', ['id' => 'latinai_questionblock']);

        $result .= $this->question_tests_link($question, $options);
        $result .= html_writer::tag('div', $questiontext, ['class' => 'qtext']);

        if (!$placeholder) {
            $result .= html_writer::start_tag('div', ['class' => 'ablock', 'id' => $inputname . '-label']);
            $result .= html_writer::tag('label', get_string('answercolon', 'qtype_numerical'), ['for' => $attributes['id']]);
            $result .= html_writer::tag('div', $input, ['class' => 'answer']);
            $result .= html_writer::end_tag('div');
        }

        $result .= html_writer::end_tag('div');

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag(
                'div',
                $question->get_validation_error(['answer' => $currentanswer]),
                ['class' => 'validationerror']
            );
        }

        return $result;
    }

    /**
     * Specific feedback
     *
     * @param  question_attempt $qa
     * @return string
     */
    public function specific_feedback(question_attempt $qa) {
        $question = $qa->get_question();

        $answer = $question->get_matching_answer(['answer' => $qa->get_last_qt_var('answer')]);
        if (!$answer || !$answer->feedback) {
            return '';
        }

        return $question->format_text(
            $answer->feedback,
            $answer->feedbackformat,
            $qa,
            'question',
            'answerfeedback',
            $answer->id
        );
    }

    /**
     * Correct response
     *
     * @param  question_attempt $qa
     * @return string
     */
    public function correct_response(question_attempt $qa) {
        return '';
    }

    /**
     * Displays a link to run the question tests, if applicable.
     *
     * @param qtype_latinai_question $question
     * @param question_display_options $options
     * @return string HTML fragment.
     */
    protected function question_tests_link(qtype_latinai_question $question, question_display_options $options) {
        if (!empty($options->suppressruntestslink)) {
            return '';
        }
        if (!$question->user_can_view()) {
            return '';
        }

        $link = html_writer::link(
            new moodle_url('/question/type/latinai/testquestion.php', ['id' => $question->id]),
            get_string('testthisquestion', 'qtype_latinai')
        );

        return html_writer::tag('div', $link, ['class' => 'questiontestslink']);
    }
}
