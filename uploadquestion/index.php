<?php

/**
 * Bulk latin question bank upload.
 *
 * @package     tool_uploadquestion
 * @copyright   2021 Terus e-Learning <khairu@teruselearning.co.uk>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
$PAGE->requires->js_call_amd('tool_uploadquestion/uploadquestion', 'latin_data_table');
$PAGE->requires->css('/admin/tool/uploadquestion/assets/datatables/datatables.min.css', true);

$mform = new tool_uploadquestion_form();
if ($form_data = $mform->get_data()) {
    $importid = csv_import_reader::get_new_iid('uploadquestion');
    $cir = new csv_import_reader($importid, 'uploadquestion');
    $content = $mform->get_file_content('questioncsvfile');
    $readcount = $cir->load_csv_content($content, $form_data->encoding, $form_data->delimiter_name);

    $pre_data = upload_question_lib::prepare_csv_data($content, $form_data->encoding, $form_data->delimiter_name);

    if ($readcount === false) {
        print_error('csvfileerror', 'tool_uploadquestion', $returnurl, $cir->get_error());
    } else if ($readcount == 0) {
        print_error('csvemptyfile', 'tool_uploadquestion', $returnurl, $cir->get_error());
    }

    upload_question_lib::validate_header($cir->get_columns(), $returnurl);
    $field = upload_question_lib::format_header($cir->get_columns());

    $data = upload_question_lib::parse_line($pre_data, $field);

    try{
        if($DB->delete_records('latin_question')) {
            $DB->insert_records('latin_question', $data);
        }
        redirect($PAGE->url, get_string('insertsuccess','tool_uploadquestion'), null, \core\output\notification::NOTIFY_SUCCESS);
    }catch (\Exception $e){
        redirect($PAGE->url, $e->getMessage(), null, \core\output\notification::NOTIFY_ERROR);
    }
}else{
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('questionformtitle', 'tool_uploadquestion'));
    $mform->display();

    echo "<br class='mt-4'/>";
    echo $OUTPUT->container_start();
    echo $OUTPUT->heading(get_string('questiondatatable', 'tool_uploadquestion'));
    $existing_sample_data = $DB->get_records('latin_question', null, 'id', '*');

    include($CFG->dirroot. '/admin/tool/uploadquestion/pages/lists.php');

    echo $OUTPUT->container_end();
    echo $OUTPUT->footer();
}