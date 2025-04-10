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

namespace block_quickfindlist;

/**
 * Methods for searching users
 *
 * @package   block_quickfindlist
 * @copyright 2025 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Mark Johnson <mark.johnson@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait user_search {
    /**
     * Search users with a particular role ID.
     *
     * @param int $roleid The role to search, -1 for all users.
     * @param int $courseid The course ID to restrict the search to.
     * @param string $userfields The configued list of user fields for formatting the returned displayname.
     * @param string $search The string to search for within the user's name.
     * @return array User objects like {id, displayname}
     */
    public static function search_users(int $roleid, int $courseid, string $userfields, string $search) {
        global $DB;
        $users = [];
        if (!empty($search)) {
            $params = ["%$search%"];
            $select = 'SELECT id, firstname, lastname, username ';
            $from = 'FROM {user} u ';
            $where = "WHERE deleted = 0 AND CONCAT(firstname, ' ', lastname) LIKE ? ";
            if ($roleid != -1) {
                $params[] = $roleid;
                $subselect = 'SELECT COUNT(*) ';
                $subfrom = 'FROM {role_assignments} ra
                                           JOIN {context} c ON c.id = contextid ';
                $subwhere = 'WHERE ra.userid = u.id
                                           AND ra.roleid=?';
                if ($courseid != SITEID) {
                    $params[] = $courseid;
                    $subwhere .= ' AND contextlevel=50 AND instanceid = ?';
                }
                $where .= 'AND (' . $subselect . $subfrom . $subwhere . ') > 0 ';
            }
            $order = 'ORDER BY lastname';

            if ($people = $DB->get_records_sql($select . $from . $where . $order, $params)) {
                foreach ($people as $person) {
                    $userstring = str_replace('[[firstname]]',
                        $person->firstname,
                        $userfields);
                    $userstring = str_replace('[[lastname]]',
                        $person->lastname,
                        $userstring);
                    $userstring = str_replace('[[username]]',
                        $person->username,
                        $userstring);
                    $users[] = (object) [
                        'id' => $person->id,
                        'displayname' => $userstring,
                    ];
                }
            }
        }
        return $users;
    }
}
