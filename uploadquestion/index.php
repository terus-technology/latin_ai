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
 * Bulk latin question bank upload.
 *
 * @package    tool_uploadquestion
 * @copyright  2021 Terus e-Learning
 * @author     Khairu Aqsara <khairu@teruselearning.co.uk>, Muhamad Ramadhan <rama@teruselearning.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\exception\moodle_exception;
use tool_uploadquestion\output\renderer;

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->dirroot. '/admin/tool/uploadquestion/classes/tool_uploadquestion_form.php');
require_once($CFG->dirroot. '/admin/tool/uploadquestion/lib.php');

admin_externalpage_setup('tooluploadquestion');
$context = context_system::instance();

$returnurl = new moodle_url('/admin/tool/uploadquestion/index.php');

$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
$PAGE->set_title(get_string('questionformtitle', 'tool_uploadquestion'));
$PAGE->requires->js_call_amd('tool_uploadquestion/uploadquestion', 'init');
$PAGE->requires->css('/admin/tool/uploadquestion/assets/css/datatables.bootstrap5.css', true);

$mform = new tool_uploadquestion_form();
if ($formdata = $mform->get_data()) {
    $importid = csv_import_reader::get_new_iid('uploadquestion');
    $cir = new csv_import_reader($importid, 'uploadquestion');
    $content = $mform->get_file_content('questioncsvfile');
    $readcount = $cir->load_csv_content($content, $formdata->encoding, $formdata->delimiter_name);

    $predata = upload_question_lib::prepare_csv_data($content, $formdata->encoding, $formdata->delimiter_name);

    if ($readcount === false) {
        throw new moodle_exception('csvfileerror', 'tool_uploadquestion', $returnurl, $cir->get_error());
    } else if ($readcount == 0) {
        throw new moodle_exception('csvemptyfile', 'tool_uploadquestion', $returnurl, $cir->get_error());
    }

    upload_question_lib::validate_header($cir->get_columns(), $returnurl);
    $field = upload_question_lib::format_header($cir->get_columns());

    $data = upload_question_lib::parse_line($predata, $field);

    try {
        if ($DB->delete_records('latin_question')) {
            $DB->insert_records('latin_question', $data);
        }
        redirect(
            $PAGE->url,
            get_string('insertsuccess', 'tool_uploadquestion'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    } catch (\Exception $e) {
        redirect($PAGE->url, $e->getMessage(), null, \core\output\notification::NOTIFY_ERROR);
    }
} else {
    echo $OUTPUT->header();
    $mform->display();

    echo "<br class='mt-4'/>";
    echo $OUTPUT->container_start();
    echo $OUTPUT->heading(get_string('questiondatatable', 'tool_uploadquestion'));

    $renderable = new renderer();
    echo $OUTPUT->render_from_template('tool_uploadquestion/lists', $renderable->export_for_template($OUTPUT));

    echo $OUTPUT->container_end();
    echo $OUTPUT->footer();
}
