<?php

namespace Drupal\uc_api\Plugin\views\style;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\rest\Plugin\views\style\Serializer;

/**
 * The style plugin for serialized output formats.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "uc_list_pager_serializer",
 *   title = @Translation("List with Pager Serializer"),
 *   help = @Translation("Serializes views row data with pager information using the Serializer component."),
 *   display_types = {"data"}
 * )
 */
class ListWithPagerSerializer extends Serializer implements CacheableDependencyInterface {

  /**
   * {@inheritdoc}
   */
  public function render() {
    $rows = [];
    $total = 0;
    $current = 0;
    if ($this->view->pager->getTotalItems()) {
      if ($this->view->pager->getTotalItems() <= $this->view->pager->getItemsPerPage()) {
        $total = 1;
      }
      else {
        $total = ceil($this->view->pager->getTotalItems() / $this->view->pager->getItemsPerPage());
      }
      $current = $this->view->pager->getCurrentPage() + 1;
      // If the Data Entity row plugin is used, this will be an array of
      // entities which will pass through Serializer to one of the registered
      // Normalizers, which will transform it to arrays/scalars. If the Data
      // field row plugin is used, $rows will not contain objects and will pass
      // directly to the Encoder.
      foreach ($this->view->result as $row_index => $row) {
        $this->view->row_index = $row_index;
        $rows[] = $this->view->rowPlugin->render($row);
      }
      unset($this->view->row_index);
    }

    // Get the content type configured in the display or fallback to the
    // default.
    if ((empty($this->view->live_preview))) {
      $content_type = $this->displayHandler->getContentType();
    }
    else {
      $content_type = !empty($this->options['formats']) ? reset($this->options['formats']) : 'json';
    }

    // Set pager values.
    $pager = [
      'current' => $current,
      'total' => $total,
      'totalResults' => (int) $this->view->pager->getTotalItems(),
    ];
    return $this->serializer->serialize(['items' => $rows, 'pager' => $pager], $content_type);
  }

}
