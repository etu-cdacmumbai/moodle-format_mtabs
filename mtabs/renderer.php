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
 * Renderer for outputting the M-Tabs course format.
 *
 * @package    format_mtabs
 * @copyright  2013 onwards C-DAC Mumbai {@link http://www.cdacmumbai.in}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/format/renderer.php');

/**
 * Basic renderer for topics format.
 *
 * @copyright 2012 CDAC Mumbai
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_mtabs_renderer extends format_section_renderer_base
{

    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list()
    {
        return html_writer::start_tag('div', array('class' => 'moodle-tab-module'));
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list()
    {
        return html_writer::end_tag('div');
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     */
    protected function page_title()
    {
	return get_string('mtabsoutline', 'format_mtabs');
    }

    /**
     * Generate the edit controls of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of links with edit controls
     */
    protected function section_edit_controls($course, $section, $onsectionpage = false)
    {
        global $PAGE;

        if(!$PAGE->user_is_editing())
        {
            return array();
        }

        if(!has_capability('moodle/course:update', context_course::instance($course->id)))
        {
            return array();
        }

        if($onsectionpage)
        {
            $url = course_get_url($course, $section->section);
        }
        else
        {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

        $controls = array();
        if($course->marker == $section->section)
        {  // Show the "light globe" on/off.
            $url->param('marker', 0);
            $controls[] = html_writer::link($url, html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/marked'),
                                'class' => 'icon ', 'alt' => get_string('markedthistopic'))), array('title' => get_string('markedthistopic'), 'class' => 'editing_highlight'));
        }
        else
        {
            $url->param('marker', $section->section);
            $controls[] = html_writer::link($url, html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/marker'),
                                'class' => 'icon', 'alt' => get_string('markthistopic'))), array('title' => get_string('markthistopic'), 'class' => 'editing_highlight'));
        }

        return array_merge($controls, parent::section_edit_controls($course, $section, $onsectionpage));
    }

    /**
     * Generate the display of the header part of a section before
     * course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a single-section page
     * @param int $sectionreturn The section to return to after an action
     * @return string HTML to output.
     */
    protected function section_header($section, $course, $onsectionpage, $sectionreturn=0)
    {
        global $PAGE;

        $o = '';
        $currenttext = '';
        $sectionstyle = '';

        if ($section->section != 0)
        {
            // Only in the non-general sections.
            if (!$section->visible)
            {
                $sectionstyle = ' hidden';
            }
            else if ($this->is_section_current($section, $course))
            {
                $sectionstyle = ' current';
            }

            if(isset($section->displaysection) && $section->displaysection)
            {
                $sectionstyle = ' displaysection';
            }
        }

        $o.= html_writer::start_tag('div', array('id' => 'chapter-' . ($section->section), 'sectionnum' => $section->section, 'class' => 'course-section' . $sectionstyle));

        return $o;
    }

    /**
     * Generate the display of the footer part of a section
     *
     * @return string HTML to output.
     */
    protected function section_footer()
    {
        $o = html_writer::end_tag('div');

        return $o;
    }

    /**
     * Generate the html for a hidden section
     *
     * @param int $sectionnum The section number in the coruse which is being dsiplayed
     * @return string HTML to output.
     */
    protected function section_hidden($sectionnum) {
        $o = '';
        $o.= html_writer::start_tag('div', array('id' => 'chapter-'.$sectionnum, 'sectionnum' => $sectionnum, 'class' => 'course-section hidden'));
        $o.= html_writer::start_tag('div', array('class' => 'tab-wrapper'));
        $o.= html_writer::tag('span', get_string('notavailable'), array('class' => 'hidden-section-msg'));
        $o.= html_writer::end_tag('div');
        $o.= html_writer::end_tag('div');
        return $o;
    }

    /**
     * Generate the html for a empty section
     *
     * @param int $sectionnum The section number in the course which is being dsiplayed
     * @return string HTML to output.
     */
    protected function section_empty($sectionnum) {
        $o = '';
        $o.= html_writer::start_tag('div', array('id' => 'chapter-'.$sectionnum, 'sectionnum' => $sectionnum, 'class' => 'course-section'));
        $o.= html_writer::start_tag('div', array('class' => 'tab-wrapper'));
        $o.= html_writer::tag('span', get_string('sectionempty','format_mtabs'), array('class' => 'empty-section-msg'));
        $o.= html_writer::end_tag('div');
        $o.= html_writer::end_tag('div');
        return $o;
    }


    /**
     * Generate a summary of a section for display on the 'course index page'
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param array    $mods course modules indexed by id (from get_all_mods)
     * @return string HTML to output.
     */
    protected function section_summary($section, $course, $mods) {
        $classattr = 'course-section section-summary clearfix';
        $linkclasses = '';

        // If section is hidden then display grey section link
        if (!$section->visible) {
            $classattr .= ' hidden';
            $linkclasses .= ' dimmed_text';
        } else if ($this->is_section_current($section, $course)) {
            $classattr .= ' cursection';
        }

        $o = '';
        $o .= html_writer::start_tag('div', array('id' => 'chapter-'.$section->section, 'class' => $classattr));
        $o .= html_writer::tag('div', '', array('id' => 'chapter-1-tabs', 'class' => 'moodle-tabs-wrap'));
        $o .= html_writer::start_tag('div', array('class' => 'tab-wrapper'));

        $title = get_section_name($course, $section);
        if ($section->uservisible) {
            $title = html_writer::tag('a', $title,
                array('href' => course_get_url($course, $section->section), 'class' => $linkclasses));
        }
        $o .= $this->output->heading($title, 3, 'section-title');

        $o .= html_writer::start_tag('div', array('class' => 'summarytext'));
        $o .= $this->format_summary_text($section);
        $o .= html_writer::end_tag('div');
        $o .= $this->section_activity_summary($section, $course, $mods);

        $o .= $this->section_availability_message($section);

        $o .= html_writer::end_tag('div');
        $o .= html_writer::end_tag('div');

        return $o;
    }

    /**
     * Generate a summary of the activites in a section
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course the course record from DB
     * @param array    $mods course modules indexed by id (from get_all_mods)
     * @return string HTML to output.
     */
    protected function section_activity_summary($section, $course, $mods)
    {
        if (empty($section->sequence))
        {
            return '';
        }

        // Generate array with count of activities in this section:
        $sectionmods = array();
        $total = 0;
        $complete = 0;
        $cancomplete = isloggedin() && !isguestuser();
        $completioninfo = new completion_info($course);
        $modsequence = explode(',', $section->sequence);
        foreach ($modsequence as $cmid)
        {
            $thismod = $mods[$cmid];

            if ($thismod->modname == 'label' || $thismod->modname == 'mtablabel')
            {
                // Labels are special (not interesting for students)!
                continue;
            }

            if ($thismod->uservisible)
            {
                if (isset($sectionmods[$thismod->modname]))
                {
                    $sectionmods[$thismod->modname]['count']++;
                }
                else
                {
                    $sectionmods[$thismod->modname]['name'] = $thismod->modplural;
                    $sectionmods[$thismod->modname]['count'] = 1;
                }
                if ($cancomplete && $completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE)
                {
                    $total++;
                    $completiondata = $completioninfo->get_data($thismod, true);
                    if ($completiondata->completionstate == COMPLETION_COMPLETE)
                    {
                        $complete++;
                    }
                }
            }
        }

        if (empty($sectionmods))
        {
            // No sections
            return '';
        }

        // Output section activities summary:
        $o = '';
        $o.= html_writer::start_tag('div', array('class' => 'section-summary-activities mdl-right'));
        foreach ($sectionmods as $mod)
        {
            $o.= html_writer::start_tag('span', array('class' => 'activity-count'));
            $o.= $mod['name'].': '.$mod['count'];
            $o.= html_writer::end_tag('span');
        }
        $o.= html_writer::end_tag('div');

        // Output section completion data
        if ($total > 0)
        {
            $a = new stdClass;
            $a->complete = $complete;
            $a->total = $total;

            $o.= html_writer::start_tag('div', array('class' => 'section-summary-activities mdl-right'));
            $o.= html_writer::tag('span', get_string('progresstotal', 'completion', $a), array('class' => 'activity-count'));
            $o.= html_writer::end_tag('div');
        }

        return $o;
    }

    /**
     * Generate next/previous section links for naviation
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections The course_sections entries from the DB
     * @param int $sectionno The section number in the coruse which is being dsiplayed
     * @return array associative array with previous and next section link
     */
    protected function get_nav_links($course, $sections, $sectionno)
    {
        // FIXME: This is really evil and should by using the navigation API.
        $canviewhidden = has_capability('moodle/course:viewhiddensections', context_course::instance($course->id))
            or !$course->hiddensections;

        $links = array('previous' => '', 'next' => '');
        $back = $sectionno - 1;
        while ($back > 0 and empty($links['previous']))
        {
            if ($canviewhidden || $sections[$back]->uservisible)
            {
                $params = array();
                if (!$sections[$back]->visible)
                {
                    $params = array('class' => 'dimmed_text');
                }
                $previouslink = html_writer::tag('span', $this->output->larrow(), array('class' => 'larrow'));
                $previouslink .= get_section_name($course, $sections[$back]);
                $links['previous'] = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id, 'section' => $back)), $previouslink, $params);
            }
            $back--;
        }

        $forward = $sectionno + 1;
        while ($forward <= $course->numsections and empty($links['next']))
        {
            if ($canviewhidden || $sections[$forward]->uservisible)
            {
                $params = array();
                if (!$sections[$forward]->visible)
                {
                    $params = array('class' => 'dimmed_text');
                }
                $nextlink = get_section_name($course, $sections[$forward]);
                $nextlink .= html_writer::tag('span', $this->output->rarrow(), array('class' => 'rarrow'));
                $links['next'] = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id, 'section' => $forward)), $nextlink, $params);
            }
            $forward++;
        }

        return $links;
    }

    /**
     * Print single section only on a page
     * 
     * @param $course
     * @param $sections
     * @param $mods
     * @param $modnames
     * @param $modnamesused
     * @param $displaysection
     * @return void
     */
    public function print_single_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection = 0)
    {
        global $PAGE, $COURSE;

        //If User is not editing the course, then print the multiple sections in a single page.
        if(!$PAGE->user_is_editing())
        {
            $this->print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection);
            return;
        }

        // Can we view the section in question?
        $context = context_course::instance($course->id);
        $canviewhidden = has_capability('moodle/course:viewhiddensections', $context);

        if (!isset($sections[$displaysection])) {
            // This section doesn't exist
            print_error('unknowncoursesection', 'error', null, $course->fullname);
            return;
        }

        if (!$sections[$displaysection]->visible && !$canviewhidden) {
            if (!$course->hiddensections) {
                echo html_writer::start_tag('ul', array('class' => 'topics'));
                echo parent::section_hidden($displaysection);
                echo html_writer::end_tag('ul');
            }
            // Can't view this section.
            return;
        }

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, $displaysection);

        // General section if non-empty.
        $thissection = $sections[0];
        if ($thissection->summary or $thissection->sequence or $PAGE->user_is_editing()) {
            echo html_writer::start_tag('ul', array('class' => 'topics'));
            echo parent::section_header($thissection, $course, true, $displaysection);

            print_section($course, $thissection, $mods, $modnamesused, true, "100%", false, $displaysection);
            print_section_add_menus($course, 0, $modnames, false, false, $displaysection);

            echo parent::section_footer();
            echo html_writer::end_tag('ul');
        }

        // Start single-section div
        echo html_writer::start_tag('div', array('class' => 'single-section'));

        // Title with section navigation links.
        $sectionnavlinks = $this->get_nav_links($course, $sections, $displaysection);
        $sectiontitle = '';
        $sectiontitle .= html_writer::start_tag('div', array('class' => 'section-navigation header headingblock'));
        $sectiontitle .= html_writer::tag('span', $sectionnavlinks['previous'], array('class' => 'mdl-left'));
        $sectiontitle .= html_writer::tag('span', $sectionnavlinks['next'], array('class' => 'mdl-right'));
        // Title attributes
        $titleattr = 'mdl-align title';
        if (!$sections[$displaysection]->visible) {
            $titleattr .= ' dimmed_text';
        }
        $sectiontitle .= html_writer::tag('div', get_section_name($course, $sections[$displaysection]), array('class' => $titleattr));
        $sectiontitle .= html_writer::end_tag('div');
        echo $sectiontitle;

        // Now the list of sections..
        echo html_writer::start_tag('ul', array('class' => 'topics'));

        // The requested section page.
        $thissection = $sections[$displaysection];
        echo parent::section_header($thissection, $course, true, $displaysection);
        // Show completion help icon.
        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();

        print_section($course, $thissection, $mods, $modnamesused, true, '100%', false, $displaysection);
        print_section_add_menus($course, $displaysection, $modnames, false, false, $displaysection);

        echo parent::section_footer();
        echo html_writer::end_tag('ul');

        // Display section bottom navigation.
        $courselink = html_writer::link(course_get_url($course), get_string('returntomaincoursepage'));
        $sectionbottomnav = '';
        $sectionbottomnav .= html_writer::start_tag('div', array('class' => 'section-navigation mdl-bottom'));
        $sectionbottomnav .= html_writer::tag('span', $sectionnavlinks['previous'], array('class' => 'mdl-left'));
        $sectionbottomnav .= html_writer::tag('span', $sectionnavlinks['next'], array('class' => 'mdl-right'));
        $sectionbottomnav .= html_writer::tag('div', $courselink, array('class' => 'mdl-align'));
        $sectionbottomnav .= html_writer::end_tag('div');
        echo $sectionbottomnav;

        // close single-section div.
        echo html_writer::end_tag('div');
    }

    /**
     * Print multiple sections on a page
     * 
     * @param $course
     * @param $sections
     * @param $mods
     * @param $modnames
     * @param $modnamesused
     * @param $displaysection
     * @return void
     */
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection = 0)
    {
        global $PAGE;

        $context = context_course::instance($course->id);
        // Title with completion help icon.
        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course);

        if($PAGE->user_is_editing())
        {
            echo html_writer::start_tag('ul', array('class' => 'topics'));
        }
        else
        {
            echo html_writer::start_tag('div', array('class' => 'course-wrapper'));

            //Print the Course Full name
            echo html_writer::nonempty_tag('h2', $course->fullname, array('class' => 'course-title'));

            //Print the bread crumb symbol
            echo html_writer::nonempty_tag('span', '►', array('class' => 'bread-crumb'));

            //Print list of Sections/Topics/Chapters... as a Combo box
            echo $this->course_sections_select_list($sections, $course->numsections);

            echo html_writer::end_tag('div');

            // Now the list of sections..
            echo $this->start_section_list();
        }

        //This will set section to be displayed when page loads
        $cursection = $sections[$displaysection];
        if(!empty($cursection))
        {
            $cursection->displaysection = true;
        }

        // General section if non-empty.
        $thissection = $sections[0];
        unset($sections[0]);
        if($thissection->summary or $thissection->sequence or $PAGE->user_is_editing())
        {
            if($PAGE->user_is_editing())
            {
                echo parent::section_header($thissection, $course, false);

                print_section($course, $thissection, $mods, $modnamesused, true);

                print_section_add_menus($course, 0, $modnames);

                echo parent::section_footer();
            }
            else
            {
                echo $this->section_header($thissection, $course, false);

                if(!$this->mtabs_in_section0)
                {
                    echo html_writer::start_tag('div', array('class' => 'tab-wrapper'));
                    echo html_writer::start_tag('div', array('class' => 'section0-tab-content'));
                    print_section($course, $thissection, $mods, $modnamesused, true);
                    echo html_writer::end_tag('div');
                    echo html_writer::end_tag('div');
                }
                else
                {
                    $this->print_mtabs_section($course, $thissection, $mods, $modnamesused);
                }

                echo $this->section_footer();
            }
        }

        $canviewhidden = has_capability('moodle/course:viewhiddensections', $context);
        for($section = 1; $section <= $course->numsections; $section++)
        {
            if(!empty($sections[$section]))
            {
                $thissection = $sections[$section];
            }
            else
            {
                // This will create a course section if it doesn't exist..
                $thissection = get_course_section($section, $course->id);

                // The returned section is only a bare database object rather than
                // a section_info object - we will need at least the uservisible
                // field in it.
                $thissection->uservisible = true;
                $thissection->availableinfo = null;
                $thissection->showavailability = 0;
            }

            // Show the section if the user is permitted to access it, OR if it's not available
            // but showavailability is turned on
            $showsection = $thissection->uservisible || ($thissection->visible && !$thissection->available && $thissection->showavailability);

            if(!$showsection)
            {
                // Hidden section message is overridden by 'unavailable' control
                // (showavailability option).
                if(!$course->hiddensections && $thissection->available)
                {
                    if($PAGE->user_is_editing())
                    {
                        echo parent::section_hidden($section);
                    }
                    else
                    {
                        echo $this->section_hidden($section);
                    }
                }

                unset($sections[$section]);
                continue;
            }

            if($thissection->uservisible)
            {
                if($PAGE->user_is_editing())
                {
                    echo parent::section_header($thissection, $course, false);

                    print_section($course, $thissection, $mods, $modnamesused);

                    print_section_add_menus($course, $section, $modnames);

                    echo parent::section_footer();
                }
		else if(empty($thissection->sequence))
		{
		    echo $this->section_empty($section);
		}
                else
                {
                    echo $this->section_header($thissection, $course, false);

                    $this->print_mtabs_section($course, $thissection, $mods, $modnamesused);

                    echo $this->section_footer();

                }
            }

            unset($sections[$section]);
        }

        if($PAGE->user_is_editing() and has_capability('moodle/course:update', $context))
        {
            // Print stealth sections if present.
            $modinfo = get_fast_modinfo($course);
            foreach($sections as $section => $thissection)
            {
                if(empty($modinfo->sections[$section]))
                {
                    continue;
                }
                echo $this->stealth_section_header($section);
                print_section($course, $thissection, $mods, $modnamesused);
                echo $this->stealth_section_footer();
            }

            echo html_writer::end_tag('ul');

            echo html_writer::start_tag('div', array('id' => 'changenumsections', 'class' => 'mdl-right'));

            // Increase number of sections.
            $straddsection = get_string('increasesections', 'moodle');
            $url = new moodle_url('/course/changenumsections.php',
                            array('courseid' => $course->id,
                                'increase' => true,
                                'sesskey' => sesskey()));
            $icon = $this->output->pix_icon('t/switch_plus', $straddsection);
            echo html_writer::link($url, $icon . get_accesshide($straddsection), array('class' => 'increase-sections'));

            if($course->numsections > 0)
            {
                // Reduce number of sections sections.
                $strremovesection = get_string('reducesections', 'moodle');
                $url = new moodle_url('/course/changenumsections.php',
                                array('courseid' => $course->id,
                                    'increase' => false,
                                    'sesskey' => sesskey()));
                $icon = $this->output->pix_icon('t/switch_minus', $strremovesection);
                echo html_writer::link($url, $icon . get_accesshide($strremovesection), array('class' => 'reduce-sections'));
            }

            echo html_writer::end_tag('div');
        }
        else
        {
            echo $this->end_section_list();

            echo html_writer::script('', new moodle_url('/course/format/mtabs/js/jquery-min.js'));
            echo html_writer::script('', new moodle_url('/course/format/mtabs/js/jquery-cookie.js'));

            $jscode = "M.course = M.course || {}; M.course.info = M.course.info || {}; M.course.info.id = {$course->id}; M.course.info.issec0tabbed = " . ($this->mtabs_in_section0 ? "true" : "false") . ";";

            echo html_writer::script($jscode);
            echo html_writer::script('', new moodle_url('/course/format/mtabs/js/mtabs.js'));
        }
    }

    /**
     * Returns the key string to be used a Tab name for Section - 0
     * @return string
     */
    protected function section0_mtab_key_str()
    {
        return 'section0tab';
    }
    /**
     * To Extract All Sections/Topics/Chapters in a Course as a List/Combo box
     *
     * @param $sections Array of all sections in a course
     * @param $numcoursesections Number of topics/sections selected in course settings
     * @return string HTML String of all sections
     */
    protected function course_sections_select_list($sections, $numcoursesections)
    {
        global $PAGE;
        $section_list = html_writer::start_tag('div', array('class' => 'chapters-list-container'));

        $opt_array = array();
	    $section = null;

        $tobeReplaced = Array(chr(0x00), chr(0x0B), chr(0xC2).chr(0xA0), '\t', '\n', '\r');

        for($i = 0; $i <= $numcoursesections; $i++)
        {
            $section = $sections[$i];
	    
            $showsection = $section->uservisible || ($section->visible && !$section->available && $section->showavailability);
		
			if(!$showsection)
            {
                continue;
            }
            
            $secsummary = trim(str_ireplace($tobeReplaced, '', html_to_text($section->summary, 0, false)));
            $secname = trim(str_ireplace($tobeReplaced, '', html_to_text($section->name, 0, false)));

            $opt_text = (!empty($secsummary) ? $secsummary : (!empty($secname) ? $secname : (get_string('mtabssection', 'format_mtabs') . ' - '. $section->section)));

            $opt_array[$section->section] = $opt_text;
        }

        $section_list .= html_writer::select($opt_array, '', '', false, array('class' => 'chapters-list', 'id' => 'chapters-list', 'onchange' => 'showSection(this.value);'));
        $section_list .= html_writer::end_tag('div');

        return $section_list;
    }

    /**
     * @param array $mods
     * @param int $sectionnum
     * @param array $section_tab_mods
     * @return string HTML String of section tabs
     */
    protected function section_tabs_wrap($mods, $sectionnum, $section_tab_mods)
    {
        global $CFG;

        $mtabmod = null;
        $i = 1;
        $o = '';
	$o .= html_writer::start_tag('div', array('id' => 'chapter-' . $sectionnum . '-tabs', 'class' => 'moodle-tabs-wrap'));
        $o .= html_writer::start_tag('ul', array('class' => 'section img-text moodle-tabs'));
	
        foreach($section_tab_mods as $mtab_mod_id => $tab_mods)
        {
            $mtabicon = null;

            //check whether $mtab_mod_id is not section-0 mtab key string
            if($this->section0_mtab_key_str() === $mtab_mod_id)
            {
                $mtabmod = $this->section0tab;
                $mtabicon = $mtabmod->iconinfo;
            }
            else
            {
                $mtabmod = $mods[$mtab_mod_id];
                $mtabicon = $mtabmod->get_custom_data();
            }

            if(!empty($mtabmod) && $mtabmod->modname === 'mtablabel')
            {
                $o .= html_writer::start_tag('li', array('class' => 'activity'));
                $o .= html_writer::start_tag('a', array('class' => ('tab-' . $sectionnum . $i), 'title' => $mtabicon['icondesc']));
                $o .= html_writer::start_tag('span', array('class' => 'mtab-content'));
                $o .= html_writer::start_tag('div', array('class' => 'mtab-span'));
                $o .= html_writer::empty_tag('img', array('class' => 'mtab-img', 'src' => ($CFG->wwwroot . '/course/format/mtabs/pix/tabs/' . $mtabicon['iconfile'])));
                $o .= html_writer::end_tag('div');
                $o .= html_writer::start_tag('div', array('class' => 'mtab-name'));
                $o .= $mtabmod->name;
                $o .= html_writer::end_tag('div');
                $o .= html_writer::end_tag('span');
                $o .= html_writer::end_tag('a');
                $o .= html_writer::end_tag('li');
            }

            $i++;
        }

        $o .= html_writer::end_tag('ul');
        $o .= html_writer::end_tag('div');
	
        return $o;
    }

    /**
     * @param $course
     * @param section_info $section
     * @param array $mods
     * @param $modnamesused
     * @param bool $absolute
     * @param string $width
     * @param bool $hidecompletion
     * @param int $sectionreturn
     * @return void
     */
    protected function print_mtabs_section($course, section_info $section, array $mods, $modnamesused, $absolute = false, $width = "100%", $hidecompletion=false, $sectionreturn=0)
    {
        global $CFG, $OUTPUT, $PAGE;

        static $initialised;
        static $modulenames;
        static $isediting;
        static $ismoving;

        $spacer = '&nbsp;&nbsp;&nbsp;';

        if(!isset($initialised))
        {
            $modulenames = array();
            $isediting = $PAGE->user_is_editing();
            $ismoving = $isediting && ismoving($course->id);
            $initialised = true;
        }

        $labelformatoptions = new stdClass();
        $labelformatoptions->noclean = true;
        $labelformatoptions->trusted = true;

        $modinfo = get_fast_modinfo($course);
        $completioninfo = new completion_info($course);

        //Acccessibility: replace table with list <ul>, but don't output empty list.
        if(!empty($section->sequence))
        {
            $sectionmods = explode(',', $section->sequence);

            //Get all mods/activities part of tabs mtablabel's  all the mods, to be able to
            // output nested lists later
            $section_tabs_mods = $this->preprocess_mods_for_mtabs($sectionmods, $mods, $modinfo);

	    if($section->section !== 0 || $this->mtabs_in_section0)
	    {
                //Prints All M-Tab Label activities/mods as tabs
                echo $this->section_tabs_wrap($mods, $section->section, $section_tabs_mods);
	    }
            //Start of section Tab wrapper
            echo html_writer::start_tag('div', array('class' => 'tab-wrapper'));

            $i = 1;
            foreach($section_tabs_mods as $mtab_mod_id => $tab_mods)
            {
                echo html_writer::start_tag('div', array('class' => 'tab-content', 'id' => ('tab-' . $section->section . $i)));

                if(empty($tab_mods))
                {
                    echo html_writer::end_tag('div');
                    $i++;
                    continue;
                }

                echo html_writer::start_tag('ul');

                foreach($tab_mods as $modnumber)
                {
                    if(empty($mods[$modnumber]))
                    {
                        continue;
                    }

                    /**
                     * @var cm_info
                     */
                    $mod = $mods[$modnumber];
                    $cms = $modinfo->get_cms();

                    if (isset($cms[$modnumber]))
                    {
                        // We can continue (because it will not be displayed at all)
                        // if:
                        // 1) The activity is not visible to users
                        // and
                        // 2a) The 'showavailability' option is not set (if that is set,
                        //     we need to display the activity so we can show
                        //     availability info)
                        // or
                        // 2b) The 'availableinfo' is empty, i.e. the activity was
                        //     hidden in a way that leaves no info, such as using the
                        //     eye icon.
                        if (!$cms[$modnumber]->uservisible && (empty($cms[$modnumber]->showavailability) || empty($cms[$modnumber]->availableinfo)))
                        {
                            // visibility shortcut
                            continue;
                        }
                    }
                    else
                    {
                        if (!file_exists("$CFG->dirroot/mod/$mod->modname/lib.php"))
                        {
                            // module not installed
                            continue;
                        }
                        if (!coursemodule_visible_for_user($mod) && empty($mod->showavailability))
                        {
                            // full visibility check
                            continue;
                        }
                    }

                    if (!isset($modulenames[$mod->modname]))
                    {
                        $modulenames[$mod->modname] = get_string('modulename', $mod->modname);
                    }

                    $modulename = $modulenames[$mod->modname];

                    // In some cases the activity is visible to user, but it is
                    // dimmed. This is done if viewhiddenactivities is true and if:
                    // 1. the activity is not visible, or
                    // 2. the activity has dates set which do not include current, or
                    // 3. the activity has any other conditions set (regardless of whether
                    //    current user meets them)
                    $modcontext = context_module::instance($mod->id);
                    $canviewhidden = has_capability('moodle/course:viewhiddenactivities', $modcontext);
                    $accessiblebutdim = false;
                    if ($canviewhidden)
                    {
                        $accessiblebutdim = !$mod->visible;
                        if (!empty($CFG->enableavailability))
                        {
                            $accessiblebutdim = $accessiblebutdim
                                || $mod->availablefrom > time()
                                || ($mod->availableuntil && $mod->availableuntil < time())
                                || count($mod->conditionsgrade) > 0
                                || count($mod->conditionscompletion) > 0;
                        }
                    }

                    $liclasses = array();
                    $liclasses[] = 'activity';
                    $liclasses[] = $mod->modname;
                    $liclasses[] = 'modtype_'.$mod->modname;
                    $extraclasses = $mod->get_extra_classes();
                    if ($extraclasses)
                    {
                        $liclasses = array_merge($liclasses, explode(' ', $extraclasses));
                    }
                    echo html_writer::start_tag('li', array('class' => join(' ', $liclasses), 'id' => 'module-'.$modnumber));

                    $classes = array('mod-indent');
                    if (!empty($mod->indent))
                    {
                        $classes[] = 'mod-indent-'.$mod->indent;
                        if ($mod->indent > 15)
                        {
                            $classes[] = 'mod-indent-huge';
                        }
                    }

                    echo html_writer::start_tag('div', array('class'=>join(' ', $classes)));

                    // Get data about this course-module
                    list($content, $instancename) = get_print_section_cm_text($cms[$modnumber], $course);

                    //Accessibility: for files get description via icon, this is very ugly hack!
                    $altname = '';
                    $altname = $mod->modfullname;

                    // Avoid unnecessary duplication: if e.g. a forum name already
                    // includes the word forum (or Forum, etc) then it is unhelpful
                    // to include that in the accessible description that is added.
                    if (false !== strpos(textlib::strtolower($instancename), textlib::strtolower($altname)))
                    {
                        $altname = '';
                    }

                    // File type after name, for alphabetic lists (screen reader).
                    if ($altname)
                    {
                        $altname = get_accesshide(' '.$altname);
                    }

                    // We may be displaying this just in order to show information
                    // about visibility, without the actual link
                    $contentpart = '';
                    if ($mod->uservisible)
                    {
                        // Nope - in this case the link is fully working for user
                        $linkclasses = '';
                        $textclasses = '';
                        if ($accessiblebutdim)
                        {
                            $linkclasses .= ' dimmed';
                            $textclasses .= ' dimmed_text';
                            $accesstext = '<span class="accesshide">'.
                                get_string('hiddenfromstudents').': </span>';
                        }
                        else
                        {
                            $accesstext = '';
                        }

                        if ($linkclasses)
                        {
                            $linkcss = 'class="' . trim($linkclasses) . '" ';
                        }
                        else
                        {
                            $linkcss = '';
                        }

                        if ($textclasses)
                        {
                            $textcss = 'class="' . trim($textclasses) . '" ';
                        }
                        else
                        {
                            $textcss = '';
                        }

                        // Get on-click attribute value if specified
                        $onclick = $mod->get_on_click();
                        if ($onclick)
                        {
                            $onclick = ' onclick="' . $onclick . '"';
                        }

                        if ($url = $mod->get_url())
                        {
                            // Display link itself
                            echo '<a ' . $linkcss . $mod->extra . $onclick .
                                ' href="' . $url . '"><img src="' . $mod->get_icon_url() .
                                '" class="activityicon" alt="' .
                                $modulename . '" /> ' .
                                $accesstext . '<span class="instancename">' .
                                $instancename . $altname . '</span></a>';

                            // If specified, display extra content after link
                            if ($content)
                            {
                                $contentpart = '<div class="' . trim('contentafterlink' . $textclasses) . '">' . $content . '</div>';
                            }
                        }
                        else
                        {
                            // No link, so display only content
                            $contentpart = '<div ' . $textcss . $mod->extra . '>' .
                                $accesstext . $content . '</div>';
                        }

                        if (!empty($mod->groupingid) && has_capability('moodle/course:managegroups', context_course::instance($course->instance)))
                        {
                            $groupings = groups_get_all_groupings($course->id);
                            echo " <span class=\"groupinglabel\">(".format_string($groupings[$mod->groupingid]->name).')</span>';
                        }
                    }
                    else
                    {
                        $textclasses = $extraclasses;
                        $textclasses .= ' dimmed_text';
                        if ($textclasses)
                        {
                            $textcss = 'class="' . trim($textclasses) . '" ';
                        }
                        else
                        {
                            $textcss = '';
                        }

                        $accesstext = '<span class="accesshide">'
                            . get_string('notavailableyet', 'condition')
                            . ': </span>';

                        if ($url = $mod->get_url())
                        {
                            // Display greyed-out text of link
                            echo '<div ' . $textcss . $mod->extra . ' >' . '<img src="' . $mod->get_icon_url() . '" class="activityicon" alt="' . $modulename . '" /> <span>'. $instancename . $altname . '</span></div>';
                            // Do not display content after link when it is greyed out like this.
                        }
                        else
                        {
                            // No link, so display only content (also greyed)
                            $contentpart = '<div ' . $textcss . $mod->extra . '>' . $accesstext . $content . '</div>';
                        }
                    }

                    // Module can put text after the link (e.g. forum unread)
                    echo $mod->get_after_link();

                    // If there is content but NO link (eg label), then display the
                    // content here (BEFORE any icons). In this case cons must be
                    // displayed after the content so that it makes more sense visually
                    // and for accessibility reasons, e.g. if you have a one-line label
                    // it should work similarly (at least in terms of ordering) to an
                    // activity.
                    if (empty($url))
                    {
                        echo $contentpart;
                    }

                    // Completion
                    $completion = $hidecompletion ? COMPLETION_TRACKING_NONE : $completioninfo->is_enabled($mod);

                    if ($completion != COMPLETION_TRACKING_NONE && isloggedin() && !isguestuser() && $mod->uservisible)
                    {
                        $completiondata = $completioninfo->get_data($mod,true);
                        $completionicon = '';

                        if ($completion == COMPLETION_TRACKING_MANUAL)
                        {
                            switch($completiondata->completionstate)
                            {
                                case COMPLETION_INCOMPLETE:
                                    $completionicon = 'manual-n'; break;
                                case COMPLETION_COMPLETE:
                                    $completionicon = 'manual-y'; break;
                            }
                        }
                        else
                        {
                            // Automatic
                            switch($completiondata->completionstate)
                            {
                                case COMPLETION_INCOMPLETE:
                                    $completionicon = 'auto-n'; break;
                                case COMPLETION_COMPLETE:
                                    $completionicon = 'auto-y'; break;
                                case COMPLETION_COMPLETE_PASS:
                                    $completionicon = 'auto-pass'; break;
                                case COMPLETION_COMPLETE_FAIL:
                                    $completionicon = 'auto-fail'; break;
                            }
                        }

                        if ($completionicon)
                        {
                            $imgsrc = $OUTPUT->pix_url('i/completion-'.$completionicon);
                            $formattedname = format_string($mod->name, true, array('context' => $modcontext));
                            $imgalt = get_string('completion-alt-' . $completionicon, 'completion', $formattedname);

                            if ($completion == COMPLETION_TRACKING_MANUAL && !$isediting)
                            {
                                $imgtitle = get_string('completion-title-' . $completionicon, 'completion', $formattedname);
                                $newstate = $completiondata->completionstate==COMPLETION_COMPLETE ? COMPLETION_INCOMPLETE : COMPLETION_COMPLETE;

                                // In manual mode the icon is a toggle form...

                                // If this completion state is used by the
                                // conditional activities system, we need to turn
                                // off the JS.
                                if (!empty($CFG->enableavailability) && condition_info::completion_value_used_as_condition($course, $mod))
                                {
                                    $extraclass = ' preventjs';
                                }
                                else
                                {
                                    $extraclass = '';
                                }

                                echo '<form class="togglecompletion' . $extraclass . ' method="post" action="' . $CFG->wwwroot . '/course/togglecompletion.php"><div>'
                                    . '<input type="hidden" name="id" value="' . $mod->id . '" />'
                                    . '<input type="hidden" name="modulename" value="' . s($mod->name) . '" />'
                                    . '<input type="hidden" name="sesskey" value="' . sesskey() . '" />'
                                    . '<input type="hidden" name="completionstate" value="' . $newstate . '" />'
                                    . '<input type="image" src="' . $imgsrc . '" alt="' . $imgalt . '" title="' . $imgtitle . '" />'
                                    . '</div></form>';
                            }
                            else
                            {
                                // In auto mode, or when editing, the icon is just an image
                                echo '<span class="autocompletion">';
                                echo '<img src="' . $imgsrc . '" alt="' . $imgalt . '" title="' . $imgalt . '" /></span>';
                            }
                        }
                    }

                    // If there is content AND a link, then display the content here
                    // (AFTER any icons). Otherwise it was displayed before
                    if (!empty($url))
                    {
                        echo $contentpart;
                    }

                    // Show availability information (for someone who isn't allowed to
                    // see the activity itself, or for staff)
                    if (!$mod->uservisible)
                    {
                        echo '<div class="availabilityinfo">' . $mod->availableinfo . '</div>';
                    }
                    elseif ($canviewhidden && !empty($CFG->enableavailability) && $mod->visible)
                    {
                        $ci = new condition_info($mod);
                        $fullinfo = $ci->get_full_information();

                        if($fullinfo)
                        {
                            echo '<div class="availabilityinfo">' . get_string(($mod->showavailability ? 'userrestriction_visible' : 'userrestriction_hidden'), 'condition', $fullinfo) . '</div>';
                        }
                    }

                    echo html_writer::end_tag('div'); // End of Div
                    echo html_writer::end_tag('li'); // End of mod printing
                }

                echo html_writer::end_tag('ul'); // End of Tab Mods ul printing
                echo html_writer::end_tag('div'); // End of div.tab-content printing

                $i++;
            }

            echo html_writer::end_tag('div'); // End of div.tab-wrapper printing
        }
    }

    /**
     * This function will preprocess all the mods in section, adding the required stuff to be able to
     * output them later in tabbed manner
     * 
     * @param array $sectionmods
     * @param array $mods
     * @param course_modinfo $modinfo
     * @return array
     */
    protected function preprocess_mods_for_mtabs($sectionmods, $mods, $modinfo)
    {
        global $CFG, $PAGE;

        $section_tab_mods = array();
        $non_tab_mods = array();
        $tab_mods = null;

        $mtabmodid = null;

        foreach($sectionmods as $modnumber)
        {
            if(empty($mods[$modnumber]))
            {
                continue;
            }

            $mod = $mods[$modnumber];

            if($mod->modname === 'mtablabel' and $mod->indent == 0)
            {
                $mtabmodid = $mod->id;
                $section_tab_mods[$mtabmodid] = array();
            }
            else
            {
                if(!is_null($mtabmodid))
                {
                    $tab_mods = $section_tab_mods[$mtabmodid];
                    $tab_mods[] = $mod->id;
                    $section_tab_mods[$mtabmodid] = $tab_mods;
                    $tab_mods = null;
                }
                else
                {
                    $non_tab_mods[] = $mod->id;
                }
            }
        }

        //For activities/mods of section0 only
        if(!empty($non_tab_mods))
        {
            $section_tab_mods[$this->section0_mtab_key_str()] = $non_tab_mods;
        }

        return $section_tab_mods;
    }
}
