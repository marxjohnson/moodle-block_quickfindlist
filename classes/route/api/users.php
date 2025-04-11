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

namespace block_quickfindlist\route\api;

use block_quickfindlist\output\results;
use block_quickfindlist\user_search;
use block_quickfindlist\user;
use core\context\block;
use core\context\system;
use core\param;
use core\router\route;
use core\router\schema\objects\array_of_things;
use core\router\schema\objects\scalar_type;
use core\router\schema\objects\schema_object;
use core\router\schema\parameters\path_parameter;
use core\router\schema\parameters\query_parameter;
use core\router\schema\response\content\json_media_type;
use core\router\schema\response\payload_response;
use core\router\schema\response\response;
use core\url;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * API route handler for quickfindlist users
 *
 * @package   block_quickfindlist
 * @copyright 2025 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Mark Johnson <mark.johnson@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class users {
    use user_search;

    /**
     * Search users for a block instance.
     *
     * @param ResponseInterface $response
     * @param ServerRequestInterface $request
     * @param int $blockinstanceid
     * @return payload_response
     */
    #[route(
        description: 'Search users in a quickfindlist block instance',
        path: '/{blockinstanceid}/users',
        pathtypes: [
            new path_parameter(
                name: 'blockinstanceid',
                type: param::INT,
                description: 'The block instance id. The searched users,
                    displayname and url returned will depend on the configuration of this block.',
            ),
        ],
        queryparams: [
            new query_parameter(
                name: 'search',
                type: param::TEXT,
                required: true,
                description: 'The search string. Only users containing this string will be returned.',
            ),
        ],
        responses: [
            new response(
                statuscode: 200,
                description: 'OK',
                content: [
                    new json_media_type(
                        schema: new schema_object(
                            content: [
                                'roleid' => new scalar_type(type: param::INT),
                                'users' => new array_of_things(thingtype: user::class),
                            ],
                        ),
                        examples: [
                            new \core\router\schema\example(
                                name: 'A list of users',
                                summary: 'A JSON response containing a list of users with id,
                                    displayname and URL, plus the role ID',
                                value: [
                                    'roleid' => 1,
                                    'users' => [
                                        [
                                            'id' => 1,
                                            'displayname' => 'Arnold Arnoldson',
                                            'url' => 'https://example.com/user/index.php?id=1',
                                        ],
                                        [
                                            'id' => 2,
                                            'displayname' => 'Bryan Bryson',
                                            'url' => 'https://example.com/user/index.php?id=2',
                                        ],
                                    ],
                                ],
                            ),
                        ],
                    ),
                ],
            ),
        ],
    )]
    public function get_search_results(
        ResponseInterface $response,
        ServerRequestInterface $request,
        int $blockinstanceid,
    ): payload_response {
        global $PAGE;
        $params = $request->getQueryParams();
        $blockcontext = block::instance($blockinstanceid);
        [, $course] = get_context_info_array($blockcontext->id);
        $PAGE->set_context($blockcontext);
        $block = block_instance_by_id($blockinstanceid);
        $roleid = $block->config->role ?? -1;
        require_login($course->id);
        require_capability('block/quickfindlist:use', system::instance());
        $userfields = $block->config->userfields ?? get_string('userfieldsdefault', 'block_quickfindlist');
        $baseurl = $block->config->url ?? new url('/user/view.php', ['course' => $course->id]);
        $users = self::search_users($roleid, $course->id, $userfields, $params['search']);
        $results = new results($users, new url($baseurl), $roleid);
        $payload = $results->export_for_template($block->page->get_renderer('core'));

        return new payload_response(
            payload: $payload,
            request: $request,
            response: $response,
        );
    }
}
