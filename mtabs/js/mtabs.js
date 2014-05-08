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
 * This file contains general Javascript functions for the course format M-Tabs
 *
 * @package    format_mtabs
 * @copyright  2013 onwards C-DAC Mumbai {@link http://www.cdacmumbai.in}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Initializes all M-Tab sections
 * @returns boolean
 */
function initSections()
{
    "use strict";
    //This is a small hack to enable editing currently viewing section...
    if(!$('div.singlebutton form div input[name="section"]').length)
    {
        $('<input type="hidden" value="1" name="section"/>').appendTo('div.singlebutton form div');
    }

    var mtabSections = $('div.moodle-tab-module > div.course-section');
    if(!mtabSections.length)
    {
        return false;
    }

    var sectionNum = 0;
    var section = null;
    var status = true;
    for(var key = 0; key < mtabSections.length; key++)
    {
        section = mtabSections[key];
        sectionNum = section.getAttribute('sectionnum');
        status = status && initTabs(sectionNum);
    }

    //Retrieving or Setting up the cookie for a course's active section
    var casCookiePtrn = 'c' + M.course.info.id + 'as';
    sectionNum = $.cookie(casCookiePtrn);
    if(sectionNum === null)
    {
        sectionNum = 1;
    }
    $('#chapters-list').val(sectionNum);
    mtabSections.hide();

    return status && showSection(sectionNum);
}

function showSection(sectionNum)
{
    "use strict";
    if(typeof sectionNum === 'undefined')
    {
        return false;
    }

    sectionNum = parseInt(sectionNum, 10);
    if(sectionNum < 0)
    {
        return false;
    }

    $('div.moodle-tab-module > div.course-section:visible').hide();
    $('div.singlebutton form div input[name="section"]').val(sectionNum);
    $('div#chapter-' + sectionNum).fadeIn('slow');

    //Retrieving or Setting up the cookie for a course's active section
    var casCookiePtrn = 'c' + M.course.info.id + 'as';
    $.cookie(casCookiePtrn, sectionNum, {"expires" : 7});
    var csTabs = $('div#chapter-' + sectionNum + '-tabs ul.moodle-tabs li a');
    //Section already has a selected tab
    if(csTabs.hasClass('selected'))
    {
        return true;
    }

    //If no tab selected at all then try retrieving or setting up the cookie for section's tab
    var cstCookiePtrn = 'c' + M.course.info.id + 's' + sectionNum;
    var activeTabClass = $.cookie(cstCookiePtrn);
    //To not to set cookie for a section not having tabs
    if(csTabs.length && activeTabClass === null)
    {
        activeTabClass = 'tab-' + sectionNum + '' + 1;
        $.cookie(cstCookiePtrn, activeTabClass, {"expires" : 7});
    }
    csTabs.filter('a.' + activeTabClass).trigger('click');

    return true;
}

function initTabs(sectionNum)
{
    "use strict";
    if(typeof sectionNum === 'undefined')
    {
        return false;
    }

    sectionNum = parseInt(sectionNum, 10);
    if(sectionNum < 0)
    {
        return false;
    }
    $('div#chapter-' + sectionNum + '-tabs ul.moodle-tabs li a').click(showTab);

    return true;
}

function showTab(event)
{
    "use strict";
    var sectionNum = $(this).parents('div.course-section').attr('sectionnum');
    var selectedTab = $('div#chapter-' + sectionNum + '-tabs ul.moodle-tabs li a.selected');
    if(selectedTab.is(this))
    {
        //alert('Tabs is already selected!');
        event.preventDefault();
        return false;
    }
    selectedTab.removeClass('selected');

    var tabClass = $(this).attr('class');
    var tabContentContainer = $('div#' + tabClass);
    tabContentContainer.siblings().hide();
    tabContentContainer.show();

    //Setting up the cookie for section's tab
    var cstCookiePtrn = 'c' + M.course.info.id + 's' + sectionNum;
    $.cookie(cstCookiePtrn, tabClass, {"expires" : 7});

    $(this).addClass('selected');
    $(this).stop();

    event.preventDefault();
    return false;
}

$(document).ready(function()
{
    "use strict";
    var initStatusMsg = 'M-Tab Sections loaded successfully!... Enjoy!... :-D';
    if(!initSections())
    {
        initStatusMsg = 'M-Tab Sections loading failed!... :-(';
    }
    if(console && console.log)
    {
        console.log(initStatusMsg);
    }
});
