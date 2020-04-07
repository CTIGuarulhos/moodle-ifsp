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
 * @subpackage role
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/report/role/locallib.php');

$id = required_param('id',PARAM_INT);       // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

$PAGE->set_url('/report/role/index.php', array('id'=>$id));
$PAGE->set_pagelayout('report');

require_login($course);
$context = context_course::instance($course->id);
require_capability('report/role:view', $context);

// Trigger an activity report viewed event.
$event = \report_role\event\activity_report_viewed::create(array('context' => $context));
$event->trigger();

$showlastaccess = true;
$hiddenfields = explode(',', $CFG->hiddenuserfields);

if (array_search('lastaccess', $hiddenfields) !== false and !has_capability('moodle/user:viewhiddendetails', $context)) {
    $showlastaccess = false;
}

$stractivityreport = get_string('pluginname', 'report_role');
$strmail       = get_string('email');
$struser       = get_string('user');
$strcourse     = get_string('courses');

$PAGE->set_title($course->shortname .': '. $stractivityreport);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($stractivityreport);


list($uselegacyreader, $useinternalreader, $minloginternalreader, $logtable) = report_role_get_common_log_variables();

// If no legacy and no internal log then don't proceed.
if (!$uselegacyreader && !$useinternalreader) {
    echo $OUTPUT->box_start('generalbox', 'notice');
    echo $OUTPUT->notification(get_string('nologreaderenabled', 'report_role'));
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    die();
}

##############
## TEACHERS ##
##############



// Get record from sql_internal_reader and merge with records obtained from legacy log (if needed).
$sql = "SELECT u.id, concat(u.firstname, ' ', u.lastname) username, u.email, count(1) cont
		  FROM {role_assignments} ra, {context} c, {role} r, {user} u
		 WHERE ra.contextid = c.id 
		   AND ra.roleid = r.id 
		   AND r.archetype in ('coursecreator', 'editingteacher', 'teacher')
		   AND ra.userid = u.id
	  GROUP BY u.id
	  ORDER BY username";
$views = $DB->get_records_sql($sql, $params);

echo $OUTPUT->container(get_string('teachers')."(".count($views).")");


$roletable = new html_table();
$roletable->attributes['class'] = 'generaltable boxaligncenter';
$roletable->cellpadding = 5;
$roletable->id = 'roletable';
$roletable->head = array($struser, $strmail, $strcourse);
$emailList = "";
foreach($views as $view){
	$reportrow = new html_table_row();
	
	// Cria nova linha com o item acessado
	$usercell = new html_table_cell();
	$usercell->attributes['class'] = 'r1';
	if (isset($view->username)) {
		$usercell->text = $view->username;
	}
	$reportrow->cells[] = $usercell;
	// Cria nova linha com o item acessado
	$usercell = new html_table_cell();
	$usercell->attributes['class'] = 'r1';
	if (isset($view->email)) {
		$usercell->text = $view->email;
		$emailList .= $view->email.", ";
	}
	$reportrow->cells[] = $usercell;
	// Cria nova linha com o item acessado
	$usercell = new html_table_cell();
	$usercell->attributes['class'] = 'r1';
	if (isset($view->cont)) {
		$usercell->text = $view->cont;
	}
	$reportrow->cells[] = $usercell;
	

	$roletable->data[] = $reportrow;


}


echo html_writer::table($roletable);
echo get_string('email').' '.$emailList;


##############
## STUDENTS ##
##############

// Get record from sql_internal_reader and merge with records obtained from legacy log (if needed).
$sql = "SELECT u.id, concat(u.firstname, ' ', u.lastname) username, u.email, count(1) cont
		  FROM {role_assignments} ra, {context} c, {role} r, {user} u
		 WHERE ra.contextid = c.id 
		   AND ra.roleid = r.id 
		   AND r.archetype in ('student')
		   AND ra.userid = u.id
	  GROUP BY u.id
	  ORDER BY username";
$views = $DB->get_records_sql($sql, $params);

echo $OUTPUT->container(get_string('students')."(".count($views).")");

$roletable = new html_table();
$roletable->attributes['class'] = 'generaltable boxaligncenter';
$roletable->cellpadding = 5;
$roletable->id = 'roletable';
$roletable->head = array($struser, $strmail, $strcourse);
$emailList = "";
foreach($views as $view){
	$reportrow = new html_table_row();
	
	// Cria nova linha com o item acessado
	$usercell = new html_table_cell();
	$usercell->attributes['class'] = 'r1';
	if (isset($view->username)) {
		$usercell->text = $view->username;
	}
	$reportrow->cells[] = $usercell;
	// Cria nova linha com o item acessado
	$usercell = new html_table_cell();
	$usercell->attributes['class'] = 'r1';
	if (isset($view->email)) {
		$usercell->text = $view->email;
		$emailList .= $view->email.", ";
	}
	$reportrow->cells[] = $usercell;
	// Cria nova linha com o item acessado
	$usercell = new html_table_cell();
	$usercell->attributes['class'] = 'r1';
	if (isset($view->cont)) {
		$usercell->text = $view->cont;
	}
	$reportrow->cells[] = $usercell;
	

	$roletable->data[] = $reportrow;


}


echo html_writer::table($roletable);
echo get_string('email').' '.$emailList;

echo $OUTPUT->footer();