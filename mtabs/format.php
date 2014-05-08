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
 * @package    format_mtabs
 * @copyright  2013 onwards C-DAC Mumbai {@link http://www.cdacmumbai.in}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/completionlib.php');

global $PAGE;

// Horrible backwards compatible parameter aliasing..
if($topic = optional_param('topic', 0, PARAM_INT))
{
    $url = $PAGE->url;
    $url->param('section', $topic);
    debugging('Outdated topic param passed to course/view.php', DEBUG_DEVELOPER);
    redirect($url);
}
// End backwards-compatible aliasing..

$context = context_course::instance($course->id);

if(($marker >= 0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey())
{
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

$renderer = $PAGE->get_renderer('format_mtabs');

$formatconfig = $CFG->dirroot . '/course/format/mtabs/config.php';
$format = array(); // initialize array in external file
if(is_readable($formatconfig))
{
    include($formatconfig);
}

if(isset($format['mtabs_in_section0']))
{
    $renderer->mtabs_in_section0 = $format['mtabs_in_section0'];
}
else
{
    $renderer->mtabs_in_section0 = true;
}

if($renderer->mtabs_in_section0)
{
    $section0tab = new stdClass();
    $section0tab->name = 'General Information';
    $section0tab->modname = 'mtablabel';
    $section0tab->iconinfo = array('iconfile' => 'glossary.png', 'icondesc' => 'Glossary of the Chapter or Section or Topic');

    $renderer->section0tab = $section0tab;
}

//Check whether all sections (except section-0) are well structured for M-Tabs format or not
if(!$PAGE->user_is_editing() && !is_sections_mtabs_compatible($sections, $mods))
{
    redirect(new moodle_url('/course/view.php', array('id' => $course->id, 'sesskey' => sesskey(), 'edit' => '1')), get_string('improperlystructured', 'format_mtabs'));
}

if(!empty($displaysection))
{
    $renderer->print_single_section_page($course, $modinfo->get_section_info_all(), $mods, $modnames, $modnamesused, $displaysection);
}
else
{
    $renderer->print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused);
}

// Include course format js module
$PAGE->requires->js('/course/format/mtabs/format.js');
if($PAGE->user_is_editing())
{
	$PAGE->requires->js('/course/format/mtabs/js/mtabs-toolbox.js');
}

