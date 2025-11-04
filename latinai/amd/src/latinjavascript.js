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

var baseUrl = M.cfg.wwwroot + '/question/type/latinai/assets/js/';

require.config({
    paths: {
        'datatables': baseUrl + 'datatables.jquery',
        'datatables.bootstrap': baseUrl + 'datatables.bootstrap5',
    }
});

define([
    'jquery',
    'core/modal_factory',
    'core/templates',
    'datatables',
    'datatables.bootstrap'
], function($, ModalFactory, Templates) {
    return {
        showModalQuestion: showModalQuestion,
        loadLatinQuestion: loadLatinQuestion
    };

    /**
     * Show modal question
     * @param {int} modalid
     */
    function showModalQuestion(modalid) {
        let mymodal = ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            large: true,
            title: 'Choose a Latin Question',
            body: Templates.render('qtype_latinai/modal_question', {modalid}),
        });

        $(document).on("click", "#choose_latin_btn", (event) => {
            event.preventDefault();
            mymodal
                .then(modal => modal.show())
                // eslint-disable-next-line no-console
                .catch(err => console.error('Failed to show modal:', err));
        });
    }

    /**
     * Show modal question
     * @param {int} modalid
     */
    function loadLatinQuestion(modalid) {
        $(`#${modalid}`).DataTable().clear().destroy();
        $(`#${modalid} tbody`).empty();

        const loadLatinQuestionOptions = {
            processing: true,
            ajax: M.cfg.wwwroot + "/admin/tool/uploadquestion/ajax.php",
            columns: [
                {"data": "latin"},
                {"data": "translation_1"},
            ]
        };

        let table = $(`#${modalid}`).DataTable(loadLatinQuestionOptions);

        $(`#${modalid} tbody`).on('click', 'tr', function() {
            $(`#${modalid} tbody tr`).removeClass('bg-primary text-white');
            $(this).addClass('bg-primary text-white');
            var pos = table.row(this).index();
            var row = table.row(pos).data();
            Y.one("#id_questiontexteditable").setHTML(row.latin);
            Y.one("#id_questiontexteditable").focus();
            for (let i = 0; i <= 10; i++) {
                $(`#id_answer_${i}`).val(row[`translation_${i + 1}`]);
            }
            $("#id_name").val(row.latin);
        });
    }
});
