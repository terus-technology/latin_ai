<?php

class upload_question_lib
{
    public static $valid_heading = array(
        'Latin','Translation 1','Translation 2','Translation 3','Translation 4','Translation 5','Translation 6',
        'Translation 7','Translation 8','Translation 9','Translation 10','Translation 11',
        'Checked','Id','Chapter','Section','Tags'
        );

    public static function validate_header($header_column, $returnurl)
    {
        if(sizeof($header_column) > 0){
            foreach($header_column as $col){
                if(!in_array($col, self::$valid_heading)){
                    print_error('invalidheader', 'tool_uploadquestion', $returnurl);
                }
            }
        }
    }

    public static function format_header($header)
    {
        $new_header = array();
        $exclude = array('Section','Id','Checked','Chapter','Tags');
        foreach($header as $item){
            if(!in_array($item, $exclude)) {
                $item = strtolower(str_replace(' ', '_', $item));
                $new_header[] = $item;
            }
        }
        return  $new_header;
    }

    public static function prepare_csv_data($content, $encoding, $delimiter_name, $enclosure='"')
    {
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_EXTRA);
        $content = core_text::convert($content, $encoding, 'utf-8');
        $content = core_text::trim_utf8_bom($content);
        $content = preg_replace('!\r\n?!', "\n", $content);
        // Remove any spaces or new lines at the end of the file.
        if ($delimiter_name == 'tab') {
            // trim() by default removes tabs from the end of content which is undesirable in a tab separated file.
            $content = trim($content, chr(0x20) . chr(0x0A) . chr(0x0D) . chr(0x00) . chr(0x0B));
        } else {
            $content = trim($content);
        }
        $csv_delimiter = csv_import_reader::get_delimiter($delimiter_name);
        $tempfile = tempnam(make_temp_directory('/csvimport'), 'tmp');
        if (!$fp = fopen($tempfile, 'w+b')) {
            @unlink($tempfile);
            return false;
        }
        fwrite($fp, $content);
        fseek($fp, 0);
        $columns = array();
        while ($fgetdata = fgetcsv($fp, 0, $csv_delimiter, $enclosure)) {
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

    public static function parse_line($data, $column) {
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_EXTRA);
        $result = array();
        if(sizeof($data) > 0){
            unset($data[0]);
            foreach($data as $rs){
                $arr = array();
                foreach($rs as $key=>$value){
                    $arr[$column[$key]] = $value;
                }
                array_push($result, (object) $arr);
            }
        }
        return $result;
    }
}