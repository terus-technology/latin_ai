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
 * Admin settings for the latinai question type.
 *
 * @package   qtype_latinai
 * @copyright  2021 Terus E-Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('qtype_latinai/url',
        get_string('latincomparasion', 'qtype_latinai'), get_string('configcomparationendpoint', 'qtype_latinai'),
        'http://latincomparison.pythonanywhere.com/comparenoauth', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('qtype_latinai/api_key',
        get_string('latincomparasionkey', 'qtype_latinai'), get_string('configcomparationendpointkey', 'qtype_latinai'),
        'L4t1n1234', PARAM_TEXT));

    $settings->add(new admin_setting_configcheckbox('qtype_latinai/no_use_ai',
        get_string('configcomparationnonai', 'qtype_latinai'), get_string('configcomparationnonai_desc', 'qtype_latinai'),
        false, PARAM_BOOL));

}
