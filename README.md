Moodle-Force-Completion
=======================

Allows use of Moodle completion report to force completion status of individual users and activities.

This is a modified version of the Moodle 2.4.1 ./report/completion directory. It contains all necessary files to implement forced completion of individual user/activity combinations from directly within the completion report.

Installation requires copying this directory in place of the existing ./report/completion directory. If your version is newer, you may have to modify the version and update files to force the update process to occur.

DB Changes:
    - An additional field, `forced`, added to the 'course_modules_completion' table and used to track completion.

File System Changes:
    - Additional files/folders:
        - db/upgrade.php (provides upgrade db changes)
        - pix (folder including force completion icon)
        - forcecompletion.js (establishes js required to make AJAX toggle completion calls)
        - toggleforcecompletion.php (handles calls from reports page when toggling forced completion)
