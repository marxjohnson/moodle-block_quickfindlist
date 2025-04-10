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

namespace block_quickfindlist\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use block_quickfindlist\output\results;
use block_quickfindlist\user_search;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_multiple_structure;
use external_value;
use moodle_url;

/**
 * Search users with a particular role by name
 *
 * @package   block_quickfindlist
 * @copyright 2025 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Mark Johnson <mark.johnson@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_users extends external_api {
    use user_search;

    /**
     * Define parameters for external function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'blockinstanceid' => new external_value(PARAM_INT, 'Block instance ID'),
                'search' => new external_value(PARAM_TEXT, 'Search term'),
            ]
        );
    }

    /**
     * Function description
     *
     * @param int $blockinstanceid
     * @param string $search
     * @return array
     */
    public static function execute(int $blockinstanceid, string $search): array {
        self::validate_context(\context_block::instance($blockinstanceid));
        $block = block_instance_by_id($blockinstanceid);
        $roleid = $block->config->role ?? -1;
        $courseid = $block->page->course->id ?? SITEID;
        $userfields = $block->config->userfields ?? get_string('userfieldsdefault', 'block_quickfindlist');
        $baseurl = $block->config->url ?? new moodle_url('/user/view.php', ['course' => $courseid]);
        $users = self::search_users($roleid, $courseid, $userfields, $search);
        $results = new results($users, new moodle_url($baseurl), $roleid);
        return $results->export_for_template($block->page->get_renderer('core'));
    }

    /**
     * Define return values.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'roleid' => new external_value(PARAM_INT, 'Role ID'),
            'users' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'User id'),
                    'displayname' => new external_value(PARAM_TEXT, 'Display name'),
                    'url' => new external_value(PARAM_LOCALURL, 'User url'),
                ]),
            ),
        ]);
    }
}
