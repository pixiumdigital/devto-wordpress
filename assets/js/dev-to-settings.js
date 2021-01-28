// Toggle the organization name dropdown
jQuery(function toggle_name_dropdown(){
    jQuery("#devto-type").change(function(){
        if (jQuery('#devto-type').val() == "me" ) {
            jQuery("tr.dev-to-username").addClass("hide");
        } else {
            jQuery("tr.dev-to-username").removeClass("hide");
        }
    });
});

//Set default dropdown state
jQuery(function set_dropdown_state(){
    var dev_to_type = jQuery('#devto-type').val()
    if(dev_to_type == "me") {
        jQuery("tr.dev-to-username").addClass("hide");
    }
});