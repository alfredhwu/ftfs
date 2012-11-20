$(document).ready(function () {
    // add device info dynamique ****************************************************
    $('#ftfs_servicebundle_serviceticket_form_asset').each(function() { 
        $(this).after('<a class="btn btn-success" href="#"><i class="icon-plus icon-white"></i></a>');
        var add = $(this).siblings('a');
        add.click(function(e) { 
            e.preventDefault();
            alert('clicked');
        });
    });
    // service ticket requested by selecter helper
    var helper = $('select#ftfs_servicebundle_serviceticket_form_company');
    addSelecterFilter(helper, $('select#ftfs_servicebundle_serviceticket_form_requested_by'), '-');
    addSelecterFilter(helper, $('select#ftfs_servicebundle_serviceticket_form_asset'), '-');
    addSelecterFilter($('select#ftfs_servicebundle_serviceticket_form_asset'), $('select#ftfs_servicebundle_serviceticket_form_devices'), '*');
    function addSelecterFilter(filter, selecter, sp) { 
        filter.change(function() { 
            var option = filter.find('option:selected').text().trim();
            selecter.val(-1);
            if(option == '<Select>') {
                selecter.children('option').show();
            }else{
                selecter.children('option').each(function() {
                    var value = $(this).attr('value');
                    var label = $(this).text().trim();
                    if(subString(label, sp, -1) == option || label == '<Select>') {
                        $(this).show();
                    }else{
                        $(this).hide();
                    }
                });
            }
        });
    }

    function subString(str, sp, position) {
        var substrs = str.split(sp);
        var pos = (substrs.length + position) % substrs.length;
        return substrs[pos].trim();
    }

    // ******************************************************************************************
    //
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
    //$('input#ftfs_assetbundle_asset_form_installed_in').addClass('typeahead-location');
    //$('input#ftfs_assetbundle_asset_form_installed_in').attr('data-provide', 'typeahead');
    //$('input#ftfs_assetbundle_asset_form_installed_in').addClass('typeahead');
    addTypeaheadLocation($('input#ftfs_assetbundle_asset_form_installed_in'));
    // a.tic-tac
    // tic-tac-target
    $('a.tic-tac').click(function(e) {
        e.preventDefault();
        $(this).children('span').toggle();
        var target = $($(this).attr('tic-tac-target'));
        // call back of target
        var callback = target.attr('callback');
        if(typeof window.settings[callback] == "function") {
            window.settings[callback](target);
        }
        // end of call back of target
        $(target).toggle();
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
    // use bootbox pop a dialog of confirmation
    $('.crud-action').click(function (e) {
        var message = $(this).attr('crud-message');
        var action = $(this).attr('crud-toggle');
        bootbox.confirm(message, function(result) {
            e.preventDefault();
            if(result) {
                //window.location = action;
                window.location.replace(action);
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
        //$('li.navmenu-item.navmenu-item-notification-countable > a > span').each(function() {
        $('span#notification-counter').each(function() {
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
                    //$('span.notification-count').html(data);
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



// ##################### type ahead location
function addTypeaheadLocation(typeaheadHolder) { 
    typeaheadHolder.attr('data-provide', 'typeahead');
    //alert(typeaheadHolder.parent().html());
    typeaheadHolder.typeahead().on('keyup', function(ev) {
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
}



// general help func ############################################### url manipulation
function urlSearchGetQuery(key, search) {
    // return the value of key 'key'
    var queries = parseSearchToQueries(getSearchString(search));
    return queries[key];
}

function urlSearchSetQuery(key, value, search) {
    // return a search string with a query key=value
    if(key===undefined || value===undefined) {
        return '?'+getSearchString(search);
    }
    var queries = parseSearchToQueries(getSearchString(search)); // by default, current search string
    var new_search = '?';
    for(var query in queries) {
        if(query!==key) {
            new_search += query+'='+queries[query]+'&';
        }
    }
    return new_search + escape(key)+'='+escape(value);
}

function urlSearchUnsetQuery(key, search) {
    var new_search = '_';
    if(key===undefined) {
        new_search = '&'+getSearchString(search);
    }else{
        var queries = parseSearchToQueries(getSearchString(search)); // by default, current search string
        for(var query in queries) {
            if(query!==key) {
                new_search += '&'+query+'='+queries[query];
            }
        }
    }
    if(new_search.length>1) {
        new_search = new_search.replace(/_&/, '?');
    }else if(new_search.length=1) {
        new_search = '';
    }
    return new_search;
}

function parseSearchToQueries(search) {
    // search: string with form, key=value&key=value...
    // all other form will be ignored
    var queries = {};
    if(typeof(search)!=='string') {
        return queries;
    }
    var pairs = search.split('&');
    for(i=0;i<pairs.length;i++) {
        var pair = pairs[i].split('=');
        if(pair.length === 2) {
            queries[pair[0]] = pair[1];
        }
    }
    return queries;
}

function getSearchString(search) {
    // return first sub string after ? of search
    // by default, the current query string
    if(search===undefined) {
        // default, current search string
        search = document.location.search;
    }
    var strings = search.split('?');
    if(strings.length > 1) {
        return strings[1];
    }
    return strings[0];
}

function pageGoto(args) {
    if(typeof(args) !== 'object') {
        return undefined;
    }
    //var host = typeof(args['host'])==='string' ? args['host'] : window.location.host;
    var pathname = typeof(args['pathname'])==='string' ? args['pathname'] : window.location.pathname;
    var search = typeof(args['search'])==='string' ? args['search'] : window.location.search;
    var hash = typeof(args['hash'])==='string' ? args['hash'] : window.location.hash;

    var href = pathname+search+hash;
    if(args['session'] === false) {
        window.location.replace(href);
    }else{
        window.location = href;
    }
}
// ########################################################################### end of url manipulation
window.settings = {
    'get_ajax_resource': get_ajax_resource
};

function get_ajax_resource(target) {
    var method = target.attr('method');
    var url = target.attr('url');
    var url_handle = target.attr('url-handle');
    url_handle = typeof url_handle == 'undefined' || url_handle == '' ? url : url_handle;
    url_handle = escape(url_handle);
    var data = target.attr('data');
    var toggle = target.attr('callback-toggle');
    var animation = ajax_animation(target);
    //alert(url_handle);
    if(method==='get') {
        $.get(url, { action: url_handle }, function(data) { 
            $(toggle).html(data);
            animation.remove();
        });
    }else{
        $.post(url, data, function(data) { 
            $(toglle).html(data);
            animation.remove();
        });
    }
}

