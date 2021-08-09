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
 * This is a quick and dirty script to test a question against a responses
 *
 *
 * @package   qtype_latinai
 * @copyright 2021 Terus E-Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->libdir . '/formslib.php');


/**
 * The upload form.
 *
 * @copyright 2021 Terus E-Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_latinai_test_form extends moodleform {
    protected function definition() {
        $this->_form->addElement('header', 'header', 'Latin AI Question Test');

        $this->_form->addElement('textarea', 'answer', 'Question Answer', array('rows' => '4', 'cols' => '60', 'style' => 'width:100%'));
        $this->_form->addRule('answer', null, 'required', null, 'client');

        $this->_form->addElement('hidden', 'id', 0);
        $this->_form->setType('id', PARAM_INT);

        $this->_form->addElement('submit', 'submitbutton', get_string('testquestionformsubmit', 'qtype_latinai'));
    }
}


$questionid = required_param('id', PARAM_INT);

$questiondata = $DB->get_record('question', array('id' => $questionid), '*', MUST_EXIST);
if ($questiondata->qtype != 'latinai') {
    throw new coding_exception('That is not a Latin AI question.');
}

require_login();
question_require_capability_on($questiondata, 'view');
$canedit = question_has_capability_on($questiondata, 'edit');

$question = question_bank::load_question($questionid);
$context = context::instance_by_id($question->contextid);

$PAGE->set_url('/question/type/latinai/testquestion.php', array('id' => $questionid));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('testquestionformtitle', 'qtype_latinai'));
$PAGE->set_heading(get_string('testquestionformtitle', 'qtype_latinai'));

$table = null;
$form = new qtype_latinai_test_form($PAGE->url);
$form->set_data(array('id' => $questionid));

if ($fromform = $form->get_data()) {
    $response = $fromform->answer;
    $table = new html_table();
    $table->head = array('Question','Correct Answer','Given Answer','Grade');
    list($fraction, $state, $grade_state,$correct) = $question->grade_response(array('answer' => $response));
    $table->data[] = array(format_string($questiondata->name), $correct, $response, $fraction);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('testquestionheader', 'qtype_latinai', format_string($questiondata->name)));
echo '<p>' . $PAGE->get_renderer('core_question')->question_preview_link(
        $question->id, $context, true) . '</p>';
$form->display();

if ($table) {
    echo $OUTPUT->heading('Test Result');
    echo html_writer::table($table);
}

echo $OUTPUT->footer();
