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
 * Represents a Latin AI question.
 *
 * @package    qtype_latinai
 * @copyright  2021 Terus e-Learning
 * @author     Khairu Aqsara <khairu@teruselearning.co.uk>, Muhamad Ramadhan <rama@teruselearning.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\exception\moodle_exception;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/latinai/nonai.php');

/**
 * Represents a Latin AI question.
 */
class qtype_latinai_question extends question_graded_by_strategy implements question_response_answer_comparer {
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
     * @return void
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
     * Is gradable response
     *
     * @param  array $response
     * @return bool
     */
    public function is_gradable_response(array $response) {
        if (!array_key_exists('answer', $response) || ((!$response['answer']) && $response['answer'] !== '0')) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Is complete response
     *
     * @param  array $response
     * @return bool
     */
    public function is_complete_response(array $response) {
        if ($this->is_gradable_response($response)) {
            return (count($this->validate($response)) === 0);
        } else {
            return false;
        }
    }

    /**
     * Validate
     *
     * @param  array $response
     * @return array
     */
    protected function validate(array $response) {
        $responsevalidationerrors = [];

        if (!array_key_exists('answer', $response) || ((!$response['answer']) && $response['answer'] !== '0')) {
            return [get_string('pleaseenterananswer', 'qtype_latinai')];
        }

        return $responsevalidationerrors;
    }

    /**
     * Get validation error
     *
     * @param  array $response
     * @return string
     */
    public function get_validation_error(array $response) {
        $errors = $this->validate($response);
        if (count($errors) === 1) {
            return array_pop($errors);
        } else {
            $errorslist = html_writer::alist($errors);
            return get_string('errors', 'qtype_latinai', $errorslist);
        }
    }

    /**
     * Is same response
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
     * Grade response
     *
     * @param  array $response
     * @return array
     */
    public function grade_response(array $response) {
        $correctanswer = array_values($this->get_answers());
        $checkcomparation = $this->find_matching_answer($response);
        if ($checkcomparation) {
            $fraction = $checkcomparation['comparison_score'];
            $gradestate = '';
            if ($fraction >= 0.8) {
                $state = question_state::$gradedright;
                $gradestate = 'Right Answer';
            } else {
                $state = question_state::$gradedwrong;
                $gradestate = 'Wrong Answer';
            }
            return [$fraction, $state, $gradestate, $correctanswer[0]->answer];
        } else {
            throw new moodle_exception('qtypelatinaierrorservice', 'qtype_latinai');
        }
    }

    /**
     * Find matching answer
     *
     * @param  array $response
     * @return array
     */
    protected function find_matching_answer($response) {
        $config = get_config('qtype_latinai');
        $usenonai = $config->no_use_ai;
        if ($usenonai) {
            // Use Non AI Comparation.
            $correctanswer = array_values($this->get_answers());
            $fraction = [];
            foreach ($correctanswer as $answer) {
                $checkcomparation = $this->call_latin_ai_service($answer->answer, $response['answer']);
                if ($checkcomparation) {
                    $fraction[] = $checkcomparation['comparison_score'];
                }
            }

            $grade = (count($fraction) > 0) ? $fraction[0] : 0;
            return ['comparison_score' => $grade];
        } else {
            // Use AI Comparation.
            $correctanswer = array_values($this->get_answers());
            $arrcorrectanswer = [];
            foreach ($correctanswer as $answer) {
                $arrcorrectanswer[] = $answer->answer;
            }
            // Send an array to AI service.
            $checkcomparation = $this->call_latin_ai_service($arrcorrectanswer, $response['answer']);
            if ($checkcomparation) {
                $grade = number_format($checkcomparation['comparison_score'], 2);
            } else {
                $grade = 0;
            }

            return ['comparison_score' => $grade];
        }
    }

    /**
     * Call latin AI service
     *
     * @param  string $correctanswer
     * @param  string $givenanswer
     * @return void
     */
    public function call_latin_ai_service($correctanswer, $givenanswer) {
        $config = get_config('qtype_latinai');
        $url = $config->url;
        $apikey = $config->api_key;
        $usenonai = $config->no_use_ai;

        if ($usenonai) {
            $compare = new smith_waterman_gotoh();
            $perc = $compare->compare($correctanswer, $givenanswer);
            $perc = ($perc > 0) ? number_format($perc, 2) : 0;
            return ['comparison_score' => $perc];
        } else {
            $curl = curl_init();
            $data = [
                'submission' => $givenanswer,
                'exemplar' => $correctanswer,
            ];

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    "X-Api-Key: $apikey",
                ],
            ]);
            $response = curl_exec($curl);
            curl_close($curl);

            $response = json_decode($response, true);
            if (isset($response['status'])) {
                if ($response['status'] == 'success') {
                    return $response['payload'];
                }
            } else {
                return false;
            }
        }
    }

    /**
     * Get context
     *
     * @return \context
     */
    public function get_context() {
        return context::instance_by_id($this->contextid);
    }

    /**
     * Has question capability
     *
     * @param  string $type
     * @return bool
     */
    protected function has_question_capability($type) {
        global $USER;
        $context = $this->get_context();
        return has_capability("moodle/question:{$type}all", $context) ||
                ($USER->id == $this->createdby && has_capability("moodle/question:{$type}mine", $context));
    }

    /**
     * User can view
     *
     * @return bool
     */
    public function user_can_view() {
        return $this->has_question_capability('view');
    }

    /**
     * Compare response with answer
     *
     * @param  array $response
     * @param  question_answer $answer
     * @return void
     */
    public function compare_response_with_answer(array $response, question_answer $answer) {
        // TODO: Implement compare_response_with_answer() method.
    }
}
