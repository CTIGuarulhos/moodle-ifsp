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
 * This file contains public API of tutors report
 *
 * @package    report
 * @subpackage tutors
 * @copyright  2009 Sam Hemelryk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * This function extends the course navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function report_tutors_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/tutors:view', $context)) {
        $url = new moodle_url('/report/tutors/index.php', array('id'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_tutors'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

/**
 * This function extends the course navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $user
 * @param stdClass $course The course to object for the report
 */
function report_tutors_extend_navigation_user($navigation, $user, $course) {
    if (report_tutors_can_access_user_report($user, $course)) {
        $url = new moodle_url('/report/tutors/user.php', array('id'=>$user->id, 'course'=>$course->id, 'mode'=>'tutors'));
        $navigation->add(get_string('tutorsreport'), $url);
        $url = new moodle_url('/report/tutors/user.php', array('id'=>$user->id, 'course'=>$course->id, 'mode'=>'complete'));
        $navigation->add(get_string('completereport'), $url);
    }
}

/**
 * Is current user allowed to access this report
 *
 * @private defined in lib.php for performance reasons
 *
 * @param stdClass $user
 * @param stdClass $course
 * @return bool
 */
function report_tutors_can_access_user_report($user, $course) {
    global $USER;

    $coursecontext = context_course::instance($course->id);
    $personalcontext = context_user::instance($user->id);

    if (has_capability('report/tutors:view', $coursecontext)) {
        return true;
    }

    if (has_capability('moodle/user:viewuseractivitiesreport', $personalcontext)) {
        if ($course->showreports and (is_viewing($coursecontext, $user) or is_enrolled($coursecontext, $user))) {
            return true;
        }

    } else if ($user->id == $USER->id) {
        if ($course->showreports and (is_viewing($coursecontext, $USER) or is_enrolled($coursecontext, $USER))) {
            return true;
        }
    }

    return false;
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 * @return array
 */
function report_tutors_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $array = array(
        '*'                    => get_string('page-x', 'pagetype'),
        'report-*'             => get_string('page-report-x', 'pagetype'),
        'report-tutors-*'     => get_string('page-report-tutors-x',  'report_tutors'),
        'report-tutors-index' => get_string('page-report-tutors-index',  'report_tutors'),
        'report-tutors-user'  => get_string('page-report-tutors-user',  'report_tutors')
    );
    return $array;
}

/**
 * Callback to verify if the given instance of store is supported by this report or not.
 *
 * @param string $instance store instance.
 *
 * @return bool returns true if the store is supported by the report, false otherwise.
 */
function report_tutors_supports_logstore($instance) {
    if ($instance instanceof \core\log\sql_internal_reader || $instance instanceof \logstore_legacy\log\store) {
        return true;
    }
    return false;
}
