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
 * Add mtablabel form
 *
 * @package    mod
 * @subpackage mtablabel
 * @copyright  2006 Jamie Pratt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_mtablabel_mod_form extends moodleform_mod {

    function definition() {
        global $DB, $CFG, $PAGE;

        $mform =& $this->_form;

        $this->add_intro_editor(true, get_string('mtablabeltext', 'mtablabel'));

        $height = '32px';
        $width = '32px';

        $sysiconlist = $DB->get_records("mtablabelicon");

        $iconarr = array();

        foreach($sysiconlist as $ico)
        {
            $iconarr[] =& $mform->createElement('radio', 'iconid', '', '<img src="' . $CFG->wwwroot . '/course/format/mtabs/pix/tabs/' . $ico->filename . '" style="vertical-align: middle; width:32px; height:32px;" title="' . $ico->description . '">', $ico->id);
        }

        $mform->addGroup($iconarr, "iconid", get_string("selectmtablabelicon", "mtablabel"), " ", false);
        $mform->addRule('iconid', get_string('required'), 'required', null, 'client');
        $mform->setDefault('iconid', 1);

        $this->standard_coursemodule_elements();

//-------------------------------------------------------------------------------
// buttons
        $this->add_action_buttons(true, false, null);

    }
}
