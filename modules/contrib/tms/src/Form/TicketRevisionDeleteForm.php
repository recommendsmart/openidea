<?php

namespace Drupal\tms\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Ticket revision.
 *
 * @ingroup tms
 */
class TicketRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Ticket revision.
   *
   * @var \Drupal\tms\Entity\TicketInterface
   */
  protected $revision;

  /**
   * The Ticket storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $ticketStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->ticketStorage = $container->get('entity_type.manager')->getStorage('ticket');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ticket_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
      '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.ticket.version_history', ['ticket' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $ticket_revision = NULL) {
    $this->revision = $this->TicketStorage->loadRevision($ticket_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->TicketStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Ticket: deleted %title revision %revision.', ['%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    $this->messenger()->addMessage(t('Revision from %revision-date of Ticket %title has been deleted.', ['%revision-date' => format_date($this->revision->getRevisionCreationTime()), '%title' => $this->revision->label()]));
    $form_state->setRedirect(
      'entity.ticket.canonical',
       ['ticket' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {ticket_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.ticket.version_history',
         ['ticket' => $this->revision->id()]
      );
    }
  }

}
