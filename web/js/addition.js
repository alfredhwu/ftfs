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
    // type ahead of country/city
    $('input#ftfs_assetbundle_asset_form_installed_in').addClass('typeahead-location');
    $('input#ftfs_assetbundle_asset_form_installed_in').attr('data-provide', 'typeahead');
    //$('input#ftfs_assetbundle_asset_form_installed_in').addClass('typeahead');
    $('input.typeahead-location').typeahead().on('keyup', function(ev) {
       // $(this).typeahead(options.source=["hewwl", "hell"]);
        ev.stopPropagation();
        ev.preventDefault();

        if($.inArray(ev.keyCode, [40, 38, 9, 13, 27]) === -1 ) {
            target = $(this);
            target.data('typeahead').source = [];

            if(!target.data('active') && target.val().length > 0) {
                target.data('active', true);
                $.getJSON("http://ws.geonames.org/searchJSON?callback=?", {
                    featureClass: "P",
                    style: "full",
                    maxRows: 8,
                    name_startsWith: $(this).val()
                }, function(data) {
                    target.data('active', true);
                    var arr = [], i = data.geonames.length, item;
                    while(i--) {
                        item = data.geonames[i];
                        arr[i] = item.name + (item.adminName1 ? ", "+item.adminName1 : "") + ", " + item.countryName;
                    }
                    target.data('typeahead').source = arr;
                    target.trigger('keyup');
                    target.data('active', false);
                });
            }
        }
    });

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
    init_auto_refresher(5000);
    // add refresher
    function init_auto_refresher(interval) {
        body_menu_count_notifier(true);
        notification_count_notifier();
        notification_message_notifier();

        // set auto refresher 
        setInterval(function() {body_menu_count_notifier()}, interval);
        setInterval(function() {notification_count_notifier()}, interval);
        setInterval(function() {notification_message_notifier()}, interval);
    }
    
    function notification_message_notifier () {
        $('div.notification-message').each(function() { 
            var target = $(this);
            var href = $(this).attr('url');
            $.ajax({
                type:   "POST",
                url:    href,
                data:   "",
                cache:  false,
                success: function(data) {
                    if(data!='') {
                        target.hide();
                        target.html('<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert">x</button> <span class="badge badge-info">!</span> <strong>System Notification:</strong>'+data+'</div>');
                        target.fadeIn(500).delay(4000).fadeOut(500);
                    }
                },
                error:   function() {
                //    alert("Ooups ... something's got wrong: the ajax connection failed in rendering response for ");
                },
            });
        });
    }

    function notification_count_notifier () {
        $('li.navmenu-item.navmenu-item-notification-countable > a > span').each(function() {
            var href = $(this).attr('url');
            var target = $(this);
            $.ajax({
                type:   "POST",
                url:    href,
                data:   "",
                cache:  false,
                success: function(data) {
                    if(target.html() != data) {
                        target.html(data);
                    }
                    $('span.notification-count').html(data);
                },
                error:   function() {
                //    alert("Ooups ... something's got wrong: the ajax connection failed in rendering response for " + url);
                },
            });
        });
    }

    function body_menu_count_notifier(init) {
        init = typeof init !== 'undefined' ? init : false;
        if(init) {
            // menu
            $('li.navmenu-item.navmenu-item-countable > a > span').each(function() {
                $(this).addClass('pull-right badge badge-inverse');        
            });
        }
        // find all menu items that need a counter and get the number
        //$('li.navmenu-item.countable > a').children(':last-child').after('<span class="pull-right">count</span>');
        $('li.navmenu-item.navmenu-item-countable > a').each(function() {
            var href = $(this).attr('href').replace(/list/,'body_menu_count');
            var target = $(this).children().last();
            $.ajax({
                type:   "POST",
                url:    href,
                data:   "",
                cache:  false,
                success: function(data) {
                    if(target.html() != data) {
                        target.html(data);
                    }
                },
                error:   function() {
                    //alert("Ooups ... something's got wrong: the ajax connection failed in rendering response for " + url);
                },
            });
        });
    }
});
