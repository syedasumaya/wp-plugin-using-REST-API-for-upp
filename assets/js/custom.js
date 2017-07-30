jQuery(function ($) {
    
   //validation while adding data 
    var v = $("#upp-add-form").validate({
         rules: {
            title: {
                required: true
            }
        },
        messages: {
            title: 'Name is required!'
        },
        errorElement: "span",
        errorClass: "help-inline-error"
    });
    $(".rs-add-upp-form").on("click", function () { 
       if (v.form()) { 
           $('#save-upp-form').trigger("click");
       }
    });
    
    //validation while editing data
    var w = $("#upp-edit-form").validate({
         rules: {
            title: {
                required: true
            }
        },
        messages: {
            title: 'Name is required!'
        },
        errorElement: "span",
        errorClass: "help-inline-error"
    });
    $(".rs-edit-upp-form").on("click", function () { 
       if (w.form()) {
            $('#edit-upp-form').trigger("click");
       }
    });
    
    //validation while connecting with upp
    var x = $("#connect-upp-form").validate({
         rules: {
            apiKey: {
                required: true
            }
        },
        messages: {
            apiKey: 'API key is required!',
        },
        errorElement: "span",
        errorClass: "help-inline-error"
    });
    $(".connect-upp-btn").on("click", function () {
       if (x.form()) {
           $('#connect-upp-form-btn').trigger("click");
       }
    });
    
    //call this function for accordion and sortable
    uppAccordionSortable();
   
});

function generateCustomFieldForm() {
    var click = jQuery("#count_field").val();
    click++;
    jQuery("#count_field").val(click);
    var html = '';
    html += '<div class="group' + click + '">\n\
             <h3><a href="#" id="heading' + click + '">Custom field</a></h3>\n\
             <div class="draggable' + click + '">\n\
             <input onkeyup="customfieldLabel(this.value)" name="customfields[customfieldlabel][]" id="input-label' + click + '" type="text" value="" size="40" placeholder="Type label name">\n\
             <label for="label-name" class="label-name' + click + '"><b>Field Type</b></label><select name="customfields[customfieldType][]"><option value="text">Text</option><option value="number">Number</option><option value="textarea">Textarea</option><option value="checkbox">Checkbox</option><option value="radio">Radio</option><option value="select">Select</option></select>\n\
             <label for="label-name" class="label-name' + click + '"><b>Feild value Format<span style="font-size:12px;"> ( except radio,checkbox or select please keep empty this field )</span></b></label>\n\
             <textarea name="customfields[customfieldValue][]" placeholder="Multiple value format for radio, checkbox and select field : a|b|c|d"></textarea>\n\
             \n\
             <input name="customfields[customfieldIdentifier][]" id="tag-slug" type="text" value="" size="40" placeholder="Type Upp Identifier...">\n\
             <input name="customfields[customHiddenfieldValue][]" id="input-value' + click + '" type="hidden" value="" size="40" placeholder="Type Value...">\n\
             <input onclick="makeHidden(' + click + ')" type="checkbox" id="make_hidden' + click + '" name="customfields[make_hidden][]" value="0"> Make it hidden<br>\n\
             <a onclick="removeCustomfield(' + click + ')">Remove</a></div></div>';
    jQuery("#accordion").append(html);
    uppAccordionSortable();


}

function removeCustomfield(value){ 
    jQuery('.group'+value).remove();
}

function makeDisable(value) {

    var status = jQuery("#disable" + value).attr('status');
    
    if (status == 'disable') {
        jQuery("#defaultFieldStatus_" + value).val('disable');
        jQuery("#disable" + value).attr('status','enable');
        jQuery("#disable" + value).hide();
        jQuery("#enable" + value).show();
        jQuery(".group"+value+" h3").css({"opacity" : "0.5","pointer-events" : "none"});
        jQuery(".draggable"+value).css({"opacity" : "0.5","pointer-events" : "none"});
        
         jQuery(".accordion")
            .accordion({
                active: false
            })
    } else {
        jQuery("#defaultFieldStatus_" + value).val('enable');
        jQuery("#disable" + value).attr('status','disable');
        jQuery("#disable" + value).show();
        jQuery("#enable" + value).hide();
        jQuery(".group"+value+" h3").css({"opacity" : "1","pointer-events" : "visible"});
        jQuery(".draggable"+value).css({"opacity" : "1","pointer-events" : "visible"});
       
    }
}


function customfieldLabel(value) {
    var click = jQuery("#count_field").val();
    if (value == '') {
        jQuery("#heading" + click).text('Custom field');
    } else {
        jQuery("#heading" + click).text(value);
    }
}
function makeHidden(click) {

    if (jQuery("#make_hidden" + click).is(':checked')) {
        jQuery("#input-value" + click).attr('type', 'text');
    } else {
        jQuery("#input-value" + click).val('');
        jQuery("#input-value" + click).attr('type', 'hidden');
    }
}

function uppAccordionSortable() {
    jQuery(".accordion")
            .accordion({
                icons: {"header": "rs-ui-icon-plus", "activeHeader": "rs-ui-icon-minus"},
                collapsible: true,
                header: "> div > h3",
                active: false
            })
            .sortable({
                axis: "y",
                handle: "h3",
                stop: function (event, ui) {
                    // IE doesn't register the blur when sorting
                    // so trigger focusout handlers to remove .ui-state-focus
                    ui.item.children("h3").triggerHandler("focusout");

                    // Refresh accordion to handle new order
                    jQuery(this).accordion("refresh");
                }
            });
}

function generateShortcode(value){

    var str = value.replace(/\s/g,'');
    jQuery('input[name="shortcode"]').val("uppForm_"+str.toLowerCase());
}
       