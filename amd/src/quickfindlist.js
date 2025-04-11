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

import Pending from 'core/pending';
import Templates from 'core/templates';
import Notification from 'core/notification';
import Fetch from 'core/fetch';

/**
 * @var {Element} blockinstance
 */
let blockinstance;

let currentRequest;

/**
 * Return list of users
 * @param {Number} blockinstanceid
 * @param {String} search
 * @return {Promise}
 */
export const getUserPreferences = async(blockinstanceid, search) => {
    const endpoint = [blockinstanceid, 'users'];
    const request = Fetch.performGet('block_quickfindlist', endpoint.join('/'), {params: {search}});
    currentRequest = request;
    const response = await request;
    if (request !== currentRequest) {
        // Too late, there's a new request in town.
        return null;
    }
    return response.json();
};

const search = async(roleid, search) => {
    const pending = new Pending(`quickfindlist${roleid}`);
    const progress = blockinstance.querySelector('.quickfindprogress');
    try {
        progress.style.visibility = 'visible';
        const results = await getUserPreferences(blockinstance.dataset.instanceId, search);
        if (!results) {
            return pending.resolve();
        }
        const {html, js} = await Templates.renderForPromise('block_quickfindlist/results', results);
        Templates.replaceNode(blockinstance.querySelector('.quickfindlistresults'), html, js);
        progress.style.visibility = 'hidden';
    } catch (e) {
        progress.style.visibility = 'hidden';
        Notification.exception(e);
        return pending.reject();
    }
    currentRequest = null;
    return pending.resolve();
};

const searchOnType = (e) => {
    search(e.target.dataset.roleid, e.target.value);
};

const searchOnSubmit = (e) => {
    e.preventDefault();
    const searchInput = e.target.querySelector('.quickfindlistsearch');
    search(e.target.dataset.roleid, searchInput.value);
};

export default {
    init(instanceid) {
        blockinstance = document.querySelector(`.block_quickfindlist[data-instance-id="${instanceid}"]`);
        blockinstance.querySelector('.quickfindlistsearch').addEventListener('keyup', searchOnType);
        blockinstance.querySelector('form').addEventListener('submit', searchOnSubmit);
    }
};