#!/bin/sh

######################################################################
# To make your own personal install script edit a copy of this file. #
# Don't edit this file itslef, or your script will become everyone's.#
######################################################################

# put your database username and password here
db_user=user
db_pass=pass

# where the filing cabinet is hosted from
web_path=user@my.domain:/my_home/web/filingcabinet

# where the files are actually stored (needs to be a 7zip file)
archive_path_enc='\/media\/sdb\/filearchive\/allfiles.7z'

# make a copy of the web files that need to be edited
cp environment.php environment.php.tmp

# this bit edits the web files so they work with your setup
sed -i "s/<insert database username>/$db_user/" environment.php.tmp
sed -i "s/<insert database password>/$db_pass/" environment.php.tmp
sed -i "s/<insert archive path and filename>/$archive_path_enc/" environment.php.tmp

# this bit copies the web files in
scp index.html upload_files.php uploader.php listview.php $web_path/
scp environment.php.tmp $web_path/environment.php

# You might want to add a bit that imports the sql into your database
