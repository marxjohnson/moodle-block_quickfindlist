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
 * Defines the form for editing block instances
 *
 * @package    block_quickfindlist
 * @copyright  2010 Onwards Taunton's College, UK
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Form for editing Quick Find List block instances
 */
class block_quickfindlist_edit_form extends block_edit_form {
    /**
     * Adds block-specific fields to the parent form
     *
     * Allows configuration of the role to be searched, the text to display for each result, and
     * the url the results should link to.
     *
     * @param mixed $mform
     */
    protected function specific_definition($mform) {
        global $DB;
        global $COURSE;

        if (!empty($this->block->config->role)) {
            $currentrole = $this->block->config->role;
        } else {
            $currentrole = null;
        }

        $roles = ['-1' => get_string('allusers', 'block_quickfindlist')]
                + get_assignable_roles($this->page->context, ROLENAME_ALIAS, false, get_admin());

        $rolesused = [];

        $select = 'SELECT * ';
        $from = 'FROM {block} b
                    JOIN {block_instances} bi ON b.name = blockname ';
        $where = 'WHERE name = ?
                    AND pagetypepattern = ?
                    AND parentcontextid = ?
                    AND bi.id < ?';
        $params = [
            'quickfindlist',
            $this->block->instance->pagetypepattern,
            $this->block->instance->parentcontextid,
            $this->block->instance->id,
        ];
        if ($blocksonthispage = $DB->get_records_sql($select.$from.$where, $params)) {
            foreach ($blocksonthispage as $block) {
                if ($block->config = unserialize(base64_decode($block->configdata))) {
                    $rolesused[] = $block->config->role;
                }
            }
        }

        $strrole = get_string('role', 'block_quickfindlist');
        $roleselect = $mform->createElement('select', 'config_role', $strrole);

        foreach ($roles as $id => $name) {
            $attributes = [];
            if ($currentrole == $id) {
                $attributes['selected'] = 'selected';
            } else if (in_array($id, $rolesused)) {
                $attributes['disabled'] = 'disabled';
            }

            $text = $name;

            $params = [$id];
            $subselect = 'SELECT COUNT(*) ';
            $subfrom = 'FROM {role_assignments} ra
                           JOIN {context} c ON c.id = contextid ';
            $subwhere = 'WHERE ra.userid = {user}.id
                           AND ra.roleid = ?';

            if ($COURSE->format != 'site') {
                $params[] = $COURSE->id;
                $subwhere .= ' AND contextlevel = 50 AND instanceid = ?';
            }

            $where = '('.$subselect.$subfrom.$subwhere.') > 0
                AND deleted = 0';

            $usercount = $DB->count_records_select('user', $where, $params);
            if ($usercount > 5000) {
                echo $text .= get_string('lotsofusers', 'block_quickfindlist', $usercount);
            }
            $roleselect->addOption($text, $id, $attributes);
        }

        // Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));
        $mform->addElement($roleselect);
        $struserfields = get_string('userfields', 'block_quickfindlist');
        $userfieldsdefault = get_string('userfieldsdefault', 'block_quickfindlist');
        $mform->addElement('text', 'config_userfields', $struserfields);
        $mform->setType('config_userfields', PARAM_TEXT);
        $mform->setDefault('config_userfields', $userfieldsdefault);
        $mform->addElement('text', 'config_url', get_string('url', 'block_quickfindlist'));
        $mform->setType('config_url', PARAM_URL);
    }
}
