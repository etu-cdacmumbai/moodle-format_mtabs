This course format is one developed from the scratch to give proper structure and good look and feel to the course. Its
main (unique) difference is that, following the "M-Tab label(s)" created in edit mode, it displays the course in a tab
way.

PS: M-Tab Label - A Label (module) associated with icon, which acts as tab(s)/sub-section(s)/category(s) of section(s)
in a course.

Configuration hacks:

- By default the tree display is disabled for section 0. If you want to
  see the tabs there, edit the config.php file and change this line from:

  $format['mtabs_in_section0'] = false;

  to:

  $format['mtabs_in_section0'] = true;

Finally, note that the tabbed display is only available in non-edit mode.
Edit mode (teachers) will display the course in standard topics format, 
in order to play with indentation and so on.

That's all, enjoy. 

--
C-DAC Mumbai 20130410
