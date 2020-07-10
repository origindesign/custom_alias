````
<?php


use Drupal\node\NodeInterface;




/**
 * Setup key => value pairs to match field value to path alias
 */
function getOptions(){

    $options = array(

        'service' => array(
            'Accommodation' => '/services/accommodation/',
            'Business' => '/business-listing/business/',
            'Dining & Nightlife' => '/things-to-do/dining-nightlife/'
        )

    );

    return $options;

}


/**
 * Set path alias on insert
 * @param NodeInterface $node
 */
function custom_alias_node_insert(NodeInterface $node) {

    // Set options array
    $options = getOptions();

    // Setup per content type
    switch($node->getType()){

        case 'service':

            // Get service taxonomy
            $field = $node->get('field_category')->view()[0]['#title'];

            // Set options
            $settings = $options['service'];

            break;

    }

    // Save path alias
    $process = \Drupal::service('custom_alias.alias_manager')->processPath($node, $field, $settings, 'save');

}


/**
 * Set path alias on update
 * @param NodeInterface $node
 */
function custom_alias_node_update(NodeInterface $node) {

    // Set options array
    $options = getOptions();

    // Setup per content type
    switch($node->getType()){

        case 'service':

            // Get service taxonomy
            $field = $node->get('field_category')->view()[0]['#title'];

            // Set options
            $settings = $options['service'];

            break;

    }

    // Save path alias
    $process = \Drupal::service('custom_alias.alias_manager')->processPath($node, $field, $settings, 'update');

}


/**
 * Disable Url Alias field
 * @param $form
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 * @param $form_id
 */
function custom_alias_form_alter(&$form, \Drupal\Core\Form\FormStateInterface $form_state, $form_id) {

    switch ($form_id) {
        case 'node_service_form':
        case 'node_service_edit_form':
            $form['path']['#disabled'] = 'disabled';
            break;
    }

}
````