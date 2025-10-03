#!/bin/bash
rsync -avP *.php *.js *.txt web@143.198.11.24:/srv/www/uruyoga.com/current/web/app/plugins/mindbody-authenticate/

#LICENSE.txt  deploy.sh        dist       languages        phpcs.xml.dist  testDeploy.sh  vendor
#README.txt   deployAssets.sh  index.php  mz-mindbody.php  src             uninstall.php
