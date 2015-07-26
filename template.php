<?php

function bartik_sub_form_element_label($variables) {
  $element = $variables ['element'];
  // This is also used in the installer, pre-database setup.
  $t = get_t();

  // If title and required marker are both empty, output no label.
  if ((!isset($element ['#title']) || $element ['#title'] === '') && empty($element ['#required'])) {
    return '';
  }

  // If the element is required, a required marker is appended to the label.
  $required = !empty($element ['#required']) ? theme('form_required_marker', array('element' => $element)) : '';

  $title = filter_xss_admin($element ['#title']);

  $attributes = array();
  // Style the label as class option to display inline with the element.
  if ($element ['#title_display'] == 'after') {
    $attributes ['class'][] = 'option';
  }
  // Show label only to screen readers to avoid disruption in visual flows.
  elseif ($element ['#title_display'] == 'invisible') {
    $attributes ['class'][] = 'element-invisible';
  }
  
  /***************************** Added label styling from bootstrap.css *****************************/
  $attributes['class'][] = 'col-sm-12';
  $attributes['class'][] = 'control-label';

  if (!empty($element ['#id'])) {
    $attributes ['for'] = $element ['#id'];
  }

  // The leading whitespace helps visually separate fields from inline labels.
  return ' <label' . drupal_attributes($attributes) . '>' . $t('!title !required', array('!title' => $title, '!required' => $required)) . "</label>\n";
}

function bartik_sub_form_element($variables) {
  $element = &$variables ['element'];

  // This function is invoked as theme wrapper, but the rendered form element
  // may not necessarily have been processed by form_builder().
  $element += array(
    '#title_display' => 'before',
  );

  // Add element #id for #type 'item'.
  if (isset($element ['#markup']) && !empty($element ['#id'])) {
    $attributes ['id'] = $element ['#id'];
  }
  // Add element's #type and #name as class to aid with JS/CSS selectors.
  $attributes ['class'] = array('form-item');
  if (!empty($element ['#type'])) {
    $attributes ['class'][] = 'form-type-' . strtr($element ['#type'], '_', '-');
  }
  if (!empty($element ['#name'])) {
    $attributes ['class'][] = 'form-item-' . strtr($element ['#name'], array(' ' => '-', '_' => '-', '[' => '-', ']' => ''));
  }
  // Add a class for disabled elements to facilitate cross-browser styling.
  if (!empty($element ['#attributes']['disabled'])) {
    $attributes ['class'][] = 'form-disabled';
  }
  /*****************************Added form-group style from bootstrap.css *****************************/
  $attributes ['class'][] = 'form-group';
  
  $output = '<div' . drupal_attributes($attributes) . '>' . "\n";

  // If #title is not set, we don't display any label or required marker.
  if (!isset($element ['#title'])) {
    $element ['#title_display'] = 'none';
  }
  $prefix = isset($element ['#field_prefix']) ? '<span class="field-prefix">' . $element ['#field_prefix'] . '</span> ' : '';
  $suffix = isset($element ['#field_suffix']) ? ' <span class="field-suffix">' . $element ['#field_suffix'] . '</span>' : '';

  switch ($element ['#title_display']) {
    case 'before':
    case 'invisible':
      $output .= ' ' . theme('form_element_label', $variables);
      $output .= ' ' . $prefix . $element ['#children'] . $suffix . "\n";
      break;

    case 'after':
      $output .= ' ' . $prefix . $element ['#children'] . $suffix;
      $output .= ' ' . theme('form_element_label', $variables) . "\n";
      break;

    case 'none':
    case 'attribute':
      // Output no label and no required marker, only the children.
      $output .= ' ' . $prefix . $element ['#children'] . $suffix . "\n";
      break;
  }

  if (!empty($element ['#description'])) {
  /*****************************added 'small' css to description *****************************/
    $output .= '<div class="description small">' . $element ['#description'] . "</div>\n";
  }

  $output .= "</div>\n";

  return $output;
}

function bartik_sub_textfield($variables) {
  $element = $variables ['element'];
  $element ['#attributes']['type'] = 'text';
  element_set_attributes($element, array('id', 'name', 'value', 'size', 'maxlength'));
  /*****************************Added 'form-control' class from bootstrap.css *****************************/
  _form_set_class($element, array('form-text','form-control'));

  $extra = '';
  if ($element ['#autocomplete_path'] && drupal_valid_path($element ['#autocomplete_path'])) {
    drupal_add_library('system', 'drupal.autocomplete');
    $element ['#attributes']['class'][] = 'form-autocomplete';

    $attributes = array();
    $attributes ['type'] = 'hidden';
    $attributes ['id'] = $element ['#attributes']['id'] . '-autocomplete';
    $attributes ['value'] = url($element ['#autocomplete_path'], array('absolute' => TRUE));
    $attributes ['disabled'] = 'disabled';
    $attributes ['class'][] = 'autocomplete';
    $extra = '<input' . drupal_attributes($attributes) . ' />';
  }

  $output = '<input' . drupal_attributes($element ['#attributes']) . ' />';

  return $output . $extra;
}

function bartik_sub_textarea($variables) {
  $element = $variables ['element'];
  element_set_attributes($element, array('id', 'name', 'cols', 'rows'));
  /*****************************Added 'form-control' class from bootstrap.css *****************************/
  _form_set_class($element, array('form-textarea','form-control'));

  $wrapper_attributes = array(
    'class' => array('form-textarea-wrapper'),
  );

  // Add resizable behavior.
  if (!empty($element ['#resizable'])) {
    drupal_add_library('system', 'drupal.textarea');
    $wrapper_attributes ['class'][] = 'resizable';
  }

  $output = '<div' . drupal_attributes($wrapper_attributes) . '>';
  $output .= '<textarea' . drupal_attributes($element ['#attributes']) . '>' . check_plain($element ['#value']) . '</textarea>';
  $output .= '</div>';
  return $output;
}



function bartik_sub_select($variables) {
  $element = $variables ['element'];
  element_set_attributes($element, array('id', 'name', 'size'));
  /*****************************Added 'form-control' class from bootstrap.css *****************************/
  _form_set_class($element, array('form-select','form-control'));

  return '<select' . drupal_attributes($element ['#attributes']) . '>' . form_select_options($element) . '</select>';
}

function bartik_sub_button($variables) {
  $element = $variables ['element'];
  $element ['#attributes']['type'] = 'submit';
  element_set_attributes($element, array('id', 'name', 'value'));

  $element ['#attributes']['class'][] = 'form-' . $element ['#button_type'];
  if (!empty($element ['#attributes']['disabled'])) {
    $element ['#attributes']['class'][] = 'form-button-disabled';
  }
  /*****************************Added 'btn','btn-primary','col-md-6' classes from bootstrap.css *****************************/
  $element ['#attributes']['class'][] = 'btn';
  $element ['#attributes']['class'][] = 'btn-primary';
  $element ['#attributes']['class'][] = 'col-md-6';

  return '<input' . drupal_attributes($element ['#attributes']) . ' />';
}



