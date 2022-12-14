<?php

/**
 * @file
 * Contains post.page.inc..
 *
 * Page callback for Post entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\social_post\Entity\Post;

/**
 * Prepares variables for Post templates.
 *
 * Default template: post.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_post(array &$variables) {
  // Fetch Post Entity Object.
  $post = $variables['elements']['#post'];

  if ($post instanceof Post) {
    // Helpful $content variable for templates.
    foreach (Element::children($variables['elements']) as $key) {
      $variables['content'][$key] = $variables['elements'][$key];
    }

    // Display Post created date in format 'time ago'.
    $created_time_ago = \Drupal::service('date.formatter')
      ->formatTimeDiffSince($post->getCreatedTime(), [
        'granularity' => 1,
        'return_as_object' => TRUE,
      ]);
    $date = t('%time ago', ['%time' => $created_time_ago->getString()]);
    $variables['date'] = Link::fromTextAndUrl($date, $post->toUrl('canonical'));

    // To change user picture settings (e.g. image style), edit the 'compact'
    // view mode on the User entity. Note that the 'compact' view mode might
    // not be configured, so remember to always check the theme setting first.
    $account = $post->getOwner();
    $storage = \Drupal::entityTypeManager()->getStorage('profile');
    $user_profile = $storage->loadByUser($account, 'profile');
    if ($user_profile !== NULL) {
      $content = \Drupal::entityTypeManager()
        ->getViewBuilder('profile')
        ->view($user_profile, 'compact');
      $variables['author_picture'] = $content;
    }

    // Show a post has been edited.
    if ($post->getCreatedTime() != $post->getChangedTime()) {
      $variables['modified'] = TRUE;
    }

    // Visibility icon and label for template use.
    $visibility = $post->field_visibility->value;
    $variables['visibility_icon'] = social_post_get_visibility_details($visibility);
    $variables['visibility_label'] = social_post_get_visibility_details($visibility, 'label');

    $variables['published'] = $post->isPublished();
  }
}
