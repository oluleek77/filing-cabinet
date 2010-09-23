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

# user @ host server
server=user@my.domain

# directory for web hosting on server
web_path=/my_web_home

# path within web directory for filing cabinet app (relative to web path)
fc_path=filingcabinet

full_path=$server:$web_path/$fc_path


# where the files are actually stored from the point of view of the server (needs to be a 7zip file)
archive_path='\/media\/sdb\/filearc$web_path/$fc_pathhive'
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

# this bit sets up the directorty structure
ssh $server mkdir $web_path/$fc_path/uploads $web_path/$fc_path/js $web_path/$fc_path/css $web_path/$fc_path/images

# this bit copies the web files in
rsync -rl --exclude='*/.svn' index.html common.php upload_files.php uploader.php listview.php fileview.php access.class.php login.php register.php download.php labelserver.php images js css $full_path/
scp environment.php.tmp $full_path/environment.php

# You might want to add a bit that imports the sql into your database
