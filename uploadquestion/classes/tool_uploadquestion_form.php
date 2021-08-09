<?php
/**
 * File containing of the upload form.
 *
 * @package     tool_uploadquestion
 * @category    string
 * @copyright   2021 Terus e-Learning <khairu@teruselearning.co.uk>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');

class tool_uploadquestion_form extends moodleform {

    public function definition()
    {
        $mform = $this->_form;
        $mform->addElement('header', 'generalhdr', get_string('questionsubtitle', 'tool_uploadquestion'));
        $mform->addElement('filepicker', 'questioncsvfile', get_string('questioncsvfile', 'tool_uploadquestion'),null,array('accepted_types'=>'csv','maxfiles'=>1));
        $mform->addRule('questioncsvfile', null, 'required');

        $choices = csv_import_reader::get_delimiter_list();
        $mform->addElement('select', 'delimiter_name', get_string('csvdelimiter', 'tool_uploadquestion'), $choices);
        if (array_key_exists('cfg', $choices)) {
            $mform->setDefault('delimiter_name', 'cfg');
        } else if (get_string('listsep', 'langconfig') == ';') {
            $mform->setDefault('delimiter_name', 'semicolon');
        } else {
            $mform->setDefault('delimiter_name', 'comma');
        }
        $mform->addHelpButton('delimiter_name', 'csvdelimiter', 'tool_uploadquestion');

        $choices = core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'tool_uploadquestion'), $choices);
        $mform->setDefault('encoding', 'UTF-8');
        $mform->addHelpButton('encoding', 'encoding', 'tool_uploadquestion');

        $this->add_action_buttons(false, get_string('questiontbnupload', 'tool_uploadquestion'));
    }

    public function reset() {
        $this->_form->updateSubmission(null, null);
    }


}