#!/bin/bash


# To automate this, set up a cron job, similar to

# 0 0,21,22 * * * /PATH_TO_WEB_DIRECTORY/private/coffee_cron.sh > /PATH_TO_WEB_DIRECTORY/private/log/cron.log

# which will run at midnight, 9 pm, and 10 pm daily.

my_dir=$(dirname -- "$0")
cd "$my_dir"
php cronjob.php > log/import.log
logrotate logrotate.conf