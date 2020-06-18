<?php

namespace Drupal\uc_twigfilters\TwigExtension;

/**
 * Class DefaultService.
 *
 * @package Drupal\uc_twigfilters
 */
class uc_twigfiltersExtension extends \Twig_Extension {

  /**
   * {@inheritdoc}
   *
   * This function must return the unique name of the extension.
   */
  public function getName() {
    return 'uc_twigfilters.twig_extension';
  }

  /**
   * Generates a list of all Twig filters that this extension defines.
   */
  public function getFilters() {
    return [
      new \Twig_SimpleFilter('convertQuotes', [$this, 'convertQuotes'], ['is_safe' => ['html']]),
      new \Twig_SimpleFilter('convertForJson', [$this, 'convertForJson'], ['is_safe' => ['html']]),
    ];
  }

  /**
   * Filter to convert quotes to safe entities for Vue props.
   * @param $txt
   *
   * @return string
   *
   * @throws \Twig\Error\RuntimeError
   */
  public static function convertQuotes($txt) {
    $output = $txt;
    if (!is_array($txt)) {
      $output = twig_replace_filter(
        $txt,
        [
          '“' => '&ldquo;',
          '”' => '&rdquo;',
          '"' => '&quot;',
          "'" => "\'",
        ]
      );
    }
    return twig_raw_filter($output);
  }

  /**
   * Filter to convert text for JSON objects.
   *
   * @param string $text
   *   The text to convert.
   *
   * @return string
   *   The converted text.
   *
   * @throws \Twig\Error\RuntimeError
   */
  public static function convertForJson($text) {
    $output = twig_replace_filter(
      $text,
      [
        '"' => '\"',
        "'" => "\'",
      ]
    );

    return twig_raw_filter($output);
  }

}
