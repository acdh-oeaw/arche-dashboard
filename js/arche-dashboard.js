(function ($, Drupal) {
    'use strict';

    var table = $('table.display').DataTable({
        "lengthMenu": [[20, 35, 50, -1], [20, 35, 50, "All"]]
    });


    ////// Dissemination service datatable settings //////////
    function formatDisseminationServiceExtraInfo(d) {
        // `d` is the original data object for the row
        return '<table cellspacing="0" border="0" style="padding-left:50px; width: 100%!important;">' +
                '<tr>' +
                '<td>Description:</td>' +
                '<td>' + d.hasDescription + '</td>' +
                '</tr>' +
                '<tr>' +
                '<td>Binary Size:</td>' +
                '<td>' + d.hasBinarySize + '</td>' +
                '</tr>' +
                '<tr>' +                
                '<td>Return Type:</td>' +
                '<td>' + d.hasReturnType + '</td>' +
                '</tr>' +
                '<tr>' +
                '<td>ServiceLocation:</td>' +
                '<td>' + d.serviceLocation + '</td>' +
                '</tr>' +
                '<tr>' +
                '<td>NumberOfItems:</td>' +
                '<td>' + d.hasNumberOfItems + '</td>' +
                '</tr>' +
                '</table>';
    }
    
    var disserv_table = $('#dissserv-table').DataTable( {   
        "paging": true,
        "searching": true,
        "info": true,
        "ajax": "/browser/dashboard-dissserv-api",
        "columns": [
             {
                "className":      'details-control',
                "orderable":      false,
                "data":           null,
                "defaultContent": '<i class="material-icons">add_circle</i>'
            },
            { "data": "uri" },
            { "data": "hasTitle" },            
            { "data": "key" }
        ],
        "order": [[1, 'asc']]
    });

    // Add event listener for opening and closing details
    $('#dissserv-table tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = disserv_table.row(tr);
        
        if($(this).text() == 'add_circle') {
            $(this).html('<i class="material-icons">remove_circle</i>');
        }else {
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
            url: '/browser/dashboard-detail-api/' + property + '/' + value,
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

})(jQuery, Drupal);

