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
 * Ajax
 *
 * @package    tool_uploadquestion
 * @copyright  2021 Terus e-Learning
 * @author     Khairu Aqsara <khairu@teruselearning.co.uk>, Muhamad Ramadhan <rama@teruselearning.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');
require_login();

/**
 * Load question
 *
 * @return string
 */
function load_data_question() {
    global $DB;

    $existingsampledata = $DB->get_records('latin_question', null, 'id', '*');
    $arr = [];
    if ($existingsampledata) {
        foreach ($existingsampledata as $value) {
            $r = [
                'latin' => $value->latin,
                'translation_1' => $value->translation_1,
                'translation_2' => $value->translation_2,
                'translation_3' => $value->translation_3,
                'translation_4' => $value->translation_4,
                'translation_5' => $value->translation_5,
                'translation_6' => $value->translation_6,
                'translation_7' => $value->translation_7,
                'translation_8' => $value->translation_8,
                'translation_9' => $value->translation_9,
                'translation_10' => $value->translation_10,
                'translation_11' => $value->translation_11,
            ];
            array_push($arr, $r);
        }
    }

    $data = json_encode(['data' => $arr]);
    return $data;
}

echo load_data_question();
