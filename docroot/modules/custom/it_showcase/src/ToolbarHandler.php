<?php

namespace Drupal\it_showcase;

/**
 * Adds a simple toolbar link for showcase index.
 *
 * This is a simplified version derived from the toolbar handler defined in the
 * devel module. It is certainly possible to also adopt the full functionality
 * from that implementation by adding a tray section to expand additional
 * showcase menu items. However, there is a ton of extra baggage that goes
 * along with that level of sophistication that is simply not needed at this
 * time.  If we ever want to revisit that option, see the following:
 *
 * @see `docroot/modules/contrib/devel/devel.module`
 * @see `docroot/modules/contrib/devel/src/ToolbarHandler.php`
 * @see `docroot/modules/contrib/devel/config/install/system.menu.devel.yml`
 * @see `docroot/modules/contrib/devel/devel.links.menu.yml`
 * @see `https://drupal.stackexchange.com/questions/215394/how-can-i-add-items-to-the-admin-toolbar`
 */

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Toolbar integration handler.
 */
class ToolbarHandler implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * ToolbarHandler constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The current user.
   */
  public function __construct(AccountProxyInterface $account) {
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user')
    );
  }

  /**
   * Hook bridge.
   *
   * @return array
   *   The showcase toolbar item render array.
   *
   * @see hook_toolbar()
   */
  public function toolbar() {
    $items['it_showcase'] = [
      '#cache' => [
        'contexts' => ['user.permissions'],
      ],
    ];

    if ($this->account->hasPermission('access showcase pages')) {
      $items['it_showcase'] += [
        '#type' => 'toolbar_item',
        '#weight' => 999,
        'tab' => [
          '#type' => 'link',
          '#title' => $this->t('Showcase'),
          '#url' => Url::fromRoute('it_showcase.index'),
          '#attributes' => [
            'title' => $this->t('Showcase index'),
            'class' => ['toolbar-icon', 'toolbar-icon-it-showcase'],
          ],
        ],
        '#attached' => [
          'library' => 'it_showcase/it_showcase.toolbar',
        ],
      ];
    }

    return $items;
  }

}
