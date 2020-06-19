<?php

/**
 * Post code deploy actions.
 */

echo "Putting site into maintenance mode...";
passthru('drush sset system.maintenance_mode 1');
echo "...\n";

echo "Performing database updates...";
passthru('drush updb --yes --strict=0');
echo "...\n";

echo "Importing configuration from yml files...";
passthru('drush cim --yes');
echo "...\n";

echo "Rebuilding cache.";
passthru('drush cr');
echo "...\n";

echo "Taking site out of maintenance mode...";
passthru('drush sset system.maintenance_mode 0');
echo "...\n";
