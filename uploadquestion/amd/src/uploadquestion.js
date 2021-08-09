/* eslint-disable */
import $ from 'jquery';
import DataTable from 'tool_uploadquestion/datatables'

export const init = () => {
    window.console.log('Loaded Js File is loaded');
};

export const latin_data_table = () => {
    $("#table_latin_questionupload").DataTable();
}