<?php

echo "Putting site into maintenance mode...\n";
passthru('drush sset system.maintenance_mode 1');
