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
 * This file contains general functions for the course format M-Tabs
 *
 * @package    format_mtabs
 * @copyright  2013 onwards C-DAC Mumbai {@link http://www.cdacmumbai.in}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Indicates this format uses sections.
 *
 * @return bool Returns true
 */
function callback_mtabs_uses_sections() {
    return true;
}

/**
 * Used to display the course structure for a course where format=topic
 *
 * This is called automatically by {@link load_course()} if the current course
 * format = weeks.
 *
 * @param array $path An array of keys to the course node in the navigation
 * @param stdClass $modinfo The mod info object for the current course
 * @return bool Returns true
 */
function callback_mtabs_load_content(&$navigation, $course, $coursenode) {
    return $navigation->load_generic_course_sections($course, $coursenode, 'mtabs');
}

/**
 * The string that is used to describe a section of the course
 * e.g. Topic, Week...
 *
 * @return string
 */
function callback_mtabs_definition() {
    return '';//get_string('topic');
}

function callback_mtabs_get_section_name($course, $section) {
    // We can't add a node without any text
    if ((string)$section->name !== '') {
        return format_string($section->name, true, array('context' => get_context_instance(CONTEXT_COURSE, $course->id)));
    } else if ($section->section == 0) {
        return get_string('section0name', 'format_mtabs');
    } else {
        return get_string('topic').' '.$section->section;
    }
}

/**
 * Declares support for course AJAX features
 *
 * @see course_format_ajax_support()
 * @return stdClass
 */
function callback_mtabs_ajax_support() {
    $ajaxsupport = new stdClass();
    $ajaxsupport->capable = true;
    $ajaxsupport->testedbrowsers = array('MSIE' => 6.0, 'Gecko' => 20061111, 'Safari' => 531, 'Chrome' => 6.0);
    return $ajaxsupport;
}

/**
 * Callback function to do some action after section move
 *
 * @param stdClass $course The course entry from DB
 * @return array This will be passed in ajax respose.
 */
function callback_mtabs_ajax_section_move($course) {
    global $COURSE, $PAGE;

    $titles = array();
    rebuild_course_cache($course->id);
    $modinfo = get_fast_modinfo($COURSE);
    $renderer = $PAGE->get_renderer('format_mtabs');
    if ($renderer && ($sections = $modinfo->get_section_info_all())) {
        foreach ($sections as $number => $section) {
            $titles[$number] = $renderer->section_title($section, $course);
        }
    }
    return array('sectiontitles' => $titles, 'action' => 'move');
}

/**
 * @param array $sections
 * @param array $mods
 * @return bool
 */
function is_sections_mtabs_compatible($sections, $mods)
{
    global $COURSE;
    $status = true;
    $malformedsections = '';
    foreach($sections as $section)
    {
        if(empty($section->sequence))
	{
	    continue;
	}
	
        $sectionmods = explode(',', $section->sequence);
	$section_first_mod = null;
	
        foreach($sectionmods as $sectionmod)
        {
            $section_first_mod = $mods[$sectionmod];

            if(!empty($section_first_mod))
            {
                break;
            }
        }

        if(($section_first_mod->modname === 'mtablabel' && $section_first_mod->indent === 0) || $section->section == '0')
        {
            $status &= true;
        }
        else
        {
            $status &= false;
	    $malformedsections .= (empty($malformedsections) ? $section->section : (', ' . $section->section));
        }
	
	if($section->section == $COURSE->numsections)
	{
	    break;
	}
    }
    
    if(!$status)
    {
	echo '<h1>Section(s) ' . $malformedsections . ' are not well-formed...</h1>';
    }

    return $status;
}
