<?php

echo "Performing database updates...\n";
passthru('drush updb --yes --strict=0');
