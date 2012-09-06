$(document).ready(function () {
    /*
    bootbox.alert("Custom label text !", "Custom button", function() {
        // code after dismission
        alert("dialog dismissed !");
    });
    bootbox.confirm("Confirm box: Are you sure?", "no way", "yes pleas", function(result) {
        if(result) {
            alert("confirmed");
        }else{
            alert("dismissed");
        }
    });
    */
    // new edit helper
    $('a.crud-submit').click(function (e) {
        var mode = $(this).attr('crud-submit-mode');
        e.preventDefault();
        form_submit(mode);
    });

    // prod
    $('.crud-action').click(function (e) {
        var message = $(this).attr('crud-message');
        var action = $(this).attr('crud-toggle');
        bootbox.confirm(message, function(result) {
            e.preventDefault();
            if(result) {
                window.location = action;
            }
        });
    });

    // modal-notification initial code
    $('div.modal-notification').modal({ 
        backdrop: true,
        show: true
    });

    // tooltip initial code
    if($("[rel=tooltip]").length) {
        $("[rel=tooltip]").tooltip();
    }
});
