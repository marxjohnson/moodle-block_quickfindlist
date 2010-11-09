
function quickfindsearch(roleid, userfields, url, courseformat, courseid){
    var progress = YAHOO.util.Dom.get('quickfindprogress'+roleid);
    var searchbox = YAHOO.util.Dom.get('quickfindlistsearch'+roleid);
    var searchstring= searchbox.value;
    var quickfindlist = YAHOO.util.Dom.get('quickfindlist'+roleid);
    if(xhr != undefined) {
        YAHOO.util.Connect.abort(xhr);
    }
    progress.style.visibility = 'visible';
    xhr = YAHOO.util.Connect.asyncRequest(
        'get',
        wwwroot+'/blocks/quickfindlist/quickfind.php?role='+roleid+'&name='+searchstring+'&userfields='+userfields+'&url='+url+'&courseformat='+courseformat+'&courseid='+courseid,        
        {
            success: function(o) {
                progress.style.visibility = 'hidden';
                quickfindlist.innerHTML = o.responseText;
            },
            failure: function(o) {
                if(o.status == 0) {
                    progress.style.visibility = 'hidden';
                    quickfindlist.innerHTML = o.statusText;
                }
            }
       }
   );
}