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
 * Renderer
 *
 * @package    tool_uploadquestion
 * @copyright  2021 Terus e-Learning
 * @author     Khairu Aqsara <khairu@teruselearning.co.uk>, Muhamad Ramadhan <rama@teruselearning.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_uploadquestion\output;

defined('MOODLE_INTERNAL') || die();

use core\output\renderable;
use core\output\renderer_base;
use core\output\templatable;

/**
 * Renderer class
 */
class renderer implements renderable, templatable {
    /**
     * Export for template
     *
     * @param  renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        global $DB;

        $latinquestions = $DB->get_records('latin_question', null, 'id', '*');

        $context = [
            'imageempty' => new \moodle_url('/admin/tool/uploadquestion/assets/images/empty.svg'),
        ];

        if (!empty($latinquestions)) {
            $context['latinquestions'] = array_map(function($latinquestion) {
                $translations = [];
                for ($i = 1; $i <= 11; $i++) {
                    if (!empty($latinquestion->{"translation_$i"})) {
                        $translations[] = $latinquestion->{"translation_$i"};
                    }
                }
                return [
                    'latin' => $latinquestion->latin,
                    'translations' => $translations,
                ];
            }, $latinquestions);
            $context['latinquestions'] = array_values($context['latinquestions']);
        }

        $context['is_exists'] = !empty($latinquestions);

        return $context;
    }
}
