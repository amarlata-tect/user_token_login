<?php

namespace Drupal\user_token_login\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for User Auth Login.
 */
class AuthTokenSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_token_login_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'user_token_login.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('user_token_login.settings');

    $form['token_length'] = [
      '#type' => 'number',
      '#title' => $this->t('Token length'),
      '#description' => $this->t('Enter a number to generate a token.'),
      '#default_value' => $config->get('token_length'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('user_token_login.settings')
      ->set('token_length', $form_state->getValue('token_length'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
