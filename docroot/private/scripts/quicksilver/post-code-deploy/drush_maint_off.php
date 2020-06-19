<?php

echo "Taking site out of maintenance mode...\n";
passthru('drush sset system.maintenance_mode 0');
