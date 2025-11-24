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

define(['jquery', 'datatables', 'datatables.bootstrap'], function($) {
    return {
        init: init
    };

    /**
     * Init
     */
    function init() {
        $("#table_latin_questionupload").DataTable();
    }
});
