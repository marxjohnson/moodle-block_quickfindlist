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
 * Defines block metadata
 *
 * @package    block_quickfindlist
 * @copyright  2010 Onwards Taunton's College, UK
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2025041100;
$plugin->requires = 2025040800; // Moodle 5.0.
$plugin->component = 'block_quickfindlist';
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '2.2 (Build: 2025041001)';
$plugin->supports = [500];
