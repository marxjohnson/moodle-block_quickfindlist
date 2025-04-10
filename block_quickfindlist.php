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
 * Defines the block_quickfindlist class
 *
 * @package    block_quickfindlist
 * @copyright  2010 Onwards Taunton's College, UK
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_quickfindlist\user_search;

/**
 *  Class definition for the Quick Find List block.
 */
class block_quickfindlist extends block_base {
    use user_search;

    /**
     * Set title.
     */
    public function init() {
        $this->title = get_string('quickfindlist', 'block_quickfindlist');
    }

    #[\Override]
    public function instance_allow_multiple() {
        return true;
    }

    /**
     *  Generates the block's content
     *
     *  Determines the role configured for this instance, and ensures it doesn't conflict with other
     *  instances on the page.
     *  Then displays a form for searching the users who have that role, and if required, the
     *  results from the submitted search.
     */
    public function get_content() {
        global $COURSE, $DB;
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;

        if (empty($this->config->role)) {
            $select = 'SELECT * ';
            $from = 'FROM {block} b
                        JOIN {block_instances} bi ON b.name = blockname ';
            $where = 'WHERE name = \'quickfindlist\'
                        AND pagetypepattern = ?
                        AND parentcontextid = ?
                        AND bi.id < ?';
            $params = [
                $this->instance->pagetypepattern,
                $this->instance->parentcontextid,
                $this->instance->id,
            ];
            if ($thispageqflblocks = $DB->get_records_sql($select.$from.$where, $params)) {
                foreach ($thispageqflblocks as $thispageqflblock) {
                    // Don't give a warning for blocks without a role configured.
                    if (@unserialize(base64_decode($thispageqflblock->configdata))->role < 1) {
                        $this->content->text = get_string('multiplenorole', 'block_quickfindlist');
                        return $this->content;
                    }
                }
            }
            if (empty($this->config)) {
                 $this->config = new stdClass();
            }

            $this->config->role = -1;
        }

        if ($role = $DB->get_record('role', ['id' => $this->config->role])) {
            $roleid = $role->id;
            $rolename = role_get_name($role);
        } else {
            $roleid = '-1';
            $rolename = get_string('allusers', 'block_quickfindlist');
        }
        $this->title = $rolename.get_string('list', 'block_quickfindlist');

        $context = context_system::instance();

        if (has_capability('block/quickfindlist:use', $context)) {
            if (empty($this->config->userfields)) {
                $this->config->userfields = get_string('userfieldsdefault', 'block_quickfindlist');
            }
            if (empty($this->config->url)) {
                $url = new moodle_url('/user/view.php', ['course' => $COURSE->id]);
            } else {
                $url = new moodle_url($this->config->url);
            }
            $name = optional_param('quickfindlistsearch'.$roleid, '', PARAM_TEXT);

            $searchform = new \block_quickfindlist\output\searchform(
                $this->instance->id,
                new moodle_url($this->page->url, anchor: "quickfindanchor{$roleid}"),
                $rolename,
                $this->config->role,
            );
            $renderer = $this->page->get_renderer('core');
            $form = $renderer->render($searchform);

            $quickfindsubmit[$roleid] = optional_param('quickfindsubmit'.$roleid,
                                                       false,
                                                       PARAM_ALPHA);
            $users = [];
            if (!empty($quickfindsubmit[$roleid])) {
                $users = $this->search_users($roleid, $COURSE->id, $this->config->userfields, $name);
            }
            $results = new \block_quickfindlist\output\results($users, $url, $roleid);
            $list = $renderer->render($results);

            if (empty($this->content)) {
                 $this->content = new stdClass();
            }
            $this->content->footer = '';
            $this->content->text = $form.$list;
        }

        return $this->content;

    }
}
