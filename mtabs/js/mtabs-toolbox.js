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
 * This file overrides YUI module moodle-course-toolboxes's is_label function(s) for the course format M-Tabs
 *
 * @package    format_mtabs
 * @copyright  2013 onwards C-DAC Mumbai {@link http://www.cdacmumbai.in}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

YUI(M.util.loader).use('node', function (Y) {
    Y.on('domready', function () {
        var MOODLE_COURSE_TOOLBOX = null;

        for (var key in M.course.coursebase.registermodules)
        {
            if (M.course.coursebase.registermodules[key].name === 'course-resource-toolbox')
            {
                MOODLE_COURSE_TOOLBOX = M.course.coursebase.registermodules[key];
                break;
            }
        }

        //Overridden RESOURCETOOLBOX.is_label() method to include support for M-Tab Label
        MOODLE_COURSE_TOOLBOX.constructor.prototype.is_label = function (target) {
            if (target.hasClass('label') || target.hasClass('mtablabel')) {
                return true;
            }

            return false;
        };
    });
});

