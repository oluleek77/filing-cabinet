<?php
// Don't edit this file directly.
// Make a copy of install-example-local.sh or install-example-remote.sh and put your values in that
$db_user = '<insert database username>';
$db_password = '<insert database password>';
$database = 'filingcabinet';
$archive_dir = '<insert archive path>';
$archive_path = '<insert archive path and filename>';
$rel_web_path = '<insert path of app relative to web directory>';
$admin_email = '<insert admin email address>';

$labels_per_page = 50;
$files_per_page = 50;
$show_common_limit = 15; // lower limit for number of displayed labels for presenting most common
$show_common_amount = 5; // when presenting most common labels, show this many
$label_large_amount = 20; // when at least this many files have a particular label, show that label in large font
$label_x_large_amount = 50; // when at least this many files have a particular label, show that label in x-large font
?>