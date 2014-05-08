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
 * Library of functions and constants for module mtablabel
 *
 * @package    mod
 * @subpackage mtablabel
 * @copyright  2013 onwards C-DAC Mumbai {@link http://www.cdacmumbai.in}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/** MTABLABEL_MAX_NAME_LENGTH = 50 */
define("MTABLABEL_MAX_NAME_LENGTH", 50);

/**
 * @uses MTABLABEL_MAX_NAME_LENGTH
 * @param object $label
 * @return string
 */
function get_mtablabel_name($label) {
    $name = strip_tags(format_string($label->intro,true));
    if (textlib::strlen($name) > MTABLABEL_MAX_NAME_LENGTH) {
        $name = textlib::substr($name, 0, MTABLABEL_MAX_NAME_LENGTH)."...";
    }

    if (empty($name)) {
        // arbitrary name
        $name = get_string('modulename','mtablabel');
    }

    return $name;
}
/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @global object
 * @param object $label
 * @return bool|int
 */
function mtablabel_add_instance($label) {
    global $DB;

    $label->name = get_mtablabel_name($label);
    $label->timemodified = time();

    return $DB->insert_record("mtablabel", $label);
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @global object
 * @param object $label
 * @return bool
 */
function mtablabel_update_instance($label) {
    global $DB;

    $label->name = get_mtablabel_name($label);
    $label->timemodified = time();
    $label->id = $label->instance;

    return $DB->update_record("mtablabel", $label);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @global object
 * @param int $id
 * @return bool
 */
function mtablabel_delete_instance($id) {
    global $DB;

    if (! $label = $DB->get_record("mtablabel", array("id"=>$id))) {
        return false;
    }

    $result = true;

    if (! $DB->delete_records("mtablabel", array("id"=>$label->id))) {
        $result = false;
    }

    return $result;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 * See get_array_of_activities() in course/lib.php
 *
 * @global object
 * @param object $coursemodule
 * @return object|null
 */
function mtablabel_get_coursemodule_info($coursemodule) {
    global $DB;
	
	$sql = 'SELECT mtl.id, mtl.name, mtl.intro, mtl.introformat,
			mtli.filename AS iconfile, mtli.description AS icondesc
			FROM {mtablabel} mtl, {mtablabelicon} mtli
			WHERE mtl.id = :id AND mtl.iconid = mtli.id';

    if ($mtablabel = $DB->get_record_sql($sql, array('id'=>$coursemodule->instance))) {
        if (empty($mtablabel->name)) {
            //Tab label name missing, fix it
            $mtablabel->name = "mtablabel{$mtablabel->id}";
            $DB->set_field('mtablabel', 'name', $mtablabel->name, array('id'=>$mtablabel->id));
        }

        $info = new cached_cm_info();
        // no filtering here because this info is cached and filtered later
        $info->content = format_module_intro('mtablabel', $mtablabel, $coursemodule->id, false);
        $info->name  = $mtablabel->name;

        $iconinfo = array('iconfile' => $mtablabel->iconfile,'icondesc' => $mtablabel->icondesc);
        $info->customdata = $iconinfo;

        return $info;
    } else {
        return null;
    }
}

/**
 * @return array
 */
function mtablabel_get_view_actions() {
    return array();
}

/**
 * @return array
 */
function mtablabel_get_post_actions() {
    return array();
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 *
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function mtablabel_reset_userdata($data) {
    return array();
}

/**
 * Returns all other caps used in module
 *
 * @return array
 */
function mtablabel_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * @uses FEATURE_IDNUMBER
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return bool|null True if module supports feature, false if not, null if doesn't know
 */
function mtablabel_supports($feature) {
    switch($feature) {
        case FEATURE_IDNUMBER:                return false;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_NO_VIEW_LINK:            return true;

        default: return null;
    }
}

