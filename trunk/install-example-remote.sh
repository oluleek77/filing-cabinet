#!/bin/sh

######################################################################
# To make your own personal install script edit a copy of this file. #
# Don't edit this file itself, or your script will become everyone's.#
######################################################################
# This script assumes the target directory structure already exists
# i.e. $full_path exists and has a subdirectory called 'images'
# images has a subdirectory called 'mimetypes'
# mimetypes has two subdirectories: '16' and '32'

# put your database username and password here
db_user=user
db_pass=pass

# directory for web hosting
web_path=user@my.domain:/my_web_home

# path within web directory for filing cabinet app (relative to web path)
fc_path=filingcabinet

full_path=$web_path/$fc_path

# where the files are actually stored from the point of view of the server (needs to be a 7zip file)
archive_path='\/media\/sdb\/filearchive'
archive_path_enc="$archive_path\/allfiles.7z"

# email address of the administrator
admin_email='admin@my.domain.com'

# make a copy of the web files that need to be edited
cp environment.php environment.php.tmp

# this bit edits the web files so they work with your setup
sed -i "s/<insert database username>/$db_user/" environment.php.tmp
sed -i "s/<insert database password>/$db_pass/" environment.php.tmp
sed -i "s/<insert archive path>/$archive_path/" environment.php.tmp
sed -i "s/<insert archive path and filename>/$archive_path_enc/" environment.php.tmp
sed -i "s/<insert path of app relative to web directory>/$fc_path/" environment.php.tmp
sed -i "s/<insert admin email address>/$admin_email/" environment.php.tmp

# this bit copies the web files in
scp index.html common.php upload_files.php uploader.php listview.php fileview.php access.class.php login.php register.php download.php labelserver.php filingcabinet-default.css jquery-1.4.1.min.js $full_path/
scp images/* $full_path/images/
scp images/mimetypes/16/* $full_path/images/mimetypes/16/
scp images/mimetypes/32/* $full_path/images/mimetypes/32/
scp environment.php.tmp $full_path/environment.php

# You might want to add a bit that imports the sql into your database
