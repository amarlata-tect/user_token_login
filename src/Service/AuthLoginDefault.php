<?php

namespace Drupal\user_token_login\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Generate token for users.
 */
class AuthLoginDefault {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Random Number.
   *
   * @var string
   */
  protected $tokenMetadata = '01234567896789234567896780123456780125678785678018012567876394934674';

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory     = $config_factory;
    $this->logger            = $logger_factory->get('user_token_login');
  }

  /**
   * Returns a shortcut for entity type storage.
   */
  public function getStorage($type = 'user') {
    return $this->entityTypeManager->getStorage($type);
  }

  /**
   * Generate and return a random token.
   */
  public function generateRandomToken() {
    $config = $this->configFactory->get('user_token_login.settings');
    $token_length = $config->get('token_length');
    $token = substr(str_shuffle($this->tokenMetadata), 0, $token_length);
    $userExist = $this->loadByUserProperties(['field_auth_token' => $token]);
    return !empty($userExist) ? $this->generateRandomToken() : $token;
  }

  /**
   * Save token for existing users.
   */
  public function userTokenGenerate() {
    $uids = $this->getStorage()->getQuery()->execute();
    $users = $this->getStorage('user')->loadMultiple($uids);
    foreach ($users as $user) {
      $token = $this->generateRandomToken();
      try {
        $user->set('field_auth_token', $token);
        $user->save();
      }
      catch (\Exception $e) {
        $this->logger
          ->error('There was an error when update ' . $user->id() . ' user id. Exception: ' . $e->getMessage());
      }
    }
  }

  /**
   * Returns user object by its properties.
   *
   * @param array $values
   *   $values Fields array values combination.
   * @param string $type
   *   $type Entity type.
   *
   * @return object
   *   An object with the following fields.
   */
  public function loadByUserProperties(
    array $values,
    $type = 'user'
  ) {
    return $this->getStorage($type)->loadByProperties($values);
  }

  /**
   * Delete field configurations.
   */
  public function deleteFieldConfig() {
    $field_storage = FieldStorageConfig::loadByName('user', 'field_auth_token');
    try {
      if (!empty($field_storage)) {
        $field_storage->delete();
      }
    }
    catch (EntityStorageException $e) {
      $this->logger
        ->warning('There is an error to delete the field configurations ' . $e->getMessage());
    }
  }

}
