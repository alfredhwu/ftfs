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

    // a.tic-tac
    // tic-tac-target
    $('a.tic-tac').click(function(e) {
        e.preventDefault();
        $(this).children('span').toggle();
        $($(this).attr('tic-tac-target')).toggle();
    });
    // new edit helper
    $('a.crud-submit').click(function (e) {
        var mode = $(this).attr('crud-submit-mode');
 //       alert("js/addition@debug: " + mode);
        e.preventDefault();
        // defined in FTFSDashboardBundle::layout.html.twig
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

    // menu indicator
    init_notifier();
    // add refresher
    function init_notifier() {
        $('div#top-notification-area').html('<h1>top notification</h1>');
        // menu
        $('li.navmenu-item.navmenu-item-countable > a > span').each(function() {
            $(this).addClass('pull-right badge badge-inverse');        
        });
        refresh_menu();
        // set auto refresher 
        var interval = 5000; // in mini seconds
        setInterval(function() {refresh_menu()}, interval);
    }

    function refresh_menu() {
        // find all menu items that need a counter and get the number
        //$('li.navmenu-item.countable > a').children(':last-child').after('<span class="pull-right">count</span>');
        $('li.navmenu-item.navmenu-item-countable > a').each(function() {
            var href = $(this).attr('href').replace(/list/,'count');
            var target = $(this);
            $.ajax({
                type:   "GET",
                url:    href,
                data:   "",
                cache:  false,
                success: function(data) {
                    var last = target.children().last();
                    if(last.html() != data) {
                        last.html(data);
                    }
                },
                error:   function() {
                    alert("Ooups ... something's got wrong: the ajax connection failed in rendering response for " + url);
                },
            });
        });
    }
});
