/* eslint-disable */

import $ from 'jquery';
import ModalFactory from 'core/modal_factory';
import Templates from 'core/templates';
import DataTable from '/question/type/latinai/assets/datatables/datatables.min.js'

export const showmodalquestion = (modalid) => {
    let mymodal = ModalFactory.create({
        type: ModalFactory.types.SAVE_CANCEL,
        large:true,
        title: 'Choose a Latin Question',
        body: Templates.render('qtype_latinai/modal_question', {modalid}),
    });

    $(document).on("click", "#choose_latin_btn",(event) => {
        event.preventDefault();
        mymodal.then(modal=>modal.show());
    });
}

export const loadlatinquestion = (modalid) => {

    $(`#${modalid}`).DataTable().clear().destroy();
    $(`#${modalid} tbody`).empty();

    const loadlatinquestionoptions = {
        processing: true,
        ajax: "/admin/tool/uploadquestion/ajax.php",
        columns: [
            { "data": "latin" },
            { "data": "translation_1" },
        ]
    };
    let oTable = $(`#${modalid}`).DataTable(loadlatinquestionoptions);

    $(`#${modalid} tbody`).on( 'click', 'tr', function() {
        $(`#${modalid} tbody tr`).removeClass('bg-primary text-white');
        $(this).addClass('bg-primary text-white');
        var pos = oTable.row(this).index();
        var row = oTable.row(pos).data();
        Y.one("#id_questiontexteditable").setHTML(row.latin);
        Y.one("#id_questiontexteditable").focus();
        for(let i=0; i<=10; i++){
            $(`#id_answer_${i}`).val(row[`translation_${i+1}`])
        }
        $("#id_name").val(row.latin);
    });

}