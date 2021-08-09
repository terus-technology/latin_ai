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
 * @package   qtype_latinai
 * @copyright 2021 Terus E-Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class qtype_latinai_edit_form extends question_edit_form {

    public function definition() {
        global $COURSE, $CFG, $DB, $PAGE;

        $modalid = md5(date('ymhis'));
        $PAGE->requires->js_call_amd('qtype_latinai/latinjavascript', 'showmodalquestion', array($modalid));
        $PAGE->requires->css('/question/type/latinai/assets/datatables/datatables.min.css', true);

        $qtype = $this->qtype();
        $langfile = "qtype_{$qtype}";

        $mform = $this->_form;

        // Standard fields at the start of the form.
        $mform->addElement('header', 'generalheader', get_string("general", 'form'));

        if (!isset($this->question->id)) {
            if (!empty($this->question->formoptions->mustbeusable)) {
                $contexts = $this->contexts->having_add_and_use();
            } else {
                $contexts = $this->contexts->having_cap('moodle/question:add');
            }

            // Adding question.
            $mform->addElement('questioncategory', 'category', get_string('category', 'question'),
                array('contexts' => $contexts));
        } else if (!($this->question->formoptions->canmove ||
            $this->question->formoptions->cansaveasnew)) {
            // Editing question with no permission to move from category.
            $mform->addElement('questioncategory', 'category', get_string('category', 'question'),
                array('contexts' => array($this->categorycontext)));
            $mform->addElement('hidden', 'usecurrentcat', 1);
            $mform->setType('usecurrentcat', PARAM_BOOL);
            $mform->setConstant('usecurrentcat', 1);
        } else {
            // Editing question with permission to move from category or save as new q.
            $currentgrp = array();
            $currentgrp[0] = $mform->createElement('questioncategory', 'category',
                get_string('categorycurrent', 'question'),
                array('contexts' => array($this->categorycontext)));
            if ($this->question->formoptions->canedit ||
                $this->question->formoptions->cansaveasnew) {
                // Not move only form.
                $currentgrp[1] = $mform->createElement('checkbox', 'usecurrentcat', '',
                    get_string('categorycurrentuse', 'question'));
                $mform->setDefault('usecurrentcat', 1);
            }
            $currentgrp[0]->freeze();
            $currentgrp[0]->setPersistantFreeze(false);
            $mform->addGroup($currentgrp, 'currentgrp',
                get_string('categorycurrent', 'question'), null, false);

            $mform->addElement('questioncategory', 'categorymoveto',
                get_string('categorymoveto', 'question'),
                array('contexts' => array($this->categorycontext)));
            if ($this->question->formoptions->canedit ||
                $this->question->formoptions->cansaveasnew) {
                // Not move only form.
                $mform->disabledIf('categorymoveto', 'usecurrentcat', 'checked');
            }
        }

        $mform->addElement('static', 'choosequestion', 'Choose Latin Question',
            html_writer::tag('button', 'Select Available Question', array('id' => 'choose_latin_btn', 'class' => 'btn btn-sm btn-primary')));

        $mform->addElement('text', 'name', get_string('questionname', 'question'),
            array('size' => 50, 'maxlength' => 255));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('editor', 'questiontext', get_string('questiontext', 'question'),array('rows' => 15), $this->editoroptions);
        //$mform->createElement('textarea', 'questiontext', get_string('questiontext', 'question'), array('rows' => '4', 'cols' => '60', 'style' => 'width:100%'));
        $mform->setType('questiontext', PARAM_RAW);
        $mform->addRule('questiontext', null, 'required', null, 'client');

        $mform->addElement('float', 'defaultmark', get_string('defaultmark', 'question'),
            array('size' => 7));
        $mform->setDefault('defaultmark', 1);
        $mform->addRule('defaultmark', null, 'required', null, 'client');

        $mform->addElement('editor', 'generalfeedback', get_string('generalfeedback', 'question'),
            array('rows' => 10), $this->editoroptions);
        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->addHelpButton('generalfeedback', 'generalfeedback', 'question');

        $mform->addElement('text', 'idnumber', get_string('idnumber', 'question'), 'maxlength="100"  size="10"');
        $mform->addHelpButton('idnumber', 'idnumber', 'question');
        $mform->setType('idnumber', PARAM_RAW);

        // Any questiontype specific fields.
        $this->definition_inner($mform);

        if (core_tag_tag::is_enabled('core_question', 'question')) {
            $this->add_tag_fields($mform);
        }

        if (!empty($this->question->id)) {
            $mform->addElement('header', 'createdmodifiedheader',
                get_string('createdmodifiedheader', 'question'));
            $a = new stdClass();
            if (!empty($this->question->createdby)) {
                $a->time = userdate($this->question->timecreated);
                $a->user = fullname($DB->get_record(
                    'user', array('id' => $this->question->createdby)));
            } else {
                $a->time = get_string('unknown', 'question');
                $a->user = get_string('unknown', 'question');
            }
            $mform->addElement('static', 'created', get_string('created', 'question'),
                get_string('byandon', 'question', $a));
            if (!empty($this->question->modifiedby)) {
                $a = new stdClass();
                $a->time = userdate($this->question->timemodified);
                $a->user = fullname($DB->get_record(
                    'user', array('id' => $this->question->modifiedby)));
                $mform->addElement('static', 'modified', get_string('modified', 'question'),
                    get_string('byandon', 'question', $a));
            }
        }

        $this->add_hidden_fields();

        $mform->addElement('hidden', 'qtype');
        $mform->setType('qtype', PARAM_ALPHA);

        $mform->addElement('hidden', 'makecopy');
        $mform->setType('makecopy', PARAM_INT);

        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'updatebutton',
            get_string('savechangesandcontinueediting', 'question'));
        if ($this->can_preview()) {
            $previewlink = $PAGE->get_renderer('core_question')->question_preview_link(
                $this->question->id, $this->context, true);
            $buttonarray[] = $mform->createElement('static', 'previewlink', '', $previewlink);
        }

        $mform->addGroup($buttonarray, 'updatebuttonar', '', array(' '), false);
        $mform->closeHeaderBefore('updatebuttonar');

        $this->add_action_buttons(true, get_string('savechanges'));

        if ((!empty($this->question->id)) && (!($this->question->formoptions->canedit ||
                $this->question->formoptions->cansaveasnew))) {
            $mform->hardFreezeAllVisibleExcept(array('categorymoveto', 'buttonar', 'currentgrp'));
        }
    }

    protected function definition_inner($mform) {
        $this->add_per_answer_fields($mform, get_string('answerno', 'qtype_latinai', '{no}'), question_bank::fraction_options());
        $this->add_interactive_settings();
    }

    protected function add_per_answer_fields(&$mform, $label, $gradeoptions, $minoptions = 11, $addoptions = QUESTION_NUMANS_ADD){

        parent::add_per_answer_fields($mform, $label, $gradeoptions, $minoptions,$addoptions);
        $answersinstruct = $mform->createElement('static', 'answersinstruct',
            get_string('correctanswers', 'qtype_latinai'),
            get_string('filloutoneanswer', 'qtype_latinai'));
        $mform->insertElementBefore($answersinstruct, 'answer[0]');
    }

    protected function get_more_choices_string() {
        return get_string('addmoreanswerblanks', 'qtype_latinai');
    }

    protected function get_per_answer_fields($mform, $label, $gradeoptions, &$repeatedoptions, &$answersoption) {
        $repeated = array();
        $repeated[] = $mform->createElement('textarea', 'answer', $label, array('rows' => '4', 'cols' => '60', 'style' => 'width:100%'));
        $repeated[] = $mform->createElement('select', 'fraction',get_string('grade'), $gradeoptions);
        $repeated[] = $mform->createElement('editor', 'feedback', get_string('feedback', 'question'), array('rows' => 5), $this->editoroptions);

        $repeatedoptions['answer']['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption = 'answers';
        return $repeated;
    }

    public function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question);
        return $question;
    }

    public function qtype() {
        return 'latinai';
    }
}
