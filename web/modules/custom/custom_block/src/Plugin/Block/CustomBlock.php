<?php

namespace Drupal\custom_block\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a 'Custom Block' Block.
 *
 * @Block(
 *   id = "custom_block",
 *   admin_label = @Translation("Custom block"),
 *   category = @Translation("Custom"),
 * )
 */
class CustomBlock extends BlockBase implements BlockPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $config = $this->getConfiguration();
        $custom_copyright = $config['custom_copyright'];

        return [
            '#markup' => "<span>$custom_copyright</span>",
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function blockForm($form, FormStateInterface $form_state)
    {
        $form = parent::blockForm($form, $form_state);

        $config = $this->getConfiguration();

        $current_year = date("Y");
        $custom_copyright = $this->t('©@year custom. All Rights Reserved.', [
            '@year' => $current_year
        ]);

        $form['custom_copyright'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Copyright'),
            '#description' => $this->t('Write your copyright text.'),
            '#default_value' => isset($config['custom_copyright']) ? $config['custom_copyright'] : $custom_copyright,
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state)
    {
        parent::blockSubmit($form, $form_state);
        $this->setConfigurationValue('custom_copyright', $form_state->getValue('custom_copyright'));
    }


    /**
     * {@inheritdoc}
     */
    public function blockValidate($form, FormStateInterface $form_state)
    {
        if (empty($form_state->getValue('custom_copyright'))) {
            $form_state->setErrorByName('custom_copyright', t('This field is required'));
        }
    }

    /**
     * {@inheritdoc}
     * return 0 If you want to disable caching for this block.
     */
    public function getCacheMaxAge()
    {

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function access(AccountInterface $account, $return_as_object = FALSE)
    {
        return AccessResult::allowedIfHasPermission($account, 'access content');
    }

}