<?php
// @codingStandardsIgnoreFile

/**
 * Environment specific settings.
 */

/*
 * Pantheon environments.
 */
if (getenv('PANTHEON_ENVIRONMENT')) {
  $pantheon_env = getenv('PANTHEON_ENVIRONMENT');

  // Services.
  $settings['container_yamls'][] = DRUPAL_ROOT . '/sites/services.yml';

  // Config read-only settings.
  if ($pantheon_env == 'test' || $pantheon_env == 'live') {
    if (PHP_SAPI !== 'cli') {
      $settings['config_readonly'] = TRUE;
    }
  }

  // Config split settings.
  // Since uat/preprod is not supported in the blt.settings.inc file, let's go ahead and
  // manage all of our environment splits here, rather than having to manage two locations.
  //
  // Site specific config splits are still enabled/disabled in the blt.settings.inc.
  $config['config_split.config_split.local']['status'] = FALSE;
  $config['config_split.config_split.non_local']['status'] = FALSE;
  $config['config_split.config_split.dev']['status'] = FALSE;
  $config['config_split.config_split.stage']['status'] = FALSE;
  $config['config_split.config_split.prod']['status'] = FALSE;

  // Environment specific configuration changes.
  switch ($pantheon_env) {
    case 'dev':
      $config['config_split.config_split.non_local']['status'] = TRUE;
      $config['config_split.config_split.dev']['status'] = TRUE;
      $config['system.logging']['error_level'] = 'verbose';

      $settings['container_yamls'][] = DRUPAL_ROOT . '/sites/debug.environment.services.yml';
      break;

    case 'test':
      $config['config_split.config_split.non_local']['status'] = TRUE;
      $config['config_split.config_split.stage']['status'] = TRUE;

      $settings['container_yamls'][] = DRUPAL_ROOT . '/sites/debug.environment.services.yml';
      break;

    case 'live':
      $config['config_split.config_split.non_local']['status'] = TRUE;
      $config['config_split.config_split.prod']['status'] = TRUE;

      if ( isset($_SERVER['REQUEST_URI']) ) {
        // increase memory on node/*/edit and config_pages
        if ( (preg_match('^\/node\/[\d]+\/edit^', $_SERVER['REQUEST_URI']) === 1)  || (preg_match('^\/admin\/structure\/config_pages\/.+^',$_SERVER['REQUEST_URI']) === 1) ) {
          ini_set('memory_limit', '384M');
        }
      }
      ini_set ('display_errors', '0');

      break;
  }

  // The custom API endpoint for CDR requires additional memory. 256M prevents an OOM error.
  if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'faculty_index') !== false ) {
    ini_set('memory_limit', '256M');
  }

  // Disable the allow_insecure_directives on prod. Currently there is a bug
  // with a security check that prevents some image styles from generating.
  // This disables that security check.
  $config['image.settings']['allow_insecure_derivatives'] = TRUE;
} else {
  // Local environments (non-Pantheon).

  // Services.
  $settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';
  $settings['container_yamls'][] = DRUPAL_ROOT . '/sites/blt.development.services.yml';

  // Config split settings.
  $config['config_split.config_split.non_local']['status'] = FALSE;
  $config['config_split.config_split.local']['status'] = TRUE;
  $config['config_split.config_split.dev']['status'] = FALSE;
  $config['config_split.config_split.stage']['status'] = FALSE;
  $config['config_split.config_split.prod']['status'] = FALSE;

  // Assertions.
  // @see http://php.net/assert
  // @see https://www.drupal.org/node/2492225
  assert_options(ASSERT_ACTIVE, TRUE);
  \Drupal\Component\Assertion\Handle::register();

  // Other development helpers.
  $config['system.logging']['error_level'] = 'verbose';
  $config['system.performance']['css']['preprocess'] = FALSE;
  $config['system.performance']['js']['preprocess'] = FALSE;

  $settings['cache']['bins']['render'] = 'cache.backend.null';
  $settings['cache']['bins']['discovery_migration'] = 'cache.backend.memory';
  $settings['cache']['bins']['page'] = 'cache.backend.null';
  $settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';

  $settings['rebuild_access'] = TRUE;
  $settings['skip_permissions_hardening'] = TRUE;
}
