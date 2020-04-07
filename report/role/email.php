<?php
/*
 * Display user activity reports for a course (totals)
 *
 * @package    report
 * @subpackage role
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/report/role/locallib.php');

$PAGE->set_url('/report/role/index.php', array('id'=>$id));
$PAGE->set_pagelayout('report');

$stractivityreport = get_string('pluginname', 'report_role');
$stractivity       = get_string('activity');
$struser           = get_string('user');
$strevent          = get_string('eventname');
$strviews          = get_string('views');


//echo $OUTPUT->header();
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

//echo $OUTPUT->container(get_string('computedfromlogs', 'admin', userdate($minlog)), 'loginfo');


$sql = "SELECT ra.userid
		  FROM {role_assignments} ra, {context} c, {role} r, {" . $logtable . "} l
		 WHERE ra.contextid = c.id 
		   AND r.archetype in ('coursecreator', 'editingteacher', 'teacher')
		   AND ra.roleid = r.id 
		   /*AND l.userid = ra.userid */
		   AND l.courseid = c.instanceid 
		   AND l.timecreated > (UNIX_TIMESTAMP() - 24*60*60)
		 GROUP BY ra.userid";
$usersemail = $DB->get_records_sql($sql);

foreach($usersemail as $useremail){
	$USER = $DB->get_record('user', array('id'=>$useremail->userid), '*', MUST_EXIST);

	$saida = "";
	$saida .= '<html>
	<head>
	<title></title>
		<link rel="stylesheet" href="'.$CFG->wwwroot.'/report/role/email.css">
	</head>
	<body>';
	$sql = "SELECT l.id logid, l.courseid, l.contextinstanceid as cmid, l.userid, l.action, l.timecreated, concat(u.firstname, ' ', u.lastname) username, ra.roleid, r.archetype, l.target, l.eventname, l.component
				  FROM {" . $logtable . "} l, {user} u, {role_assignments} ra, {context} c, {role} r
				 WHERE l.courseid IN (select c.instanceid
									from {role_assignments} ra, {context} c
									where ra.contextid = c.id and ra.userid = :userid)
				   AND l.anonymous = 0 
				   AND l.contextlevel = '70'
				   AND l.userid <> :userid2
				   AND action <> 'viewed' and l.component <> 'core'
				   AND l.timecreated > (UNIX_TIMESTAMP() - 24*60*60)
				   AND l.userid = u.id AND u.id = ra.userid AND ra.contextid = c.id 
				   AND l.courseid = c.instanceid and ra.roleid = r.id
			  GROUP BY l.contextinstanceid, l.userid
			  ORDER BY l.timecreated";
			  
	$params = array('userid' => $useremail->userid, 'userid2' => $useremail->userid);
	$views = array();
	$views = $DB->get_records_sql($sql, $params);
	
	$reportcmids = array();
	foreach($views as $view){
		$reportcmids[$view->courseid][$view->cmid][] = $view;
	}


	foreach($reportcmids as $courseid => $reportcmid){
		$courseR = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
		$saida .= '<h3 style="background-color:#CCCCCC">'.$courseR->fullname.'</h3>';
		$modinfo = get_fast_modinfo($courseR);
		$roletable = new html_table();
		$roletable->attributes['class'] = 'generaltable boxaligncenter';
		$roletable->cellpadding = 5;
		$roletable->id = 'roletable';
		$roletable->head = array($stractivity.'/'.$struser,$strevent, $strviews);
		foreach ($modinfo->sections as $sectionnum=>$section) {
			foreach ($section as $cmid) {
			
				 if (isset($reportcmid[$cmid])) {
					$sectionrow = new html_table_row();
					$sectionrow->attributes['class'] = 'section';
					$sectioncell = new html_table_cell();
					$sectioncell->colspan = count($roletable->head);

					$cm = $modinfo->cms[$cmid];
					$modulename = get_string('modulename', $cm->modname);
					$cmidtitle = $modulename;

					$sectioncell->text = html_writer::link("$CFG->wwwroot/mod/$cm->modname/view.php?id=$cm->id", format_string($cm->name), $attributes);

					$sectionrow->cells[] = $sectioncell;
					$roletable->data[] = $sectionrow;
				}
				foreach($reportcmid[$cmid] as $vcmid => $view){

					$reportrow = new html_table_row();
					// Cria nova linha com o item acessado
					$usercell = new html_table_cell();
					$usercell->attributes['class'] = 'r1';
					if (isset($view->username)) {
						$usercell->text = '<li>'.$view->username;
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

					$roletable->data[] = $reportrow;
				}
			}
		}
		$saida .= html_writer::table($roletable);
	}
	$saida .= '</body></head>';

	if(count($reportcmids)>0){
		// multiple recipients
		$to  = $USER->email;
		// subject
		$subject = 'Moodle IFRS-BG - Atualizações';
		// message
		$message = '<p align="center"><img src="http://moodle.bento.ifrs.edu.br/report/role/top_moodle.png" width="100%"><br><h3 align="center">Confira as últimas atualizações em seus cursos do Moodle IFRS-BG</h3></p>'.$saida;
		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		// Additional headers
		$headers .= 'From: Moodle IFRS-BG <moodle@bento.ifrs.edu.br>' . "\r\n";
		// Mail it
		mail($to, $subject, $message, $headers);
		echo '<br>Email enviado para: '.$useremail->userid.' - '.$USER->firstname.' '.$USER->lastname;
		echo $message;
	}
}

//echo $OUTPUT->footer();
?>
