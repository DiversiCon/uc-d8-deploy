<?php

namespace Acquia\Blt\Plugin\Commands;

use Acquia\Blt\Robo\BltTasks;

/**
 * BLT commands class.
 */
class ConfigCommand extends BltTasks {

  /**
   * Runs updb and config-import.
   *
   * @throws \Robo\Exception\TaskException
   *
   * @command uc:update
   */
  public function update() {
    $this->taskDrush()
      ->stopOnFail()
      ->drush('updb')
      ->run();

    $this->configImport();
  }

  /**
   * Runs config-import.
   *
   * @throws \Robo\Exception\TaskException
   *
   * @command uc:config-import
   */
  public function configImport() {
    $this->taskDrush()->drush('config-import')->run();
  }

}
