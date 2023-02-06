
(function ($, Drupal) {
    'use strict';


    $( document ).ready(function() {
        let params = $('#params').val();
        params = params.replace("#", "%23");
        params = params.replace("#", "%23");
        params = params.replace("#", "%23");
        
        var values_by_properties = $('#dashboard-values-property-detail').DataTable({
            paging: true,
            searching: true,
            pageLength: 10,
            processing: true,
            serverSide: true,
            responsive: true,
            serverMethod: "post",
            ajax:  "/browser/dashboard-vbp-detail-api/"+params,
            'columns': [
                {data: 'id'},
                {data: 'title'}
            ]
        });
    });

})(jQuery, Drupal);


