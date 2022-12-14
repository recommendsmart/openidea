<?php

namespace Drupal\social_comment;

use Drupal\comment\CommentInterface;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Class to define the comment breadcrumb builder.
 */
class SocialCommentBreadcrumbBuilder implements BreadcrumbBuilderInterface {
  use StringTranslationTrait;

  /**
   * The comment storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * Constructs the SocialCommentBreadcrumbBuilder.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->storage = $entity_manager->getStorage('comment');
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $comments_routes = [
      'comment.reply',
      'entity.comment.edit_form',
      'entity.comment.delete_form',
    ];
    return in_array($route_match->getRouteName(), $comments_routes);
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addCacheContexts(['route']);
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home'), '<front>'));
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home'), '<front>'));

    switch ($route_match->getRouteName()) {
      case 'comment.reply':
        $page_title = $this->t('Reply to Comment');
        $pid = $route_match->getParameter('pid');
        if ($pid) {
          $comment = $this->storage->load($pid);
        }
        break;

      case 'entity.comment.edit_form':
        $page_title = $this->t('Edit Comment');
        $comment = $route_match->getParameter('comment');
        break;

      case 'entity.comment.delete_form':
        $page_title = $this->t('Delete Comment');
        $comment = $route_match->getParameter('comment');
        break;

      default:
        $page_title = $this->t('Comment');
    }

    // Add Entity path to Breadcrumb for Reply.
    if ($route_match->getParameter('entity') &&
      $route_match->getParameter('entity') instanceof EntityInterface) {
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      $entity = $route_match->getParameter('entity');
      $label = $entity->label();
      if (!empty($label)) {
        $breadcrumb->addLink(new Link($label, $entity->toUrl()));
      }
      $breadcrumb->addCacheableDependency($entity);
    }

    // Add Caching.
    if (
      isset($comment) &&
      $comment instanceof CommentInterface
    ) {
      $breadcrumb->addCacheableDependency($comment);
    }

    // Display link to current page.
    $breadcrumb->addLink(new Link($page_title, Url::fromRoute('<current>')));

    return $breadcrumb;
  }

}
