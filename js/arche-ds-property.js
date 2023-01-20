
(function ($, Drupal) {
    'use strict';


    $( document ).ready(function() {
        let property = $('#dashboard-property').val();
        var values_by_properties = $('#dashboard-property-table').DataTable({
            paging: true,
            searching: true,
            pageLength: 10,
            processing: true,
            serverSide: true,
            responsive: true,
            serverMethod: "post",
            ajax:  "/browser/dashboard-property-api/"+property,
            'columns': [
                {data: 'title'},
                {data: 'type'},
                {data: 'key'},
                {data: 'cnt'}
            ]
        });
    });

})(jQuery, Drupal);

