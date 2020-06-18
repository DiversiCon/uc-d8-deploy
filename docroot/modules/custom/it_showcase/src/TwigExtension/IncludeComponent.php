<?php

namespace Drupal\it_showcase\TwigExtension;

use Drupal\it_showcase\Showcase;

/**
 * A class that provides an included showcase component.
 *
 * @package Drupal\it_showcase
 */
class IncludeComponent extends \Twig_Extension {

  /**
   * Showcase elements definition.
   *
   * @var \Drupal\it_showcase\Showcase
   */
  protected $showcase;

  /**
   * Constructor for twig extension.
   *
   * @param \Drupal\it_showcase\Showcase $showcase
   *   Showcase Object.
   */
  public function __construct(Showcase $showcase) {
    $this->showcase = $showcase;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'it_showcase.twig.include_component';
  }

  /**
   * Declare the Twig extension functions.
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('it_showcase_component',
        [$this, 'renderComponent']
      ),
    ];
  }

  /**
   * Helper function to get the component render array.
   *
   * @param string $component_id
   *   Component ID.
   * @param mixed $delta
   *   Variant ID (delta).
   *
   * @return array
   *   Component render array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function renderComponent($component_id, $delta = FALSE) {
    // We need to namespace the provided component id.
    $component_id = $component_id . '.component';
    return $this->showcase->renderComponent($component_id, $delta);
  }

}
