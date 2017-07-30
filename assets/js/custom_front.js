jQuery(function ($) {

    var v = $(".uppForm").validate({
        rules: {
            companyName: {
                required: true
            },
            "emails[value]": {
                required: true,
                email: true 
            },
            name: {
                required: true
            }


        },
        messages: {
            companyName: 'Company name is required!',
            "emails[value]": 'Email is required and must be valid format!!',
            name: 'Name is required!'
        },
        errorElement: "span",
        errorClass: "help-inline-error"
    });
    $("#saveUppForm").on("click", function () {
       if (v.form()) {
           $(".uppForm").submit();
       }
    });


});

