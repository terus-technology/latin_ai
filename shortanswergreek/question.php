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
 * Short answer question definition class.
 *
 * @package    qtype_shortanswergreek
 * @copyright  2021 Terus e-Learning
 * @author     Khairu Aqsara <khairu@teruselearning.co.uk>, Muhamad Ramadhan <rama@teruselearning.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/questionbase.php');

/**
 * Represents a short answer question.
 */
class qtype_shortanswergreek_question extends question_graded_by_strategy implements question_response_answer_comparer {
    /** @var bool whether answers should be graded case-sensitively. */
    public $usecase;
    /** @var array of question_answer. */
    public $answers = [];

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct() {
        parent::__construct(new question_first_matching_answer_grading_strategy($this));
    }

    /**
     * Get expected data
     *
     * @return array
     */
    public function get_expected_data() {
        return ['answer' => PARAM_RAW_TRIMMED];
    }

    /**
     * Summarise response
     *
     * @param  array $response
     * @return ?string
     */
    public function summarise_response(array $response) {
        if (isset($response['answer'])) {
            return $response['answer'];
        } else {
            return null;
        }
    }

    /**
     * Unsummarise response
     *
     * @param  string $summary
     * @return ?array
     */
    public function un_summarise_response(string $summary) {
        if (!empty($summary)) {
            return ['answer' => $summary];
        } else {
            return [];
        }
    }

    /**
     * Check if response is completed
     *
     * @param  array $response
     * @return bool
     */
    public function is_complete_response(array $response) {
        return array_key_exists('answer', $response) && ($response['answer'] || $response['answer'] === '0');
    }

    /**
     * Get validation error
     *
     * @param  array $response
     * @return string
     */
    public function get_validation_error(array $response) {
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string('pleaseenterananswer', 'qtype_shortanswergreek');
    }

    /**
     * Check if response is the same
     *
     * @param  array $prevresponse
     * @param  array $newresponse
     * @return bool
     */
    public function is_same_response(array $prevresponse, array $newresponse) {
        return question_utils::arrays_same_at_key_missing_is_blank($prevresponse, $newresponse, 'answer');
    }

    /**
     * Get answers
     *
     * @return array
     */
    public function get_answers() {
        return $this->answers;
    }

    /**
     * Compare response with answer
     *
     * @param  array $response
     * @param  question_answer $answer
     * @return bool
     */
    public function compare_response_with_answer(array $response, question_answer $answer) {
        if (!array_key_exists('answer', $response) || is_null($response['answer'])) {
            return false;
        }

        return self::compare_string_with_wildcard($response['answer'], $answer->answer, !$this->usecase);
    }

    /**
     * Compare string with wildcard
     *
     * @param  string $string
     * @param  string $pattern
     * @param  bool $ignorecase
     * @return bool
     */
    public static function compare_string_with_wildcard($string, $pattern, $ignorecase) {
        // Normalise any non-canonical UTF-8 characters before we start.
        $pattern = self::safe_normalize($pattern);
        $string = self::safe_normalize($string);

        // Break the string on non-escaped runs of asterisks.
        // ** is equivalent to *, but people were doing that, and with many *s it breaks preg.
        $bits = preg_split('/(?<!\\\\)\*+/', $pattern);

        // Escape regexp special characters in the bits.
        $escapedbits = [];
        foreach ($bits as $bit) {
            $escapedbits[] = preg_quote(str_replace('\*', '*', $bit), '|');
        }
        // Put it back together to make the regexp.
        $regexp = '|^' . implode('.*', $escapedbits) . '$|u';

        // Make the match insensitive if requested to.
        if ($ignorecase) {
            $regexp .= 'i';
        }

        return preg_match($regexp, trim($string));
    }

    /**
     * Normalise a UTf-8 string to FORM_C, avoiding the pitfalls in PHP's normalizer_normalize function.
     *
     * @param string $string the input string.
     * @return string the normalised string.
     */
    protected static function safe_normalize($string) {
        if ($string === '') {
            return '';
        }

        if (!function_exists('normalizer_normalize')) {
            return $string;
        }

        $normalised = normalizer_normalize($string, Normalizer::FORM_C);
        if (is_null($normalised)) {
            // An error occurred in normalizer_normalize, but we have no idea what.
            debugging('Failed to normalise string: ' . $string, DEBUG_DEVELOPER);
            return $string; // Return the original string, since it is the best we have.
        }

        return $normalised;
    }

    /**
     * Get correct response
     *
     * @return array
     */
    public function get_correct_response() {
        $response = parent::get_correct_response();
        if ($response) {
            $response['answer'] = $this->clean_response($response['answer']);
        }
        return $response;
    }

    /**
     * Clean response
     *
     * @param  string $answer
     * @return string
     */
    public function clean_response($answer) {
        // Break the string on non-escaped asterisks.
        $bits = preg_split('/(?<!\\\\)\*/', $answer);

        // Unescape *s in the bits.
        $cleanbits = [];
        foreach ($bits as $bit) {
            $cleanbits[] = str_replace('\*', '*', $bit);
        }

        // Put it back together with spaces to look nice.
        return trim(implode(' ', $cleanbits));
    }

    /**
     * Check file access
     *
     * @param  question_answer $qa
     * @param  object $options
     * @param  string $component
     * @param  string $filearea
     * @param  array $args
     * @param  bool $forcedownload
     * @return bool
     */
    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        if ($component == 'question' && $filearea == 'answerfeedback') {
            $currentanswer = $qa->get_last_qt_var('answer');
            $answer = $this->get_matching_answer(['answer' => $currentanswer]);
            $answerid = reset($args); // Itemid is answer id.
            return $options->feedback && $answer && $answerid == $answer->id;

        } else if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);

        } else {
            return parent::check_file_access($qa, $options, $component, $filearea, $args, $forcedownload);
        }
    }
}
