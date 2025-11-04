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

use core\exception\moodle_exception;

/**
 * Lib
 *
 * @package    tool_uploadquestion
 * @copyright  2021 Terus e-Learning
 * @author     Khairu Aqsara <khairu@teruselearning.co.uk>, Muhamad Ramadhan <rama@teruselearning.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class upload_question_lib {
    /**
     * @var array $validheading
     */
    public static $validheading = [
        'Latin',
        'Translation 1',
        'Translation 2',
        'Translation 3',
        'Translation 4',
        'Translation 5',
        'Translation 6',
        'Translation 7',
        'Translation 8',
        'Translation 9',
        'Translation 10',
        'Translation 11',
        'Checked',
        'Id',
        'Chapter',
        'Section',
        'Tags',
    ];

    /**
     * Validate header
     *
     * @param  array $headercolumn
     * @param  string $returnurl
     * @return void
     */
    public static function validate_header($headercolumn, $returnurl) {
        if (count($headercolumn) > 0) {
            foreach ($headercolumn as $col) {
                if (!in_array($col, self::$validheading)) {
                    throw new moodle_exception('invalidheader', 'tool_uploadquestion', $returnurl);
                }
            }
        }
    }

    /**
     * Format header
     *
     * @param  array $header
     * @return array
     */
    public static function format_header($header) {
        $newheader = [];
        $exclude = ['Section', 'Id', 'Checked', 'Chapter', 'Tags'];
        foreach ($header as $item) {
            if (!in_array($item, $exclude)) {
                $item = strtolower(str_replace(' ', '_', $item));
                $newheader[] = $item;
            }
        }
        return  $newheader;
    }

    /**
     * Prepare csv data
     *
     * @param  string $content
     * @param  string $encoding
     * @param  string $delimitername
     * @param  ?string $enclosure
     * @return array
     */
    public static function prepare_csv_data($content, $encoding, $delimitername, $enclosure='"') {
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_EXTRA);
        $content = core_text::convert($content, $encoding, 'utf-8');
        $content = core_text::trim_utf8_bom($content);
        $content = preg_replace('!\r\n?!', "\n", $content);
        // Remove any spaces or new lines at the end of the file.
        if ($delimitername == 'tab') {
            // The trim() by default removes tabs from the end of content which is undesirable in a tab separated file.
            $content = trim($content, chr(0x20) . chr(0x0A) . chr(0x0D) . chr(0x00) . chr(0x0B));
        } else {
            $content = trim($content);
        }
        $csvdelimiter = csv_import_reader::get_delimiter($delimitername);
        $tempfile = tempnam(make_temp_directory('/csvimport'), 'tmp');
        if (!$fp = fopen($tempfile, 'w+b')) {
            @unlink($tempfile);
            return false;
        }
        fwrite($fp, $content);
        fseek($fp, 0);
        $columns = [];
        while ($fgetdata = fgetcsv($fp, 0, $csvdelimiter, $enclosure)) {
            // Check to see if we have an empty line.
            if (count($fgetdata) == 1) {
                if ($fgetdata[0] !== null) {
                    // The element has data. Add it to the array.
                    unset($fgetdata[6]);
                    unset($fgetdata[7]);
                    unset($fgetdata[8]);
                    unset($fgetdata[9]);
                    unset($fgetdata[12]);
                    $columns[] = array_values($fgetdata);
                }
            } else {
                unset($fgetdata[6]);
                unset($fgetdata[7]);
                unset($fgetdata[8]);
                unset($fgetdata[9]);
                unset($fgetdata[12]);
                $columns[] = array_values($fgetdata);
            }
        }

        return $columns;
    }

    /**
     * Parse line
     *
     * @param  array $data
     * @param  string $column
     * @return array
     */
    public static function parse_line($data, $column) {
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_EXTRA);
        $result = [];
        if (count($data) > 0) {
            unset($data[0]);
            foreach ($data as $rs) {
                $arr = [];
                foreach ($rs as $key => $value) {
                    $arr[$column[$key]] = $value;
                }
                array_push($result, (object) $arr);
            }
        }
        return $result;
    }
}
