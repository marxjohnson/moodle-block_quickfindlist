<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * AJAX script to respond to search requests
 *
 * Checks the user has required permissions, then returns a JSON object containing search results
 *
 * @package    block_quickfindlist
 * @copyright  2010 Onwards Taunton's College, UK
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);
require_once('../../config.php');

$name = required_param('name', PARAM_TEXT);
$role = required_param('role', PARAM_INT);
$courseformat = required_param('courseformat', PARAM_TEXT);
$courseid = required_param('courseid', PARAM_TEXT);

$context = get_context_instance(CONTEXT_COURSE, $courseid);

if (isloggedin() && has_capability('block/quickfindlist:use', $context) && confirm_sesskey()) {

    $output = new stdClass;
    $output->roleid = $role;
    if (!empty($name)) {

        // max 2 words
        $sStrings = explode(" ", trim($name), 2);

        if (!$sStrings || empty($sStrings)) {
            echo json_encode($output);
            exit();
        }

        $params = array();

        $select = 'SELECT id, firstname, lastname, username ';
        $from = 'FROM {user} AS u ';
        $where = 'WHERE deleted = 0 AND ';

        if (count($sStrings) == 1) {
            if (is_numeric($sStrings[0])) {
                $params[] = $sStrings[0];
                $where .= $DB->sql_like('idnumber', '?') . ' ';
            } else {
                $fields = array('firstname', 'lastname', 'username');
                $whereArr = array();

                foreach ($fields as $field) {
                    $whereArr[] = $DB->sql_like($field, "?", false, false);
                    $params[] = "%{$sStrings[0]}%";
                }

                $where .= "(" . implode(" OR ", $whereArr) . ")";
            }
        } else {
            $params[] = "%{$sStrings[0]}%";
            $params[] = "%{$sStrings[1]}%";
            $params[] = "%{$sStrings[0]}%";
            $params[] = "%{$sStrings[1]}%";
            $where .= "(" . $DB->sql_like("firstname", "?", false, false) . " AND " . $DB->sql_like("lastname", "?", false, false) . ") OR (" . $DB->sql_like("lastname", "?", false, false) . " AND " . $DB->sql_like("firstname", "?", false, false) . ")";
        }

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
            $where .= 'AND (' . $subselect . $subfrom . $subwhere . ') > 0 ';
        }

        $order = 'ORDER BY lastname';

        if ($people = $DB->get_records_sql($select . $from . $where . $order, $params)) {
            $output->people = $people;
        }
    }
    echo json_encode($output);
} else {
    header('HTTP/1.1 401 Not Authorised');
}
