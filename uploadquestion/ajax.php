<?php
header('Content-Type: application/json');

require(__DIR__ . '/../../../config.php');

function load_data_question()
{
    global $DB;
    $existing_sample_data = $DB->get_records('latin_question', null, 'id', '*');
    $arr = array();
    if($existing_sample_data){
        foreach($existing_sample_data as $value){
            $r = array(
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
            );
            array_push($arr, $r);
        }
    }

    $data = json_encode(['data' => $arr]);
    return $data;
}

echo load_data_question();
