<?php

namespace Drupal\uc_migrate\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d7\Node as D7Node;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\uc_migrate\MigrateHelper;

/**
 * Custom D7 node source that returns additional node related data.
 *
 * @MigrateSource(
 *   id = "d7_node_story",
 *   source_module = "node"
 * )
 */
class D7NodeStory extends D7Node {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'smartbody' => $this->t('Smartbody'),
      'preview_text' => $this->t('Preview Text'),
      'inline_images' => $this->t('Inline Images'),
    ] + parent::fields();
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    parent::prepareRow($row);

    // Build preview/smartbody text.
    if (NULL != $row->getSourceProperty('body')) {
      $body = $row->getSourceProperty('body');
      if (isset($body[0]['value'])) {
        $text = $body[0]['value'];

        $text = $this->adjustSmartbody($text);
        $row->setSourceProperty('smartbody', $text);
      }
      if (isset($body[0]['summary'])) {
        $row->setSourceProperty('preview_text', $body[0]['summary']);
      }
    }

    // Include proper content to identify inline image components.
    $inline_images = [];
    $images = $row->getSourceProperty('field_maincontentimage');
    if ($images) {
      foreach ($images as $delta => $image) {

        $nid = $row->getSourceProperty('nid');
        $database = \Drupal::service('database');

        // Hacky approach...get wide inline images (order could be off).
        $query = $database->select('migrate_map_migrate_content_story_images_wide', 'map');
        $query->fields('map', [
          'destid1',
          'destid2',
        ]);
        $query->condition('sourceid1', $row->getSourceProperty('nid'));
        $query->condition('sourceid2', $row->getSourceProperty('vid'));
        $query->condition('sourceid3', $delta);
        $destination = $query->execute()->fetch();
        if ($destination) {
          $inline_images[] = [
            'target_id' => $destination->destid1,
            'target_revision_id' => $destination->destid2,
            'delta' => $delta,
            'fid' => $image['fid'],
          ];
        }

        // Hacky approach...get inset inline images (order could be off).
        $query = $database->select('migrate_map_migrate_content_story_images_inset', 'map');
        $query->fields('map', [
          'destid1',
          'destid2',
        ]);
        $query->condition('sourceid1', $row->getSourceProperty('nid'));
        $query->condition('sourceid2', $row->getSourceProperty('vid'));
        $query->condition('sourceid3', $delta);
        $destination = $query->execute()->fetch();
        if ($destination) {
          $inline_images[] = [
            'target_id' => $destination->destid1,
            'target_revision_id' => $destination->destid2,
            'delta' => $delta,
            'fid' => $image['fid'],
          ];
        }
      }
    }

    // Now we have the whole process of putting those images in correct order.
    $ordered = [];
    $sorted_inline_images = [];
    $helper = new MigrateHelper();
    $nid = $row->getSourceProperty('nid');
    if ($inline_images && $nid) {
      $ordered = $helper->getOrderedImagesNode($nid);

      // Loop through ordered images, generating a sorted inline images array.
      if ($ordered) {
        foreach ($ordered as $ordered_delta => $ordered_image) {

          // See if we have this fid in the inline images array.
          $found = $this->findInlineImageFid($ordered_image['fid'], $inline_images);
          if ($found !== FALSE) {
            $sorted_inline_images[] = $inline_images[$found];
          }
        }
      }
    }
    if ($sorted_inline_images) {
      $inline_images = $sorted_inline_images;
    }

    // Add inline images to row data.
    $row->setSourceProperty('inline_images', $inline_images);

    return TRUE;
  }

  /**
   * Helper function to search inline images for specific fid.
   *
   * @param string $id
   *   File id.
   * @param array $images
   *   Array of images.
   *
   * @return bool|int|string
   *   Key of found id.
   */
  private function findInlineImageFid($id, array $images) {
    foreach ($images as $key => $image) {
      if ($image['fid'] == $id) {
        return $key;
      }
    }
    return FALSE;
  }

  /**
   * Helper function to adjust text for smartbody components.
   *
   * @param string $text
   *   Full original text.
   *
   * @return mixed
   *   New text value.
   */
  private function adjustSmartbody($text) {
    // Remove any existing hr tags.
    $new_text = str_replace('<hr />', '', $text);
    if ($new_text) {
      $text = $new_text;
    }

    // Replace img tags with hr.
    $new_text = preg_replace('/<img[^>]+\>/i', '<hr />', $text);
    if ($new_text) {
      $text = $new_text;
    }

    // Remove paragraphs wrapping hr tag.
    $new_text = str_replace('<p><hr /></p>', '<hr />', $text);
    if ($new_text) {
      $text = $new_text;
    }

    // Adjust path for internal links that point to files directory.
    $new_text = str_replace('sites/pritzker.uchicago.edu/files/', 'sites/pritzker/files/legacy/', $text);
    if ($new_text) {
      $text = $new_text;
    }

    return $text;
  }

  /**
   * Helper function to create image render array.
   *
   * @param string $token
   *   Token value.
   *
   * @return string
   *   Render array.
   */
  public function renderImage($token) {
    $html = '';

    // Load file.
    $file = File::load($token->fid);
    if ($file) {
      $variables = [];

      // The image.factory service will check if our image is valid.
      $image = \Drupal::service('image.factory')->get($file->getFileUri());
      if ($image->isValid()) {
        $variables['width'] = $image->getWidth();
        $variables['height'] = $image->getHeight();
      }
      else {
        $variables['width'] = $variables['height'] = NULL;
      }

      // Build image path based on image_style.
      $url = ImageStyle::load('max_width_768')->buildUrl($file->getFileUri());

      // Alt/Title text.
      $alt = isset($token->attributes->alt) ? trim($token->attributes->alt) : '';
      $title = isset($token->attributes->title) ? trim($token->attributes->alt) : '';

      // Build image render array.
      $build = [
        '#type' => 'html_tag',
        '#tag' => 'img',
        '#attributes' => [
          'src' => file_url_transform_relative($url),
          'height' => $variables['height'],
          'width' => $variables['width'],
          'alt' => $alt,
          'title' => $title,
        ],
      ];

      // Render image markup.
      $renderer = \Drupal::service('renderer');
      $html = (string) $renderer->renderRoot($build);
      $html = trim($html);
    }
    return $html;
  }

}
