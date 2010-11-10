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

    $output = new stdClass;
    $output->roleid = $role;
    if(!empty($name)) {

        $params = array("%$name%");
        $select = 'SELECT id, firstname, lastname, username ';
        $from = 'FROM {user} AS u ';
        $where = "WHERE deleted = 0 AND CONCAT(firstname, ' ', lastname) LIKE ? ";
        if ($role != -1) {
            $params[] = $role;
            $subselect = 'SELECT COUNT(*) ';
            $subfrom = 'FROM {role_assignments} AS ra
                               JOIN {context} AS c ON c.id = contextid ';
            $subwhere = 'WHERE ra.userid = u.id
                               AND ra.roleid=?';
            if ($courseformat != 'site') {
                $params[] = $courseid;
                $subwhere .= ' AND contextlevel=50 AND instanceid = ?';
            }
            $where .= 'AND ('.$subselect.$subfrom.$subwhere.') > 0 ';
        }
        $order = 'ORDER BY lastname';

        if($people = $DB->get_records_sql($select.$from.$where.$order, $params)){
            $output->people = $people;
//            foreach ($people as $person) {
//                $userstring = str_replace('[[firstname]]', $person->firstname, $userfields);
//                $userstring = str_replace('[[lastname]]', $person->lastname, $userstring);
//                $userstring = str_replace('[[username]]', $person->username, $userstring);
//                $output .= '<div><a href="'.$url.$person->id.'">'.$userstring.'</a></div>';
//            }
        }
    }
    echo json_encode($output);

} else {
	header('HTTP/1.1 401 Not Authorised');
}

?>
