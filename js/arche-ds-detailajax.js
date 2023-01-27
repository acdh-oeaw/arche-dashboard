
(function ($, Drupal) {
    'use strict';

    $(document).ready(function () {

        let categoryColumns = {
            properties: [{data: 'property'}, {data: 'count'}],
            classes: [{data: 'class'}, {data: 'count'}],
            classesproperties: [{data: 'class'}, {data: 'property'}, {data: 'cnt_distinct_value'}, {data: 'cnt'}],
            topcollections: [{data: 'id'}, {data: 'title'}, {data: 'count'}, {data: 'max_relatives'}, {data: 'sum_size'}, {data: 'binary_size'}],
            formats: [{data: 'format'}, {data: 'count_format'}, {data: 'count_rawbinarysize'}, {data: 'sum_size'}],
            formatspercollection: [{data: 'id'}, {data: 'title'}, {data: 'type'}, {data: 'format'}, {data: 'count'}, {data: 'sum_size'}],
        };

        let key = $('#dashboard-detail-ajax-key').val();

        var values_by_properties = $('#dashboard-detail-ajax').DataTable({
            paging: true,
            searching: true,
            pageLength: 10,
            processing: true,
            serverSide: true,
            responsive: true,
            bAutoWidth: false,
            serverMethod: "post",
            ajax: "/browser/dashboard-detail-api/" + key,
            columns: categoryColumns[key]
        });
    });
})(jQuery, Drupal);

