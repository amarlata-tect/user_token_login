<?php

namespace Drupal\user_token_login\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\user_token_login\Service\AuthLoginDefault;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Anonymous User Session Subscriber.
 *
 * Create session for a user if token is valid.
 */
class AnonymousUserSessionSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;
  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The auth default.
   *
   * @var \Drupal\user_token_login\Service\AuthLoginDefault
   */
  protected $authDefault;

  /**
   * The kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * Constructs the FirstMiddleware object.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   * @param \Drupal\Core\Messenger\MessengerInterface|null $config_factory
   *   The config object.
   * @param \Drupal\Core\Messenger\MessengerInterface|null $messenger
   *   The messenger.
   * @param \Drupal\user_token_login\Service\AuthLoginDefault $authDefault
   *   The user auth token.
   */
  public function __construct(AccountInterface $account, ConfigFactoryInterface $config_factory, MessengerInterface $messenger, AuthLoginDefault $authDefault) {
    $this->account = $account;
    $this->configFactory = $config_factory;
    $this->messenger = $messenger;
    $this->authDefault = $authDefault;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['userLoginByToken', 28];

    return $events;
  }

  /**
   * Create session for a valid user auth token.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The event request.
   */
  public function userLoginByToken(RequestEvent $event) {
    $request = $event->getRequest();
    $authToken = $request->query->get('authtoken');
    if ($authToken) {
      $redirectUrl = '/';
      if ($this->account->isAuthenticated()) {
        // Redirect if the user is logged in.
        $redirectUrl = Url::fromRoute('entity.user.canonical',
          ['user' => $this->account->id()])->toString();
      }
      elseif ($this->account->isAnonymous()) {
        $userObject = $this->authDefault->loadByUserProperties(['field_auth_token' => $authToken]);
        // Login if User token is valid.
        if (!empty($userObject)) {
          $user = reset($userObject);
          user_login_finalize($user);
          $this->messenger->addMessage($this->t('%user user logged in successfully.', ['%user' => $user->getAccountName()]));
          $redirectUrl = Url::fromRoute('entity.user.canonical',
            ['user' => $user->id()])->toString();
        }
        else {
          // Redirect if token is not valid.
          $this->messenger->addMessage($this->t('Token is not valid.'), 'error');
          $redirectUrl = '/';
        }
      }
      $response = new RedirectResponse($redirectUrl);
      $event->setResponse($response);
    }
  }

}
