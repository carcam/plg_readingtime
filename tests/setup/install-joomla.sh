#!/bin/bash
set -e

# Variables
JOOMLA_VERSION="5-3-3"
JOOMLA_DIR="/var/www/html/joomla"
JEDCHECKER_URL=`curl -s https://api.github.com/repos/joomla-extensions/jedchecker/releases/latest | jq -r '.zipball_url'`

# Descargar Joomla si no existe
if [ ! -d "$JOOMLA_DIR" ]; then
  mkdir -p $JOOMLA_DIR
  curl -L -o /tmp/joomla.zip https://downloads.joomla.org/cms/joomla5/$JOOMLA_VERSION/Joomla_${JOOMLA_VERSION}-Stable-Full_Package.zip
  unzip /tmp/joomla.zip -d $JOOMLA_DIR
  rm /tmp/joomla.zip
  php $JOOMLA_DIR/installation/joomla.php install --site-name="DDEV Testing" --admin-user="Administrator" --admin-username=admin --admin-password=AdminAdmin1! --admin-email=admin@example.com --db-type=mysql --db-encryption=0 --db-host=db --db-user=db --db-pass="db" --db-name=db --db-prefix=ddev_ --public-folder="public"
fi

# Instalar JEDChecker
php $JOOMLA_DIR/cli/joomla.php extension:install --url=$JEDCHECKER_URL
cp -r tests/setup/tmp/jedchecker $JOOMLA_DIR/tmp/
