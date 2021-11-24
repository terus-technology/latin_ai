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
 * Pattern-match question definition class.
 *
 * @package   qtype_latinai
 * @copyright 2021 Terus E-Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/latinai/nonai.php');

/**
 * Represents a Latin AI  question.
 *
 * @copyright 2021 Terus E-Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_latinai_question extends question_graded_by_strategy implements question_response_answer_comparer {

    /** @var array of question_answer. */
    public $answers = array();

    public function __construct() {
        parent::__construct(new question_first_matching_answer_grading_strategy($this));
    }

    public function get_expected_data() {
        return array('answer' => PARAM_RAW_TRIMMED);
    }

    public function summarise_response(array $response) {
        if (isset($response['answer'])) {
            return $response['answer'];
        } else {
            return null;
        }
    }

    public function is_gradable_response(array $response) {
        if (!array_key_exists('answer', $response) || ((!$response['answer']) && $response['answer'] !== '0')) {
            return false;
        } else {
            return true;
        }
    }

    public function is_complete_response(array $response) {
        if ($this->is_gradable_response($response)) {
            return (count($this->validate($response)) === 0);
        } else {
            return false;
        }
    }

    protected function validate(array $response) {
        $responsevalidationerrors = array();

        if (!array_key_exists('answer', $response) || ((!$response['answer']) && $response['answer'] !== '0')) {
            return array(get_string('pleaseenterananswer', 'qtype_latinai'));
        }

        return $responsevalidationerrors;
    }

    public function get_validation_error(array $response) {
        $errors = $this->validate($response);
        if (count($errors) === 1) {
            return array_pop($errors);
        } else {
            $errorslist = html_writer::alist($errors);
            return get_string('errors', 'qtype_latinai', $errorslist);
        }
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        return question_utils::arrays_same_at_key_missing_is_blank(
                $prevresponse, $newresponse, 'answer');
    }

    public function get_answers() {
        return $this->answers;
    }

    public function grade_response(array $response) {
        $correct_answer = array_values($this->get_answers());
        $check_comparation = $this->find_matching_answer($response);
        if($check_comparation) {
            $fraction = $check_comparation['comparison_score'];
            $grade_state = '';
            if ($fraction <= 0.5) {
                $state = question_state::$gradedwrong;
                $grade_state = 'Wrong Answer';
            } elseif ($fraction > 0.5 && $fraction <= 0.7) {
                $state = question_state::$gradedpartial;
                $grade_state = 'Partial Right Answer';
            } elseif ($fraction > 0.7) {
                $state = question_state::$gradedright;
                $grade_state = 'Right Answer';
            }
            return array($fraction, $state, $grade_state, $correct_answer[0]->answer);
        }else{
            print_error('qtypelatinaierrorservice', 'qtype_latinai');
        }
    }

    protected function find_matching_answer($response)
    {
        $config = get_config('qtype_latinai');
        $use_non_ai = $config->no_use_ai;
        if($use_non_ai) {
            // Use Non Ai Comparation
            $correct_answer = array_values($this->get_answers());
            $fraction = [];
            foreach($correct_answer as $answer){
                $check_comparation = $this->call_latin_ai_service($answer->answer, $response['answer']);
                if($check_comparation) {
                    $fraction[] = $check_comparation['comparison_score'];
                }
            }

            $grade = (sizeof($fraction) >0) ? $fraction[0] : 0;
            return array('comparison_score' => $grade);
        }else{
            // Use AI Comparation
            $correct_answer = array_values($this->get_answers());
            $arr_correct_answer = [];
            foreach($correct_answer as $answer){
                $arr_correct_answer[] = $answer->answer;
            }
            // send an array to AI Service
            $check_comparation = $this->call_latin_ai_service($arr_correct_answer, $response['answer']);
            if($check_comparation) {
                $grade = number_format($check_comparation['comparison_score'], 2);
            }else{
                $grade = 0;
            }

            return array('comparison_score' => $grade);
        }
    }

    public function call_latin_ai_service($correct_answer,$given_answer) {
        $config = get_config('qtype_latinai');
        $url = $config->url;
        $api_key = $config->api_key;
        $use_non_ai = $config->no_use_ai;

        if($use_non_ai) {
            $compare = new SmithWatermanGotoh();
            $perc = $compare->compare($correct_answer, $given_answer);
            $perc = ($perc > 0) ? number_format($perc, 2) : 0;
            return array('comparison_score' => $perc);
        }else{
            $curl = curl_init();
            $data = array(
                'submission' => $given_answer,
                'exemplar' => $correct_answer
            );

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    "X-Api-key: $api_key"
                ),
            ));
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

    public function get_context() {
        return context::instance_by_id($this->contextid);
    }

    protected function has_question_capability($type) {
        global $USER;
        $context = $this->get_context();
        return has_capability("moodle/question:{$type}all", $context) ||
                ($USER->id == $this->createdby && has_capability("moodle/question:{$type}mine", $context));
    }

    public function user_can_view() {
        return $this->has_question_capability('view');
    }

    public function compare_response_with_answer(array $response, question_answer $answer)
    {
        // TODO: Implement compare_response_with_answer() method.
    }
}