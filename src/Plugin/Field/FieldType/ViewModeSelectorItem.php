<?php

namespace Drupal\view_mode_selector\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'list_string' field type.
 *
 * @FieldType(
 *   id = "view_mode_selector",
 *   label = @Translation("View Mode Selector"),
 *   description = @Translation("This field stores entity view mode."),
 *   default_widget = "view_mode_selector_radios",
 *   default_formatter = "view_mode_selector",
 * )
 */
class ViewModeSelectorItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'view_modes' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('View mode'))
      ->addConstraint('Length', ['max' => 255])
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'varchar',
          'length' => 255,
        ],
      ],
      'indexes' => [
        'value' => ['value'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    $settings = $this->getSettings();
    $entity_type = $this->definition->getFieldDefinition()->getTargetEntityTypeId();
    $bundle = $this->definition->getFieldDefinition()->getTargetBundle();

    // Get all view modes for the current bundle.
    $view_modes = \Drupal::service('entity_display.repository')->getViewModeOptionsByBundle($entity_type, $bundle);

    foreach ($view_modes as $view_mode_id => $view_mode_label) {
      if (!isset($element['view_modes'])) {
        $element['view_modes'] = [
          '#type' => 'fieldset',
          '#tree' => TRUE,
          '#title' => t('Available view modes'),
          '#attributes' => ['class' => ['view-mode-selector-view-modes']],
        ];
      }

      $element['view_modes'][$view_mode_id]['enable'] = [
        '#type' => 'checkbox',
        '#title' => $view_mode_label . ' (' . $view_mode_id . ')',
        '#default_value' => isset($settings['view_modes'][$view_mode_id]) && $settings['view_modes'][$view_mode_id]['enable'] ?: FALSE,
      ];

      $element['view_modes'][$view_mode_id]['prefix']['#markup'] = '<div class="settings">';

      $element['view_modes'][$view_mode_id]['hide_title'] = [
        '#type' => 'checkbox',
        '#title' => t('Hide title'),
        '#default_value' => isset($settings['view_modes'][$view_mode_id]) && $settings['view_modes'][$view_mode_id]['hide_title'] ?: FALSE,
        '#states' => [
          'visible' => [
            'input[name="field[settings][view_modes][' . $view_mode_id . '][enable]"]' => ['checked' => TRUE],
          ],
        ]
      ];

      $element['view_modes'][$view_mode_id]['icon'] = [
        '#type' => 'textfield',
        '#title' => t('Icon'),
        '#description' => t('An icon which can be used for a view mode preview.'),
        '#default_value' => (isset($settings['view_modes'][$view_mode_id]) && $settings['view_modes'][$view_mode_id]['icon']) ? $settings['view_modes'][$view_mode_id]['icon'] : '',
        '#states' => [
          'visible' => [
            'input[name="field[settings][view_modes][' . $view_mode_id . '][enable]"]' => ['checked' => TRUE],
          ],
        ],
      ];
      $element['view_modes'][$view_mode_id]['suffix']['#markup'] = '</div>';

    }
    return $element;
  }
}
