M.block_quickfindlist = {
    init: function(Y, roleid, userfields, url, courseformat, courseid) {
        this.Y = Y;
        if (this.instances === undefined) {
            this.instances = new Array();
        }
        
        var instance = {
            'roleid': roleid,
            'userfields': userfields,
            'url': url,
            'courseformat': courseformat,
            'courseid': courseid,
            'progress': Y.one('#quickfindprogress'+roleid),
            'listcontainer': Y.one('#quickfindlist'+roleid),
            'searchbox': Y.one('#quickfindlistsearch'+roleid),
            'xhr': null
        }
        this.instances[roleid] = instance;
        Y.on('keyup', this.search, '#quickfindlistsearch'+roleid);
        Y.on('submit', this.search, '#quickfindform'+roleid);
    },

    search: function(e) {
        e.preventDefault();
        var Y = M.block_quickfindlist.Y;
        var roleid = /[\-0-9]+/.exec(e.target.get('id'))[0];
        M.block_quickfindlist.currentinstance = M.block_quickfindlist.instances[roleid];
        var instance = M.block_quickfindlist.currentinstance;
        var searchstring = instance.searchbox.get('value');

        uri = M.cfg.wwwroot+'/blocks/quickfindlist/quickfind.php';
        if (instance.xhr != null) {
            instance.xhr.abort();
        }
        instance.progress.setStyle('visibility', 'visible');
        instance.xhr = Y.io(uri, {
            data: 'role='+roleid+'&name='+searchstring+'&userfields='+instance.userfields+'&url='+instance.url+'&courseformat='+instance.courseformat+'&courseid='+instance.courseid,
            on: {
                success: function(id, o) {
                    var instance = M.block_quickfindlist.currentinstance;
                    instance.progress.setStyle('visibility', 'hidden');
                    instance.listcontainer.set('innerHTML', o.responseText);
                },
                failure: function(id, o) {
                    if (o.statusText != 'abort') {
                        var instance = M.block_quickfindlist.currentinstance;
                        instance.progress.setStyle('visibility', 'hidden');
                        if (o.statusText !== undefined) {
                            instance.listcontainer.set('innerHTML', o.statusText);
                        }
                    }
                }
            }
        });
    }
}

//
//quickfindsearch(\''.$roleid.'\', \''.$this->config->userfields.'\', \''.urlencode($this->config->url).'\', \''.$COURSE->format.'\', \''.$COURSE->id.'\')" id="quickfindlistsearch'.$roleid.'" name="quickfindlistsearch'.$roleid.'" value="'.$name.'
//function quickfindsearch(roleid, userfields, url, courseformat, courseid){
//    var progress = YAHOO.util.Dom.get('quickfindprogress'+roleid);
//    var searchbox = YAHOO.util.Dom.get('quickfindlistsearch'+roleid);
//    var searchstring= searchbox.value;
//    var quickfindlist = YAHOO.util.Dom.get('quickfindlist'+roleid);
//    if(xhr != undefined) {
//        YAHOO.util.Connect.abort(xhr);
//    }
//    progress.style.visibility = 'visible';
//    xhr = YAHOO.util.Connect.asyncRequest(
//        'get',
//        wwwroot+'/blocks/quickfindlist/quickfind.php?role='+roleid+'&name='+searchstring+'&userfields='+userfields+'&url='+url+'&courseformat='+courseformat+'&courseid='+courseid,
//        {
//            success: function(o) {
//                progress.style.visibility = 'hidden';
//                quickfindlist.innerHTML = o.responseText;
//            },
//            failure: function(o) {
//                if(o.status == 0) {
//                    progress.style.visibility = 'hidden';
//                    quickfindlist.innerHTML = o.statusText;
//                }
//            }
//       }
//   );
//}