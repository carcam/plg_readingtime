#!/bin/bash
set -e

# Variables
JOOMLA_MAJOR_VERSION="${1:-6}"
CMS_DIR="tests/cms"
JOOMLA_DIR="$CMS_DIR/joomla$JOOMLA_MAJOR_VERSION"

# Check if Joomla installation exists
if [ ! -d "$JOOMLA_DIR" ]; then
    echo "Error: Joomla $JOOMLA_MAJOR_VERSION is not installed at $JOOMLA_DIR"
    echo "Please run install-joomla.sh $JOOMLA_MAJOR_VERSION first"
    exit 1
fi

echo "Installing plugin to Joomla $JOOMLA_MAJOR_VERSION..."

# Create temporary zip for installation
# This bypasses issues with discovering symlinked extensions during install
ZIP_PATH="$(pwd)/tests/setup/tmp/plg_readingtime.zip"
mkdir -p "$(dirname "$ZIP_PATH")"
cd src/plugins/content/plg_content_readingtime
zip -r "$ZIP_PATH" .
cd -

# Install via zip
# We use ddev exec to access the zip file which is mounted in the container
ZIP_PATH_CONTAINER="/var/www/html/tests/setup/tmp/plg_readingtime.zip"
ddev exec php /var/www/html/$JOOMLA_DIR/cli/joomla.php extension:install --path="$ZIP_PATH_CONTAINER"

# Replace installed files with symlink for development
PLUGIN_SRC_CONTAINER="/var/www/html/src/plugins/content/plg_content_readingtime"
PLUGIN_DEST_CONTAINER="/var/www/html/$JOOMLA_DIR/plugins/content/readingtime"

ddev exec rm -rf "$PLUGIN_DEST_CONTAINER"
ddev exec ln -s "$PLUGIN_SRC_CONTAINER" "$PLUGIN_DEST_CONTAINER"

# Enable the plugin
# extension:enable does not exist in the list of commands.
# We use database query to enable it.
ddev mysql -e "UPDATE jos${JOOMLA_MAJOR_VERSION}_extensions SET enabled=1 WHERE element='readingtime' AND folder='content'"

# Install JED Checker
echo "Installing JED Checker..."
JEDCHECKER_URL=$(curl -s https://api.github.com/repos/joomla-extensions/jedchecker/releases/latest | jq -r '.zipball_url')
ddev exec php /var/www/html/$JOOMLA_DIR/cli/joomla.php extension:install --url="$JEDCHECKER_URL"

echo "Plugin installation complete!"
