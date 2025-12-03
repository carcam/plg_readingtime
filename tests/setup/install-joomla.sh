#!/bin/bash
set -e

# Variables
JOOMLA_MAJOR_VERSION="6"
JOOMLA_VERSION="$JOOMLA_MAJOR_VERSION-0-1"
JOOMLA_DIR="/var/www/html/joomla$JOOMLA_MAJOR_VERSION"
JEDCHECKER_URL=`curl -s https://api.github.com/repos/joomla-extensions/jedchecker/releases/latest | jq -r '.zipball_url'`

# Descargar Joomla si no existe
if [ ! -d "$JOOMLA_DIR" ]; then
  mkdir -p $JOOMLA_DIR
  curl -L -o /tmp/joomla.zip https://downloads.joomla.org/cms/joomla$JOOMLA_MAJOR_VERSION/$JOOMLA_VERSION/Joomla_${JOOMLA_VERSION}-Stable-Full_Package.zip
  unzip /tmp/joomla.zip -d $JOOMLA_DIR
  rm /tmp/joomla.zip
  php $JOOMLA_DIR/installation/joomla.php install --site-name="DDEV Testing" --admin-user="Administrator" --admin-username=admin --admin-password=AdminAdmin1! --admin-email=admin@example.com --db-type=mysql --db-encryption=0 --db-host=db --db-user=db --db-pass="db" --db-name=db --db-prefix=ddev_ 
fi

# Install plg_readingtime
php $JOOMLA_DIR/bin/joomla.php extension:discover
php $JOOMLA_DIR/bin/joomla.php extension:install --name=plg_content_readingtime
ddev mysql -e "UPDATE ddev_extensions SET params = '{\"notification_email\":{\"notification_email0\":{\"email\":\"admin@example.com\"}}}' WHERE element = 'com_heptawhistleblower'"

# Instalar JEDChecker
php $JOOMLA_DIR/cli/joomla.php extension:install --url=$JEDCHECKER_URL
cp -r tests/setup/tmp/jed_checker $JOOMLA_DIR/tmp/
