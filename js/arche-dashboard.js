(function ($, Drupal) {
    'use strict';


    $( document ).ready(function() {
        $('.dashboard-desc-btn-hide').hide();
    });
    
    

    var table = $('table.display').DataTable({
        "lengthMenu": [[20, 35, 50, -1], [20, 35, 50, "All"]]
    });


    ////// Dissemination service datatable settings //////////
    function formatDisseminationServiceExtraInfo(d) {
        // `d` is the original data object for the row
        var str = '<table cellspacing="0" border="0" style="padding-left:50px; width: 100%!important;">' +
                '<tr>' +
                '<td width="150px">Description:</td>' +
                '<td>' + d.description + '</td>' +
                '</tr>' +
                '<tr>' +
                '<td width="150px">Formats:</td>' +
                '<td>' + d.formats + '</td>' +
                '</tr>' +
                '<tr>' +
                '<td width="150px">Return Type:</td>' +
                '<td>' + d.returnType + '</td>' +
                '</tr>' +
                '<tr>' +
                '<td width="150px">Location:</td>' +
                '<td>' + d.location + '</td>' +
                '</tr>' +
                '</table>';

        if (!($.isEmptyObject(d.dissParams))) {
            str += '<b>PARAMS</b>:';

            str += '<table cellspacing="0" border="0" style="padding-left:50px; width: 100%!important;">';
            $.each(d.dissParams, function (k, v) {
                str += '<tr>';
                str += '<td><b>Param: ' + k + '</b></td>';
                str += '</tr>';
                str += '<tr>';
                str += '<td width="150px">isPartOf:</td>';
                str += '<td>' + v.isPartOf + '</td>';
                str += '</tr>';
                str += '<tr>';
                str += '<td width="150px">defaultValue:</td>';
                str += '<td>' + v.defaultValue + '</td>';
                str += '</tr>';
            });
            str += '</table>';
        }

        if (!($.isEmptyObject(d.filterValues))) {
            str += '<b>Filters</b>:';

            str += '<table cellspacing="0" border="0" style="padding-left:50px; width: 100%!important;">';
            $.each(d.filterValues, function (k, v) {
                if (v.matchesProp || v.matchesValue || v.isRequired) {
                    str += '<tr>';
                    str += '<td><b>Filter ' + k + ':</b></td>';
                    str += '</tr>';
                    str += '<tr>';
                    str += '<td width="150px">matchesProp:</td>';
                    str += '<td>' + v.matchesProp + '</td>';
                    str += '</tr>';
                    str += '<tr>';
                    str += '<td width="150px">matchesValue:</td>';
                    str += '<td>' + v.matchesValue + '</td>';
                    str += '</tr>';
                    str += '<tr>';
                    str += '<td width="150px">matchesValue:</td>';
                    str += '<td>' + v.isRequired + '</td>';
                    str += '</tr>';
                }
            });
            str += '</table>';
        }

        return str;
    }

    var disserv_table = $('#dissserv-table').DataTable({
        "paging": true,
        "searching": true,
        "pageLength": 25,
        "info": true,
        "ajax": "/browser/dashboard-dissserv-api",
        "columns": [
            {
                "className": 'details-control',
                "orderable": false,
                "data": null,
                "defaultContent": '<i class="material-icons">add_circle</i>'
            },
            {"data": "url"},
            {"data": "title",
                "render": function (data, type, row, meta) {
                    return '<a href="/browser/dashboard-dissserv-detail/' + row.id + '">' + data + '</a>';
                }
            },
            {"data": "count"}
        ],
        "order": [[1, 'asc']]
    });

    // Add event listener for opening and closing details
    $('#dissserv-table tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = disserv_table.row(tr);

        if ($(this).text() == 'add_circle') {
            $(this).html('<i class="material-icons">remove_circle</i>');
        } else {
            $(this).html('<i class="material-icons">add_circle</i>');
        }

        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        } else {
            // Open this row
            row.child(formatDisseminationServiceExtraInfo(row.data())).show();
            tr.addClass('shown');
        }
    });

    ////// Dissemination service datatable settings  end //////////

    /// Dissmeination service matching resources datatable settings /////
    var dissId = $('#dissId').val();

    var disserv_matching_table = $('#dissserv-matching-table').DataTable({
        "paging": true,
        "searching": false,
        "pageLength": 10,
        "processing": true,
        "serverSide": true,
        "serverMethod": "post",
        "ajax": "/browser/dashboard-dissserv-matching-api/" + dissId,
        'columns': [
            {data: 'url'},
            {data: 'title',
                "render": function (data, type, row, meta) {
                    return '<a href="/browser/oeaw_detail/' + row.id + '">' + data + '</a>';
                }
            }
        ]
    });

    /// Dissmeination service matching resources datatable settings END /////


    $(document).delegate("a#getAttributesView", "click", function (e) {

        $('table.display-dashboard-detail').DataTable();

        $('html, body').animate({scrollTop: '0px'}, 0);
        var value = $(this).data('value');
        var property = $(this).data('property');

        if (property.indexOf('#') != -1) {
            property = property.replace('#', '%23');
        }

        $("#dashboard_property_details_table_div").slideDown("slow", function () {
            // Animation complete.
            $('#dashb_prop').html(property);
            $('#dashb_value').html(value);
        });

        $.ajax({
            url: '/browser/dashboard-detail-prop-api/' + property + '/' + value,
            type: "POST",
            success: function (data, status) {
                $('#dashboard_property_details_table').html(data);
                $('table.display.db-detail-view').DataTable();
            },
            error: function (message) {
                $('#dashboard_property_details_table').html("Resource does not exists!");
            }
        });
        e.preventDefault();
    });


    $('#values-by-property-table').hide();
     $(document).delegate("a#valuesByPropertyBtn", "click", function (e) {
        e.preventDefault();
        //we need to destroy the table to we can reinit a new selection with new data
        $('#values-by-property-table').DataTable().clear();
        $('#values-by-property-table').DataTable().destroy();
        $('#values-by-property-table').show();
        let rdf = $('#rdftype-list').val();
        let prop = $('#property-list').val();
        var values_by_properties = $('#values-by-property-table').DataTable({
            "paging": true,
            "searching": true,
            "pageLength": 10,
            "processing": true,
            "serverSide": true,
            "serverMethod": "post",
            "ajax": "/browser/dashboard-values-by-property-api/" + prop+"&"+rdf,
            'columns': [
                {data: 'property'},
                {data: 'key'},
                {data: 'count'}
                
            ]
        });
    });

    $('.dashboard-desc-btn-hide').on('click', function () {
        $('.dashboard-desc-btn-hide').hide();
        $('.dashboard-desc-btn-show').show();
        $('.dashboard-description').hide();
    });
    
    $('.dashboard-desc-btn-show').on('click', function () {
        $('.dashboard-desc-btn-show').hide();
        $('.dashboard-desc-btn-hide').show();
        $('.dashboard-description').show();
    });


})(jQuery, Drupal);

