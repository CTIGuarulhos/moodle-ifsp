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
 * Display user activity reports for a course (totals)
 *
 * @package    report
 * @subpackage students
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/report/students/locallib.php');

$id = required_param('id',PARAM_INT);       // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

$PAGE->set_url('/report/students/index.php', array('id'=>$id));
$PAGE->set_pagelayout('report');

require_login($course);
$context = context_course::instance($course->id);
require_capability('report/students:view', $context);

// Trigger an activity report viewed event.
$event = \report_students\event\activity_report_viewed::create(array('context' => $context));
$event->trigger();

$showlastaccess = true;
$hiddenfields = explode(',', $CFG->hiddenuserfields);

if (array_search('lastaccess', $hiddenfields) !== false and !has_capability('moodle/user:viewhiddendetails', $context)) {
    $showlastaccess = false;
}

$stractivityreport = get_string('pluginname', 'report_students');
$stractivity       = get_string('activity');
$strreports        = get_string('reports');

$PAGE->set_title($course->shortname .': '. $stractivityreport);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($course->fullname));

list($uselegacyreader, $useinternalreader, $minloginternalreader, $logtable) = report_students_get_common_log_variables();

// If no legacy and no internal log then don't proceed.
if (!$uselegacyreader && !$useinternalreader) {
    echo $OUTPUT->box_start('generalbox', 'notice');
    echo $OUTPUT->notification(get_string('nologreaderenabled', 'report_students'));
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    die();
}

$studentstable = new html_table();
$studentstable->attributes['class'] = 'generaltable boxaligncenter';
$studentstable->cellpadding = 5;
$studentstable->id = 'studentstable';
$studentstable->head[] = $stractivity;

/* Pegar os nomes dos tutores */
 $tutorSQL = "SELECT u.id, concat(u.firstname,' ',u.lastname) fullname
                   FROM {role_assignments} ra
                   JOIN ({user} u, {context} cm, {course} c, {role} r) ON (r.archetype in ('student')  and c.id = :courseid and u.id = ra.userid
                  and ra.contextid = cm.id AND cm.instanceid = c.id AND ra.roleid = r.id )
				  order by fullname";

$tutorNames = $DB->get_records_sql($tutorSQL, array('courseid' => $course->id));
if(count($tutorNames)>0){
	foreach($tutorNames as $tn){
		$studentstable->head[] = $tn->fullname;
		$students[] = $tn->id;
		
	}
	$tutorStr = implode(',', $students);
}

$modinfo = get_fast_modinfo($course);

// Get record from sql_internal_reader and merge with records obtained from legacy log (if needed).
if ($useinternalreader) {
    // Check if we need to show the last access.
    /*$sqllasttime = '';
	 $sql = " SELECT cm.id as cmid, gi.itemmodule
              FROM {grade_items} gi, {course_modules} cm, {modules} m
             WHERE gi.courseid = :courseid
			   AND cm.visible = 1
			   AND gi.iteminstance = cm.instance
			   AND gi.itemmodule = m.name
			   AND m.id = cm.module";
    $params = array('courseid' => $course->id);
    $v = $DB->get_records_sql($sql, $params);
	if(count($v)>0){
        foreach ($v as $key => $value) {
			$show[$value->cmid] = $value->cmid;
		}
	}*/
	
    $sql = " SELECT l.id, contextinstanceid as cmid, l.userid, COUNT(distinct l.relateduserid) AS numviews
              FROM logstore_standard_log l
             WHERE courseid = :courseid
               AND anonymous = 0
               /*AND crud = 'u'*/
               AND contextlevel = :contextmodule
               /*AND userid in ( $tutorStr )*/
               AND relateduserid is not null
          GROUP BY contextinstanceid, l.userid";
    $params = array('courseid' => $course->id, 'contextmodule' => CONTEXT_MODULE);
    $v = $DB->get_records_sql($sql, $params);

	if(count($v)>0){
        foreach ($v as $key => $value) {
			$views[$value->cmid][$value->userid] = $value->numviews;
        }
	}
}
$prevsecctionnum = 0;
foreach ($modinfo->sections as $sectionnum=>$section) {
    foreach ($section as $cmid) {
        $cm = $modinfo->cms[$cmid];
		//if(in_array($cmid, $show)){
			if (!$cm->has_view()) {
				continue;
			}
			if (!$cm->uservisible) {
				continue;
			}
			if ($prevsecctionnum != $sectionnum) {
				$sectionrow = new html_table_row();
				$sectionrow->attributes['class'] = 'section';
				$sectioncell = new html_table_cell();
				$sectioncell->colspan = count($studentstable->head);

				$sectiontitle = get_section_name($course, $sectionnum);

				$sectioncell->text = $OUTPUT->heading($sectiontitle, 3);
				$sectionrow->cells[] = $sectioncell;
				$studentstable->data[] = $sectionrow;

				$prevsecctionnum = $sectionnum;
			}

			$dimmed = $cm->visible ? '' : 'class="dimmed"';
			$modulename = get_string('modulename', $cm->modname);

			$reportrow = new html_table_row();
			$activitycell = new html_table_cell();
			$activitycell->attributes['class'] = 'activity';

			$activityicon = $OUTPUT->pix_icon('icon', $modulename, $cm->modname, array('class'=>'icon'));

			$attributes = array();
			if (!$cm->visible) {
				$attributes['class'] = 'dimmed';
			}

			$activitycell->text = $activityicon . html_writer::link("$CFG->wwwroot/mod/$cm->modname/view.php?id=$cm->id", format_string($cm->name), $attributes);

			$reportrow->cells[] = $activitycell;

			foreach($students as $t){
				$numviewscell = new html_table_cell();
				$numviewscell->attributes['class'] = 'numviews';
				if(isset($views[$cm->id][$t])){
					$numviewscell->text = $views[$cm->id][$t];
				}
				else{
					$numviewscell->text = '-';
				}
				$reportrow->cells[] = $numviewscell;
			}

			$studentstable->data[] = $reportrow;
		//}
    }
}
echo html_writer::table($studentstable);

echo $OUTPUT->footer();



