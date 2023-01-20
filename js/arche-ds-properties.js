
(function ($, Drupal) {
    'use strict';

    $( document ).ready(function() {
        let property = $('#dashboard-properties').val();
        var values_by_properties = $('#dashboard-properties-table').DataTable({
            "paging": true,
            "searching": true,
            "pageLength": 10,
            "processing": true,
            "serverSide": true,
            "serverMethod": "post",
            ajax:  "/browser/dashboard-properties-api/"+property,
            'columns': [
                {data: 'title'},
                {data: 'type'},
                {data: 'key'},
                {data: 'cnt'}
            ]
        });
    });

})(jQuery, Drupal);

