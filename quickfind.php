<?php
require_once('../../config.php');

$name = required_param('name', PARAM_TEXT);
$role = required_param('role', PARAM_INT);
$userfields = required_param('userfields', PARAM_CLEAN);
$url = urldecode(required_param('url', PARAM_URL));
$courseformat = required_param('courseformat', PARAM_TEXT);
$courseid = required_param('courseid', PARAM_TEXT);

$context_system = get_context_instance(CONTEXT_SYSTEM);

if (has_capability('block/quickfindlist:use', $context_system)) {

    $output='';
    if(!empty($name)) {

        if($role_record = get_record('role', 'id', $role)) {
            if ($role_record->shortname == 'student') {
                $staff_query = 'SELECT u.id, u.firstname, u.lastname, u.username
                                FROM mdl_user AS u
                                    JOIN mdl_role_assignments AS a ON a.userid=u.id
                                    JOIN mdl_role AS r ON a.roleid=r.id
                                    JOIN mdl_context AS c ON a.contextid=c.id
                                WHERE contextlevel = 10
                                    AND r.shortname="staff"';
                $staff = get_records_sql($staff_query);
            }
        }
        $query='SELECT id,firstname,lastname,username
            FROM '.$CFG->prefix.'user WHERE deleted=0 AND CONCAT(firstname, \' \', lastname) LIKE \'%'.$name.'%\'';
        if($role!=-1){
            $query.=' AND (SELECT COUNT(*)
                           FROM '.$CFG->prefix.'role_assignments
                               JOIN '.$CFG->prefix.'context ON '.$CFG->prefix.'context.id=contextid
                           WHERE '.$CFG->prefix.'role_assignments.userid='.$CFG->prefix.'user.id
                               AND '.$CFG->prefix.'role_assignments.roleid='.$role.'
                               AND '.$CFG->prefix.'role_assignments.hidden=0';
            if($courseformat!='site'){$query.=' AND contextlevel=50 AND instanceid='.$courseid;}
            $query.=' )>0';
        }
        $query.=' ORDER BY lastname';


        if($people=get_records_sql($query)){
            if(empty($userfields)){$userfields=get_string('userfieldsdefault','block_quickfindlist');}
            foreach ($people as $person) {
                if ($role_record->shortname != 'student' || ($role_record->shortname == 'student' && !in_array($person, $staff))) {
                    $userstring=str_replace('[[firstname]]',$person->firstname,$userfields);
                    $userstring=str_replace('[[lastname]]',$person->lastname,$userstring);
                    $userstring=str_replace('[[username]]',$person->username,$userstring);
                    $output .= '<div><a href="'.$url.$person->id.'">'.$userstring.'</a></div>';
                }
            }

        }
    }
    echo $output;

} else {
	header('HTTP/1.1 401 Not Authorised');
}

?>
