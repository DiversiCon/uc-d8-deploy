<?php

namespace Acquia\Blt\Plugin\Commands;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * BLT commands class.
 */
class DeployCommand extends BltTasks {

  /**
   * BLT location.
   *
   * @var string
   */
  protected $blt = "./vendor/bin/blt";

  /**
   * Executes frontend tasks, and then executes blt deploy.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   * @throws \Robo\Exception\TaskException
   *
   * @command uc:deploy
   */
  public function deploy() {

    // Determine the frontend build command.
    $fe_build_cmd = 'frontend:compile:prod';

    // Set the deploy command. This will vary on the deploy type.
    $name = 'master';
    $environment = 'pantheon';
    $deploy_cmd = "deploy --branch=$name --ignore-dirty --commit-msg='Manual deploy to $environment environment.'";

    // Execute the deploy.
    // @codingStandardsIgnoreStart
    $task = $this->taskExecStack()
      ->stopOnFail()
      ->dir($this->getRepoRoot())
      ->exec("$this->blt frontend:setup")
      ->exec("$this->blt $fe_build_cmd")
      ->exec("$this->blt $deploy_cmd");
    // @codingStandardsIgnoreEnd

    $result = $task->run();
    $exit_code = $result->getExitCode();

    if ($exit_code) {
      throw new BltException("Deploy failed.");
    }
  }

  /**
   * Helper method to get repository root.
   */
  private function getRepoRoot() {
    return $this->getConfigValue('repo.root');
  }

}
