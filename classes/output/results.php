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

use block_quickfindlist\user;
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
class results implements renderable, templatable {

    /**
     * Constructor
     *
     * @param array $users
     * @param url $baseurl
     * @param int $roleid
     */
    public function __construct(
        /** @var array {id, displayname} For each user to list */
        protected array $users,
        /** @var url Base URL for user links. */
        protected url $baseurl,
        /** @var int Role ID for search. -1 For all roles. */
        protected int $roleid = -1,
    ) {
    }

    #[\Override]
    public function export_for_template(renderer_base $output) {
        $users = [];
        foreach ($this->users as $user) {
            $users[] = new user(
                $user->id,
                $user->displayname,
                (new url($this->baseurl, ['id' => $user->id]))->out(false),
            );
        }
        return [
            'roleid' => $this->roleid,
            'users' => $users,
        ];
    }
}
