/* eslint-disable */
import $ from 'jquery';
import DataTable from 'assets/datatables/datatables.min.js'

export const init = () => {
    window.console.log('Loaded Js File is loaded');
};

export const latin_data_table = () => {
    $("#table_latin_questionupload").DataTable();
}