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
 * AI Text Rubric question type upgrade code.
 *
 * @package    qtype_aitext_rubric
 * @copyright  Marcus Green 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade code for the aitext_rubric question type.
 *
 * qtype_aitext_rubric is a fresh component (a renamed hard fork of
 * qtype_aitext); its 0.1.0 install has no earlier schema to upgrade from.
 *
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_aitext_rubric_upgrade($oldversion) {
    return true;
}
