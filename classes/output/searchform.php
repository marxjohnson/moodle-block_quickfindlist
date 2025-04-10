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

namespace block_quickfindlist\output;

use core\output\renderable;
use core\output\templatable;
use core\url;
use renderer_base;


/**
 * Search form
 *
 * @package   block_quickfindlist
 * @copyright 2025 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Mark Johnson <mark.johnson@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class searchform implements renderable, templatable {

    /**
     * Constructor
     *
     * @param int $blockinstanceid
     * @param url $actionurl
     * @param string $rolename
     * @param int $roleid
     */
    public function __construct(
        /** @var int Block Instance ID for Javascript initialisation */
        protected int $blockinstanceid,
        /** @var url Action URL for form submission */
        protected url $actionurl,
        /** @var string Role name for input label */
        protected string $rolename,
        /** @var int Role ID for search. -1 For all roles. */
        protected int $roleid = -1,
    ) {
    }

    #[\Override]
    public function export_for_template(renderer_base $output) {
        global $PAGE;
        return [
            'instanceid' => $this->blockinstanceid,
            'roleid' => $this->roleid,
            'actionurl' => $this->actionurl->out(false),
            'progressurl' => $PAGE->theme->image_url('i/loading_small', 'moodle'),
            'searchlabel' => get_string('search', 'block_quickfindlist', $this->rolename),
        ];
    }
}
