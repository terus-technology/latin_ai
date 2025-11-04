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
 * Upload question form
 *
 * @package    tool_uploadquestion
 * @copyright  2021 Terus e-Learning
 * @author     Khairu Aqsara <khairu@teruselearning.co.uk>, Muhamad Ramadhan <rama@teruselearning.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');

/**
 * Upload question form class
 */
class tool_uploadquestion_form extends moodleform {
    /**
     * Definition
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;
        $mform->addElement('header', 'generalhdr', get_string('questionsubtitle', 'tool_uploadquestion'));
        $mform->addElement(
            'filepicker',
            'questioncsvfile',
            get_string('questioncsvfile', 'tool_uploadquestion'),
            null,
            ['accepted_types' => 'csv', 'maxfiles' => 1]
        );
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

    /**
     * Reset
     *
     * @return void
     */
    public function reset() {
        $this->_form->updateSubmission(null, null);
    }
}
