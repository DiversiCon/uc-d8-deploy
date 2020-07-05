#!/bin/bash

./vendor/mglaman/drupal-check/drupal-check -d ./docroot/modules/custom/
./vendor/acquia/blt/bin/blt tests:phpcs:sniff:all

