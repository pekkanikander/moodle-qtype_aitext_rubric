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
 * Serve question type files
 *
 * @package    qtype_aitext_rubric
 * @copyright  Marcus Green 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Checks file access for aitext questions.
 *
 * @package  qtype_aitext_rubric
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool
 */
function qtype_aitext_rubric_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $CFG;
    require_once($CFG->libdir . '/questionlib.php');
    question_pluginfile($course, $context, 'qtype_aitext_rubric', $filearea, $args, $forcedownload, $options);
}

/**
 * Add a per-quiz scaffold level override selector to the quiz settings form.
 *
 * @param moodleform_mod $formwrapper the module settings form wrapper
 * @param MoodleQuickForm $mform the wrapped form
 * @return void
 */
function qtype_aitext_rubric_coursemodule_standard_elements($formwrapper, $mform) {
    global $DB;
    $current = $formwrapper->get_current();
    if ($current->modulename !== 'quiz') {
        return;
    }
    $mform->addElement('header', 'qtypeaitextheader', get_string('quizsettingsheader', 'qtype_aitext_rubric'));
    // 0 here means "no override", not the reserved scaffold level 0.
    $mform->addElement(
        'select',
        'qtypeaitextscaffoldlevel',
        get_string('scaffoldlevel', 'qtype_aitext_rubric'),
        [
            0 => get_string('scaffoldquizdefault', 'qtype_aitext_rubric'),
            1 => get_string('scaffoldlevel1', 'qtype_aitext_rubric'),
            2 => get_string('scaffoldlevel2', 'qtype_aitext_rubric'),
        ]
    );
    $mform->addHelpButton('qtypeaitextscaffoldlevel', 'scaffoldlevel', 'qtype_aitext_rubric');
    if (!empty($current->instance)) {
        $override = $DB->get_field('qtype_aitext_rubric_quiz', 'scaffoldlevel', ['quizid' => $current->instance]);
        if ($override !== false) {
            $mform->setDefault('qtypeaitextscaffoldlevel', (int) $override);
        }
    }
}

/**
 * Persist the per-quiz scaffold level override from the quiz settings form.
 *
 * @param stdClass $moduleinfo the saved module info
 * @param stdClass $course the course
 * @return stdClass the module info, unchanged
 */
function qtype_aitext_rubric_coursemodule_edit_post_actions($moduleinfo, $course) {
    global $DB;
    if ($moduleinfo->modulename !== 'quiz' || !isset($moduleinfo->qtypeaitextscaffoldlevel)) {
        return $moduleinfo;
    }
    $level = (int) $moduleinfo->qtypeaitextscaffoldlevel;
    $existing = $DB->get_record('qtype_aitext_rubric_quiz', ['quizid' => $moduleinfo->instance]);
    if ($level === 0) {
        if ($existing) {
            $DB->delete_records('qtype_aitext_rubric_quiz', ['id' => $existing->id]);
        }
    } else if ($existing) {
        if ((int) $existing->scaffoldlevel !== $level) {
            $existing->scaffoldlevel = $level;
            $DB->update_record('qtype_aitext_rubric_quiz', $existing);
        }
    } else {
        $DB->insert_record('qtype_aitext_rubric_quiz', (object) [
            'quizid' => $moduleinfo->instance,
            'scaffoldlevel' => $level,
        ]);
    }
    return $moduleinfo;
}

/**
 * Delete the scaffold level override of a quiz being deleted.
 *
 * @param stdClass $cm the course module being deleted
 * @return void
 */
function qtype_aitext_rubric_pre_course_module_delete($cm) {
    global $DB;
    if ($cm->modname === 'quiz') {
        $DB->delete_records('qtype_aitext_rubric_quiz', ['quizid' => $cm->instance]);
    }
}
