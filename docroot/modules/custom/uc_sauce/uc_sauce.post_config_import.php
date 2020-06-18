<?php

use Drupal\paragraphs\Entity\Paragraph;

/**
 * Migrate data content from 1 field within accordion_item paragraph to another.
 *
 * @throws \Drupal\Core\Entity\EntityStorageException
 */
function uc_sauce_post_config_import_001() {
  // Retrieve all accordion_item paragraph ids.
  $pids = \Drupal::entityQuery('paragraph')
    ->condition('type', 'accordion_item')
    ->execute();

  foreach ($pids as $pid) {
    // Load the paragraph by id.
    $accordion_item = Paragraph::load($pid);

    // Get the current WYSIWYG content.
    $current_content = $accordion_item
      ->get('field_par_html_single')->getValue()[0]['value'];

    // Create the new smartbody_lite paragraph with the current content.
    $smartbody_lite = Paragraph::create([
      'type' => 'smartbody_lite',
      'field_smartbody_text' => [
        'value' => $current_content,
        'format' => 'smartbody',
      ],
    ]);

    // Save the new paragraph.
    $smartbody_lite->save();

    // Set the reference for accordion_item paragraph reference to the
    // newly created smartbody_lite paragraph.
    $accordion_item->set('field_par_paragraph_single', [
      'target_id' => $smartbody_lite->id(),
      'target_revision_id' => $smartbody_lite->getRevisionId(),
    ]);

    // Save the accordion_item paragraph.
    $accordion_item->save();
  }
}

/**
 * Set all quick_links_two_column paragraphs to display accordion on mobile.
 *
 * @throws \Drupal\Core\Entity\EntityStorageException
 */
function uc_sauce_post_config_import_002() {
  $pids = \Drupal::entityQuery('paragraph')
    ->condition('type', 'quick_links_two_column')
    ->execute();

  foreach ($pids as $pid) {
    $quicklinks = Paragraph::load($pid);

    $quicklinks->set('field_accordion_switch', 'mobile-accordion');
    $quicklinks->save();
  }
}

/**
 * Convert all quote paragraph items to a quote_list paragraph.
 *
 * @throws \Drupal\Core\Entity\EntityStorageException
 */
function uc_sauce_post_config_import_003() {
  // Load all main component paragraphs.
  $pids = \Drupal::entityQuery('paragraph')
    ->condition('type', 'components')
    ->execute();

  // Loop through each, looking for any quote components.
  foreach ($pids as $pid) {
    $components_paragraph = Paragraph::load($pid);

    $referenced_components = $components_paragraph
      ->get('field_par_paragraphs')
      ->referencedEntities();

    for ($i = 0; $i < count($referenced_components); $i++) {
      $component = $referenced_components[$i];

      if ($component->bundle() == 'quote') {
        // Save the index to insert with later.
        $insert_index = $i;

        // Save this quote to a new array of quote items.
        $quote_items = [
          $component,
        ];

        // Look at the next item in the list.
        for ($j = $i + 1; $j < count($referenced_components); $j++) {
          $next_component = $referenced_components[$j];

          // If it's a quote, add it to the array and look at the next item.
          if ($next_component->bundle() == 'quote') {
            // Add the item to the quote list.
            $quote_items[] = $next_component;

            // Remove it from the original list of items.
            unset($referenced_components[$j]);

            // Update the index of the original loop.
            $i = $j;
          }
          else {
            // This item was not a quote. Break the loop.
            break;
          }
        }

        // Create a new quote list.
        $quote_list = Paragraph::create([
          'type' => 'quote_list',
        ]);

        $quote_list->set('field_paragraphs_unlimited', $quote_items);

        $quote_list->save();

        // Insert quote list to the position of the original first quote.
        $referenced_components[$insert_index] = $quote_list;
      }
    }

    // Set the list of components and save.
    $components_paragraph->set('field_par_paragraphs', $referenced_components);

    $components_paragraph->save();
  }

}

/**
 * Convert contact group text field to rich text.
 *
 * @throws \Drupal\Core\Entity\EntityStorageException
 */
function uc_sauce_post_config_import_004() {
  $pids = \Drupal::entityQuery('paragraph')
    ->condition('type', 'contact_group')
    ->execute();

  foreach ($pids as $pid) {
    // Load the paragraph by id.
    $contact_group = Paragraph::load($pid);

    // Get the current content.
    $current_content = $contact_group
      ->get('field_description_plaintext')->getValue()[0]['value'];

    $contact_group->set('field_body', [
      'value' => $current_content,
      'format' => 'basic_html',
    ]);

    $contact_group->save();
  }

}
