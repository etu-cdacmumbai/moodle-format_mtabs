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
 * Post-install code for the mtablabel module.
 *
 * @package     mod_mtablabel
 * @copyright  2013 onwards C-DAC Mumbai {@link http://www.cdacmumbai.in}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Code run after the quiz module database tables have been created.
 */
function xmldb_mtablabel_install() {
    global $DB;

    $record = new stdClass();
    $record->name = 'Study Material';
    $record->filename = 'stdmaterial.png';
    $record->description = 'Study Material';
    $DB->insert_record('mtablabelicon', $record, false);

    $record = new stdClass();
    $record->name = 'Multimedia Content';
    $record->filename = 'mmcontent.png';
    $record->description = 'Multimedia Content like Video, Audio, Flash animations, etc...';
    $DB->insert_record('mtablabelicon', $record, false);
    
    $record = new stdClass();
    $record->name = 'Assessment';
    $record->filename = 'assessment.png';
    $record->description = 'Formative, Submittive Assessments, etc...';
    $DB->insert_record('mtablabelicon', $record, false);
    
    $record = new stdClass();
    $record->name = 'Activities';
    $record->filename = 'activities.png';
    $record->description = 'Different varieties of activities';
    $DB->insert_record('mtablabelicon', $record, false);
    
    $record = new stdClass();
    $record->name = 'Question Bank';
    $record->filename = 'qbank.png';
    $record->description = 'Question Bank';
    $DB->insert_record('mtablabelicon', $record, false);
    
    $record = new stdClass();
    $record->name = 'Glossary';
    $record->filename = 'glossary.png';
    $record->description = 'Glossary of Chapter or Section or Topic';
    $DB->insert_record('mtablabelicon', $record, false);
}
