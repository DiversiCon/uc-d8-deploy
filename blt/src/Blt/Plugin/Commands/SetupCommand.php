<?php

namespace Acquia\Blt\Plugin\Commands;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * BLT commands class.
 */
class SetupCommand extends BltTasks {

  /**
   * Wraps the 'blt setup' command.
   *
   * Performs custom frontend tasks before executing 'blt setup'.
   *
   * @throws \Robo\Exception\TaskException
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   *
   * @command uc:setup
   */
  public function setup() {

    $task = $this->taskExecStack()
      ->stopOnFail()
      ->dir($this->getRepoRoot())
      ->exec('blt frontend:setup')
      ->exec('blt setup');

    $result = $task->run();
    $exit_code = $result->getExitCode();

    if ($exit_code) {
      throw new BltException("Setup failed.");
    }

  }

  /**
   * Copies settings files needed for running Drupal VM.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   *
   * @command uc:setup:vm
   */
  public function drupalVmSetup() {
    $result = $this->taskFilesystemStack()
      ->copy(
        $this->getRepoRoot() . '/docroot/sites/default/default.local.drupalvm.settings.php',
        $this->getRepoRoot() . '/docroot/sites/default/settings/local.settings.php')
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Drupal VM setup failed.");
    }
  }

  /**
   * Copies settings files needed for running Lando.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   *
   * @command uc:setup:lando
   */
  public function drupalLandoSetup() {
    $result = $this->taskFilesystemStack()
      ->copy(
        $this->getRepoRoot() . '/docroot/sites/default/default.local.settings.php',
        $this->getRepoRoot() . '/docroot/sites/default/settings/local.settings.php')
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Drupal VM setup failed.");
    }
  }

  /**
   * Helper method to get repository root.
   */
  private function getRepoRoot() {
    return $this->getConfigValue('repo.root');
  }

}
