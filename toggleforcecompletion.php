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
 * Toggles the force completion of an activity by a teacher/admin.
 * and the current user.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package course
 */

require_once('../../config.php');
require_once($CFG->libdir.'/completionlib.php');

// Parameters
$cmid = optional_param('id', 0, PARAM_INT);
$criteriaid = optional_param('criteriaid', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);

if (!$userid || !$cmid) {
    print_error('invalidarguments');
}

$targetstate = required_param('forcecompletionstate', PARAM_INT);
$fromajax    = optional_param('fromajax', 0, PARAM_INT);

$PAGE->set_url('/course/toggleforcecompletion.php', array('id'=>$cmid, 'forcecompletionstate'=>$targetstate));

switch($targetstate) {
    case COMPLETION_COMPLETE:
    case COMPLETION_UNKNOWN:
        break;
    default:
        print_error('unsupportedstate');
}

// Get course-modules entry
$cm = get_coursemodule_from_id(null, $cmid, null, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

// Check user is logged in
require_login($course, false, $cm);

if (isguestuser() or !confirm_sesskey()) {
    print_error('error');
}

// Now change state
$completion = new completion_info($course);
if (!$completion->is_enabled()) {
    die;
}

// Check completion state is manual
if($cm->completion != COMPLETION_TRACKING_AUTOMATIC) {
    error_or_ajax('completion:notautomatictrack', $fromajax);
}



//$completion->update_state($cm, $targetstate, $userid, 1);

// Get current value of completion state and do nothing if it's same as
// the possible result of this change. If the change is to COMPLETE and the
// current value is one of the COMPLETE_xx subtypes, ignore that as well
$current = $completion->get_data($cm, false, $userid);

$newstate = ($targetstate == COMPLETION_UNKNOWN) ? $completion->internal_get_state($cm, $userid, $current) : $targetstate;
$current->forced = ($targetstate == COMPLETION_UNKNOWN) ? '0' : '1';
$current->completionstate = $newstate;
$current->timemodified    = time();
$completion->internal_set_data($cm, $current);

if ($targetstate == COMPLETION_COMPLETE) {
/*
	// immediately mark completion criteria as complete
	$r = array('course'=>$cm->course,'criteriaid'=>$criteriaid,'userid'=>$userid,'timecompleted'=>time());
	$critcompletion = new completion_criteria_completion($r, DATA_OBJECT_FETCH_BY_KEY);
	$critcompletion->mark_complete($r->timecompleted);	
*/
} elseif ($newstate != COMPLETION_COMPLETE && $newstate != COMPLETION_COMPLETE_PASS) {
	//remove completion criteria completion record so cron can take care of it
	$DB->delete_records_select('course_completion_crit_compl', 'course = ? && criteriaid = ? && userid = ?', array($cm->course,$criteriaid,$userid));
}

// flag course completion to reaggregate
// if un-setting a forced complete, unset the timecompleted
$ccompletion = new completion_completion(array('course' => $cm->course, 'userid' => $userid));
if ($newstate != COMPLETION_COMPLETE && $newstate != COMPLETION_COMPLETE_PASS)
	$ccompletion->timecompleted = null;
$ccompletion->mark_inprogress();

// And redirect back to course
if ($fromajax) {
    print 'OK';
} else {
    // In case of use in other areas of code we allow a 'backto' parameter,
    // otherwise go back to course page
    $backto = optional_param('backto', $CFG->wwwroot.'/report/completion/index.php?course='.$course->id, PARAM_URL);
    redirect($backto);
}

// utility functions

function error_or_ajax($message, $fromajax) {
    if ($fromajax) {
        print get_string($message, 'report_completion');
        exit;
    } else {
        print_error($message, 'report_completion');
    }
}

