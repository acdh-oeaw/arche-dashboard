(function ($, Drupal) {
    'use strict';
    
    var table = $('table.display').DataTable({
       "lengthMenu": [[20, 35, 50, -1], [20, 35, 50, "All"]]
    });
    
    $(document ).delegate( "a#getAttributesView", "click", function(e) {
        
        $('table.display-dashboard-detail').DataTable();
        
        $('html, body').animate({scrollTop: '0px'}, 0);
        var value = $(this).data('value');
        var property = $(this).data('property');
        
        if (property.indexOf('#') != -1) {
            property = property.replace('#', '%23');
        }
        
        $( "#dashboard_property_details_table_div" ).slideDown( "slow", function() {
            // Animation complete.
            $('#dashb_prop').html(property);
            $('#dashb_value').html(value);
        });
        
        $.ajax({
            url: '/browser/dashboard-detail-api/'+property+'/'+value,
            type: "POST",
            success: function(data, status) {
                $('#dashboard_property_details_table').html(data);
                 $('table.display.db-detail-view').DataTable(); 
            },
            error: function(message) {
                $('#dashboard_property_details_table').html("Resource does not exists!");
            }
        });
        e.preventDefault();
    });
    
})(jQuery, Drupal);
 
