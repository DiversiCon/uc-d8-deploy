<?php

namespace Acquia\Blt\Plugin\Commands;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * BLT commands class.
 */
class FrontendCommand extends BltTasks {

  /**
   * Performs setup tasks on designated sites/themes.
   *
   * @param array $options
   *   Command options.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   * @throws \Robo\Exception\TaskException
   *
   * @command frontend:setup
   * @aliases fe:setup
   */
  public function setup(array $options) {
    $sites = $this->parseSiteOption($options['site']);

    $theme_dirs = $this->getThemeDir($sites);

    foreach ($theme_dirs as $dir) {
      $task = $this->taskExecStack()
        ->stopOnFail()
        ->dir($dir)
        ->exec('npm install')
        ->exec('npm rebuild node-sass');

      $result = $task->run();
      $exit_code = $result->getExitCode();

      if ($exit_code) {
        throw new BltException("Frontend setup failed for $dir.");
      }
    }

  }

  /**
   * Compiles all assets in an uncompressed format.
   *
   * @param array $options
   *   Command options.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   * @throws \Robo\Exception\TaskException
   *
   * @command frontend:compile:dev
   * @aliases fe:c:dev
   */
  public function compileDev(array $options) {
    $sites = $this->parseSiteOption($options['site']);

    $theme_dirs = $this->getThemeDir($sites);

    foreach ($theme_dirs as $dir) {
      $task = $this->taskExecStack()
        ->stopOnFail()
        ->dir($dir)
        ->exec('npm run dev --silent');

      $result = $task->run();
      $exit_code = $result->getExitCode();

      if ($exit_code) {
        throw new BltException('Frontend dev compilation failed.');
      }
    }

  }

  /**
   * Compiles all assets.
   *
   * @param array $options
   *   Command options.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   * @throws \Robo\Exception\TaskException
   *
   * @command frontend:compile:prod
   * @aliases fe:c:prod
   */
  public function compileProd(array $options) {
    $sites = $this->parseSiteOption($options['site']);

    $theme_dirs = $this->getThemeDir($sites);

    foreach ($theme_dirs as $dir) {
      $task = $this->taskExecStack()
        ->stopOnFail()
        ->dir($dir)
        ->exec('npm run production --silent');

      $result = $task->run();
      $exit_code = $result->getExitCode();

      if ($exit_code) {
        throw new BltException('Frontend prod compilation failed.');
      }
    }

  }

  /**
   * Watches a theme for changes, and re-compiles assets when they occur.
   *
   * @param array $options
   *   Command options.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   * @throws \Robo\Exception\TaskException
   *
   * @command frontend:watch
   * @aliases fe:w
   */
  public function watch(array $options) {
    $sites = $this->parseSiteOption($options['site']);

    $theme_dirs = $this->getThemeDir($sites);

    // In the even no theme is specified, the first theme returned is uccollege.
    $task = $this->taskExecStack()
      ->stopOnFail()
      ->dir($theme_dirs[0])
      ->exec('npm run watch');

    $result = $task->run();
    $exit_code = $result->getExitCode();

    if ($exit_code) {
      throw new BltException('Frontend watch failed.');
    }
  }

  /**
   * Creates a new frontend component.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   * @throws \Robo\Exception\TaskException
   *
   * @command frontend:new-component
   * @aliases fe:nc
   */
  public function newComponent() {
    // The script only lives in the uccollege theme.
    $theme_dirs = $this->getThemeDir(['uccollege']);

    $task = $this->taskExecStack()
      ->stopOnFail()
      ->dir($theme_dirs[0] . '/components')
      ->exec('./new-component.sh');

    $result = $task->run();
    $exit_code = $result->getExitCode();

    if ($exit_code) {
      throw new BltException('Component creation failed.');
    }
  }

  /**
   * Resets any specified themes by deleting and reinstalling npm modules.
   *
   * @param array $options
   *   Options array.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   * @throws \Robo\Exception\TaskException
   *
   * @command frontend:reset
   * @aliases fe:reset
   */
  public function reset(array $options) {
    $sites = $this->parseSiteOption($options['site']);

    $theme_dirs = $this->getThemeDir($sites);

    foreach ($theme_dirs as $dir) {
      $task = $this->taskExecStack()
        ->stopOnFail()
        ->dir($dir)
        ->exec('rm -r node_modules')
        ->exec('npm install')
        ->exec('npm rebuild node-sass');

      $result = $task->run();
      $exit_code = $result->getExitCode();

      if ($exit_code) {
        throw new BltException("Frontend reset failed for $dir.");
      }
    }

  }

  /**
   * Returns the absolute path to the theme directory.
   *
   * @param array $sites
   *   Sites array.
   *
   * @return array
   *   The absolute paths of all theme directories.
   */
  private function getThemeDir(array $sites) {
    // Define the themes/sites and their respective theme directories.
    $repo_root = $this->getConfigValue('repo.root');
    $dirs = [
      'uccollege' => $repo_root . '/docroot/themes/custom/uccollege',
      'thunder_admin' => $repo_root . '/docroot/themes/contrib/thunder_admin',
      'admin_uccollege' => $repo_root . '/docroot/themes/custom/admin_uccollege',
    ];

    $site_theme_dirs = [];

    // If sites were specified, return only those directories.
    if (isset($sites) && !empty($sites)) {
      foreach ($sites as $site) {
        $site_theme_dirs[] = $dirs[$site];
      }
    }
    else {
      // No sites were specified, return all theme directories.
      foreach ($dirs as $site_name => $dir) {
        $site_theme_dirs[] = $dir;
      }
    }

    return $site_theme_dirs;
  }

  /**
   * Parses a given value for the site option present in several commands.
   *
   * @param string $site
   *   The declared site(s).
   *
   * @return mixed
   *   Sites array.
   */
  private function parseSiteOption($site) {
    // If no value, return an empty array.
    if (empty($site)) {
      $sites = [];
    }
    else {
      // Otherwise, parse the given site option value.
      $sites = explode(',', $site);
    }

    return $sites;
  }

}
