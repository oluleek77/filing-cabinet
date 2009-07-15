#!/bin/sh

######################################################################
# To make your own personal install script edit a copy of this file. #
# Don't edit this file itslef, or your script will become everyone's.#
######################################################################

# put your database username and password here
db_user=user
db_pass=pass

# where the filing cabinet is hosted from
web_path=$HOME/web/filingcabinet

# where the files are actually stored (needs to be a 7zip file)
archive_path_enc='\/media\/sdb\/filearchive\/allfiles.7z'

# this bit copies the web files in
cp index.html environment.php upload_files.php uploader.php listview.php $web_path

# this bit edits the web files so they work with your setup
sed -i "s/<insert database username>/$db_user/" $web_path/environment.php
sed -i "s/<insert database password>/$db_pass/" $web_path/environment.php
sed -i "s/<insert archive path and filename>/$archive_path_enc/" $web_path/environment.php

# You might want to add a bit that imports the sql into your database
