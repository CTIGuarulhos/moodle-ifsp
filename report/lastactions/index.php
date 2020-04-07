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
 * @subpackage lastactions
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/report/lastactions/locallib.php');

$id = required_param('id',PARAM_INT);       // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

$PAGE->set_url('/report/lastactions/index.php', array('id'=>$id));
$PAGE->set_pagelayout('report');

require_login($course);
$context = context_course::instance($course->id);
require_capability('report/lastactions:view', $context);

// Trigger an activity report viewed event.
$event = \report_lastactions\event\activity_report_viewed::create(array('context' => $context));
$event->trigger();

$showlastaccess = true;
$hiddenfields = explode(',', $CFG->hiddenuserfields);

if (array_search('lastaccess', $hiddenfields) !== false and !has_capability('moodle/user:viewhiddendetails', $context)) {
    $showlastaccess = false;
}

$stractivityreport = get_string('pluginname', 'report_lastactions');
$stractivity       = get_string('activity');
$struser           = get_string('user');
$strviews          = get_string('views');

$PAGE->set_title($course->shortname .': '. $stractivityreport);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($stractivityreport);


list($uselegacyreader, $useinternalreader, $minloginternalreader, $logtable) = report_lastactions_get_common_log_variables();

// If no legacy and no internal log then don't proceed.
if (!$uselegacyreader && !$useinternalreader) {
    echo $OUTPUT->box_start('generalbox', 'notice');
    echo $OUTPUT->notification(get_string('nologreaderenabled', 'report_lastactions'));
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    die();
}


echo $OUTPUT->container('Actions of last week');




// Get record from sql_internal_reader and merge with records obtained from legacy log (if needed).
if ($useinternalreader) {
	if($course->id <> 1){
		$sql = "SELECT l.id logid, l.courseid, l.contextinstanceid as cmid, l.userid, l.action, l.timecreated, concat(u.firstname, ' ', u.lastname) username, ra.roleid, r.archetype, l.target, l.eventname, l.component
				  FROM {" . $logtable . "} l, {user} u, {role_assignments} ra, {context} c, {role} r
				 WHERE l.courseid = :courseid 
				   AND l.anonymous = 0 
				   AND l.contextlevel = :contextmodule 
				   AND l.userid <> :userid
				   AND action <> 'viewed' and l.component <> 'core'
				   AND l.timecreated > (UNIX_TIMESTAMP() - 24*60*60*7)
				   AND l.userid = u.id AND u.id = ra.userid AND ra.contextid = c.id 
				   AND l.courseid = c.instanceid and ra.roleid = r.id
			  GROUP BY l.contextinstanceid, l.userid
			  ORDER BY l.timecreated";
		$params = array('courseid' => $course->id, 'contextmodule' => CONTEXT_MODULE, 'userid' => $USER->id);
	}
	else{
		$sql = "SELECT l.id logid, l.courseid, l.contextinstanceid as cmid, l.userid, l.action, l.timecreated, concat(u.firstname, ' ', u.lastname) username, ra.roleid, r.archetype, l.target, l.eventname, l.component
				  FROM {" . $logtable . "} l, {user} u, {role_assignments} ra, {context} c, {role} r
				 WHERE l.courseid IN (select c.instanceid
									from {role_assignments} ra, {context} c
									where ra.contextid = c.id and ra.userid = :userid)
				   AND l.anonymous = 0 
				   AND l.contextlevel = :contextmodule 
				   AND l.userid <> :userid2
				   AND action <> 'viewed' and l.component <> 'core'
				   AND l.timecreated > (UNIX_TIMESTAMP() - 24*60*60*7)
				   AND l.userid = u.id AND u.id = ra.userid AND ra.contextid = c.id 
				   AND l.courseid = c.instanceid and ra.roleid = r.id
			  GROUP BY l.contextinstanceid, l.userid
			  ORDER BY l.timecreated";
		$params = array('userid' => $USER->id, 'contextmodule' => CONTEXT_MODULE, 'userid2' => $USER->id);
	}
    $views = $DB->get_records_sql($sql, $params);
	foreach($views as $view){
		$reportcmids[$view->courseid][$view->cmid][] = $view;
	}
}

foreach($reportcmids as $courseid => $reportcmid){
	$courseR = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
	echo $OUTPUT->container(format_string($courseR->fullname), null, 'title-course');
	$modinfo = get_fast_modinfo($courseR);
	$lastactionstable = new html_table();
	$lastactionstable->attributes['class'] = 'generaltable boxaligncenter';
	$lastactionstable->cellpadding = 5;
	$lastactionstable->id = 'lastactionstable';
	$lastactionstable->head = array($stractivity, $struser, $strviews);
	foreach ($modinfo->sections as $sectionnum=>$section) {
		foreach ($section as $cmid) {
		
			 if (isset($reportcmid[$cmid])) {
				$sectionrow = new html_table_row();
				$sectionrow->attributes['class'] = 'section';
				$sectioncell = new html_table_cell();
				$sectioncell->colspan = count($lastactionstable->head);

				$cm = $modinfo->cms[$cmid];
				$modulename = get_string('modulename', $cm->modname);
				$cmidtitle = $modulename;

				$activityicon = $OUTPUT->pix_icon('icon', $modulename, $cm->modname, array('class'=>'icon'));
				$sectioncell->text = $activityicon . html_writer::link("$CFG->wwwroot/mod/$cm->modname/view.php?id=$cm->id", format_string($cm->name), $attributes);

				$sectionrow->cells[] = $sectioncell;
				$lastactionstable->data[] = $sectionrow;
			}
			foreach($reportcmid[$cmid] as $vcmid => $view){

				$reportrow = new html_table_row();
				// Cria nova linha com o item acessado
				$usercell = new html_table_cell();
				$usercell->attributes['class'] = 'r1';
				if (isset($view->username)) {
					$usercell->text = $view->username;
				}
				$reportrow->cells[] = $usercell;

				
				// Cria nova linha com o item acessado
				$eventnamecell = new html_table_cell();
				$eventnamecell->attributes['class'] = 'r1';
				if (isset($view->eventname)) {
					$term = str_replace('_','',$view->target.$view->action);
					$eventname = get_string('event'.$term, $view->component);
					if(substr($eventname,0,2) == '[['){
						$eventname = get_string('event'.$term);
						if(substr($eventname,0,2) == '[['){
							//eventquizattemptstarted
							$term = str_replace('_','',$view->target.$view->action);
							$term2 = str_replace('mod_','',$view->component);
							$eventname = get_string('event'.$term2.$term, $view->component);
						}
					}
					$eventnamecell->text = $eventname;
					/*eventassessableuploaded
					\mod_forum\event\assessable_uploaded*/
				}
				$reportrow->cells[] = $eventnamecell;

				// Cria nova linha com o item acessado
				$timecreatedcell = new html_table_cell();
				$timecreatedcell->attributes['class'] = 'r1';
				if (isset($view->timecreated)) {
					$timecreatedcell->text = userdate($view->timecreated);
				}
				$reportrow->cells[] = $timecreatedcell;

				$lastactionstable->data[] = $reportrow;
			}
		}
	}
	echo html_writer::table($lastactionstable);
}


echo $OUTPUT->footer();