define(
    ['jquery'],
    function($) {
        var priv = {
            sesskey: null,
            instances: [],
            loginasurl: ''
        };

        var t = {
            init: function(roleid, userfields, url, courseformat, courseid, sesskey, loginasurl) {
                priv.sesskey = sesskey;
                priv.loginasurl = loginasurl;

                var instance = {
                    'roleid': roleid,
                    'userfields': userfields,
                    'url': url,
                    'courseformat': courseformat,
                    'courseid': courseid,
                    'progress': $('#quickfindprogress'+roleid),
                    'listcontainer': $('#quickfindlist'+roleid),
                    'xhr': null
                };
                priv.instances[roleid] = instance;
                $('#quickfindlistsearch'+roleid).on('keyup', t.search_on_type);
                $('#quickfindform'+roleid).on('submit', t.search_on_submit);
            },

            search_on_type: function(e) {
                var target = $(e.target);
                var searchstring = target.val();
                var roleid = /[\-0-9]+/.exec(target.attr('id'))[0];
                t.search(searchstring, roleid);
                M.util.js_pending('quickfindlist' + roleid);
            },

            search_on_submit: function(e) {
                e.preventDefault();
                var target = $(e.target);
                var roleid = /[\-0-9]+/.exec(target.attr('id'))[0];
                var searchstring = target.find('#quickfindlistsearch'+roleid).val();
                t.search(searchstring, roleid);
                M.util.js_pending('quickfindlist' + roleid);
            },


            search: function(searchstring, roleid) {

                var instance = priv.instances[roleid];

                var url = M.cfg.wwwroot+'/blocks/quickfindlist/quickfind.php';
                if (instance.xhr !== null) {
                    instance.xhr.abort();
                }
                instance.progress.css('visibility', 'visible');
                instance.xhr = $.ajax({
                    url: url,
                    data: {
                        role: roleid,
                        name: searchstring,
                        courseformat: instance.courseformat,
                        courseid: instance.courseid,
                        sesskey: priv.sesskey
                    }
                }).done(function(response) {
                    var list = $('<ul />');
                    for (var p in response.people) {
                        var userstring = instance.userfields.replace('[[firstname]]', response.people[p].firstname);
                        userstring = userstring.replace('[[lastname]]', response.people[p].lastname);
                        userstring = userstring.replace('[[username]]', response.people[p].username);
                        var loginas = '<a href="' + priv.loginasurl + '&user=' + response.people[p].id + '&sesskey=' +
                            priv.sesskey + '"><i class="fa fa-user"></i></a>';
                        var li = $('<li><a href="' + instance.url + '&id=' + response.people[p].id + '">' + userstring +
                            '</a> ' + loginas + '</li>');
                        list.append(li);
                    }
                    $('#quickfindlist'+roleid).replaceWith(list);
                    list.attr('id', 'quickfindlist'+roleid);
                }).fail(function(jqXHR, status) {
                    if (status !== 'abort') {
                        if (status !== undefined) {
                            instance.listcontainer.html(status);
                        }
                    }
                }).always(function() {
                    instance.progress.css('visibility', 'hidden');
                    M.util.js_complete('quickfindlist' + roleid);
                });
            }
        };

        return t;
    }
);
