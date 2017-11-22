<?php
/**
 * @file Contains \Drupal\custom_alias\AliasManager
 */

namespace Drupal\custom_alias;


use Drupal\Core\Path\AliasStorage;
use Drupal\pathauto\AliasCleaner;




/**
 * Service to save/update custom path alias
 */
class AliasManager {


    protected $aliasStorage;
    protected $aliasCleaner;



    /**
     * AliasManager constructor.
     * @param AliasStorage $aliasStorage
     * @param AliasCleaner $aliasCleaner
     */
    public function __construct(AliasStorage $aliasStorage, AliasCleaner $aliasCleaner) {
        $this->aliasStorage  = $aliasStorage;
        $this->aliasCleaner  = $aliasCleaner;
    }


    /**
     * Process the path and save it
     * @param object $node
     * @param string $field
     * @param array $settings
     * @param string $action
     * @param string $langcode
     */
    public function processPath($node, $field, $settings, $action, $langcode = 'en'){

        $nid = $node->id();

        // Clean title
        $title = $this->aliasCleaner->cleanString($node->label());

        // Set default path
        $path = '/';

        // Search $options for a match on $field and set path to value
        foreach($settings as $key => $value){

            if($field == $key){
                $path = $value;
            }

        }

        // Save or update
        switch($action){

            case 'save':

                // If an existing alias exists
                if($this->aliasStorage->lookupPathSource($path.$title,$langcode)){

                    // Append integer to title
                    $title = $this->loopAlias($path, $title, $langcode);

                }

                // Save path alias
                $this->aliasStorage->save('/node/'.$nid, $path.$title, $langcode);

                break;

            case 'update':

                // Load current alias
                $alias = $this->aliasStorage->load(array('source' => '/node/'.$nid));

                // If title has changed or current alias doesnt match $options alias
                if($node->original->label() != $node->label() || $alias['alias'] != $path.$title){

                    // If an existing alias exists
                    if($this->aliasStorage->lookupPathSource($path.$title,$langcode)){

                        // Append integer to title
                        $title = $this->loopAlias($path, $title, $langcode);

                    }

                    // Update existing path alias by passing pid
                    // Redirect module will auto-create a redirect if enabled
                    $this->aliasStorage->save('/node/'.$nid, $path.$title, $langcode, $alias['pid']);

                }

                break;

        }

    }


    /**
     * Loop over integer and return the next available appended to title
     * @param string $path
     * @param string $title
     * @param string $langcode
     * @return string new appended title
     */
    private function loopAlias($path, $title, $langcode){

        for ($i = 0; $i <= 100; $i++) {

            // Append when there is no matching path
            if(!$this->aliasStorage->lookupPathSource($path.$title.'-'.$i,$langcode)){

                $title = $title.'-'.$i;

                return $title;
                break;

            }

        }

    }


}
