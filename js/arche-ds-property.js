
(function ($, Drupal) {
    'use strict';

    $( document ).ready(function() {
        $('#dashboard-property-table').DataTable().clear();
        $('#dashboard-property-table').DataTable().destroy();
        $('#dashboard-property-table').show();
        
        let property = $('#dashboard-property').val();
        
        var values_by_properties = $('#dashboard-property-table').DataTable({
            "paging": true,
            "searching": true,
            "pageLength": 10,
            "processing": true,
            "serverSide": true,
            "serverMethod": "post",
            "ajax": "/browser/dashboard-by-property-api/" + property,
            'columns': [
                {data: 'title'},
                {data: 'type'},
                {data: 'key'},
                {data: 'count'}
                
            ]
        });
    });
    



})(jQuery, Drupal);

