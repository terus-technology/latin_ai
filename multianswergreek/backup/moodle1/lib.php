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
 * Lib.
 *
 * @package    qtype_multianswergreek
 * @copyright  2021 Terus e-Learning
 * @author     Khairu Aqsara <khairu@teruselearning.co.uk>, Muhamad Ramadhan <rama@teruselearning.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * multianswergreek (aka embedded, cloze) question type conversion handler
 */
class moodle1_qtype_multianswergreek_handler extends moodle1_qtype_handler {

    /**
     * Get question subpaths
     *
     * @return array
     */
    public function get_question_subpaths() {
        return [
            'ANSWERS/ANSWER',
            'multianswergreekS/multianswergreek',
        ];
    }

    /**
     * Appends the multianswergreek specific information to the question
     *
     * Note that there is an upgrade step 2008050800 that is not replayed here as I suppose there
     * was an error on restore and the backup file contains correct data. If I'm wrong on this
     * assumption then the parent of the embedded questions could be fixed on conversion in theory
     * (by using a temporary stash that keeps multianswergreek's id and its sequence) but the category
     * fix would be tricky in XML.
     */
    public function process_question(array $data, array $raw) {
        // Convert and write the answers first.
        if (isset($data['answers'])) {
            $this->write_answers($data['answers'], $this->pluginname);
        }

        // Convert and write the multianswergreek extra fields.
        foreach ($data['multianswergreeks'] as $multianswergreeks) {
            foreach ($multianswergreeks as $multianswergreek) {
                $this->write_xml('multianswergreek', $multianswergreek, ['/multianswergreek/id']);
            }
        }
    }
}
