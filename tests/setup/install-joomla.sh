#!/bin/bash
set -e

# Variables
JOOMLA_MAJOR_VERSION="${1:-6}"
if [ "$JOOMLA_MAJOR_VERSION" = "5" ]; then
    JOOMLA_VERSION="5-3-3"
else
    JOOMLA_VERSION="6-0-1"
fi


CMS_DIR="tests/cms"
JOOMLA_DIR="$CMS_DIR/joomla$JOOMLA_MAJOR_VERSION"
CURRENT_LINK="$CMS_DIR/current"

rm -rf $JOOMLA_DIR
mkdir -p $JOOMLA_DIR
# Determine download URL based on version
JOOMLA_VERSION_DOTS=$(echo "$JOOMLA_VERSION" | sed 's/-/./g')
DOWNLOAD_URL="https://downloads.joomla.org/cms/joomla${JOOMLA_MAJOR_VERSION}/${JOOMLA_VERSION}/Joomla_${JOOMLA_VERSION_DOTS}-Stable-Full_Package.zip"

echo "Downloading Joomla from $DOWNLOAD_URL..."
curl -L -o /tmp/joomla.zip "$DOWNLOAD_URL"

if unzip -t /tmp/joomla.zip > /dev/null 2>&1; then
    unzip -q /tmp/joomla.zip -d $JOOMLA_DIR
    rm /tmp/joomla.zip
      
      # Run installation inside DDEV to ensure PHP environment is correct
      # We assume ddev is running. If not, this might fail or run locally if php is available.
      # Ideally we should run this via 'ddev exec' if we are outside, but this script might be run from inside or outside.
      # Let's try to detect or just run 'php' and assume the user runs this via 'ddev ssh' or we wrap it.
      # But wait, the previous script used 'php'.
      # If we are running this from the host (which we are), 'php' command uses host PHP.
      # The user said "I use ddev to setup the web environment".
      # The script was originally: php $JOOMLA_DIR/installation/joomla.php ...
      # If I run this on host, I need PHP on host.
      # Better to run the install command via ddev exec if possible, or assume user has PHP.
      # Given the previous script was just 'php', I'll stick to 'php' but maybe the user runs this inside ddev ssh?
      # The user's prompt said "I use ddev...".
      # Let's use 'ddev exec php' if ddev is running, or just 'php' if not?
      # Or just 'php' and let the user handle it.
      # But wait, I am running this script via `run_command` on the host.
      # If I use `ddev exec`, I need to make sure paths are correct (mapped).
      # `tests/cms` on host is `/var/www/html/tests/cms` in container.
      
    echo "Installing Joomla..."
    # We use ddev exec to run the installation to ensure dependencies/extensions are present
    ddev exec php /var/www/html/$JOOMLA_DIR/installation/joomla.php install --site-name="DDEV Testing $JOOMLA_MAJOR_VERSION" --admin-user="Administrator" --admin-username=admin --admin-password=AdminAdmin1! --admin-email=admin@example.com --db-type=mysql --db-encryption=0 --db-host=db --db-user=db --db-pass="db" --db-name=db --db-prefix=jos${JOOMLA_MAJOR_VERSION}_ --public-folder=/var/www/html/$JOOMLA_DIR/public
  
      # Remove installation folder if it still exists (Joomla 6 might not remove it automatically)
    if [ -d "$JOOMLA_DIR/installation" ]; then
        rm -rf "$JOOMLA_DIR/installation"
    fi
else
    echo "Error: Downloaded file is not a valid zip archive."
    rm /tmp/joomla.zip
    exit 1
fi

# Update symlink
rm -f $CURRENT_LINK
ln -s "joomla$JOOMLA_MAJOR_VERSION" $CURRENT_LINK

echo "Joomla $JOOMLA_MAJOR_VERSION installation complete!"
echo "To install the plugin, run: tests/setup/install-plugin.sh $JOOMLA_MAJOR_VERSION"
