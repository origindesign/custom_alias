<?php
/**
 * @file Contains \Drupal\custom_alias\AliasManager
 */

namespace Drupal\custom_alias;


use Drupal\pathauto\AliasStorageHelper;
use Drupal\path_alias\AliasRepositoryInterface;
use Drupal\pathauto\AliasCleaner;




/**
 * Service to save/update custom path alias
 */
class AliasManager {


  protected $aliasStorage;
  protected $aliasRepository;
  protected $aliasCleaner;


  /**
   * AliasManager constructor.
   * @param AliasStorageHelper $AliasStorageHelper
   * @param AliasRepositoryInterface $alias_repository
   * @param AliasCleaner $aliasCleaner
   */
  public function __construct(AliasStorageHelper $AliasStorageHelper, AliasRepositoryInterface $alias_repository, AliasCleaner $aliasCleaner) {
    $this->aliasStorage  = $AliasStorageHelper;
    $this->aliasRepository = $alias_repository;
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
        if($this->aliasRepository->lookupByAlias($path.$title,$langcode)){

          // Append integer to title
          $title = $this->loopAlias($path, $title, $langcode);

        }

        // Save path alias
        $path = [
          'source' => '/node/'.$nid,
          'alias' => $path.$title,
          'language' => $langcode,
        ];
        $this->aliasStorage->save($path);

        break;

      case 'update':

        // Load current alias
        $alias = $this->aliasStorage->loadBySource('/node/'.$nid, $langcode);

        // If title has changed or current alias doesnt match $options alias
        if($node->original->label() != $node->label() || $alias['alias'] != $path.$title){

          // If an existing alias exists
          if($this->aliasRepository->lookupByAlias($path.$title,$langcode)){

            // Append integer to title
            $title = $this->loopAlias($path, $title, $langcode);

          }

          // Update existing path alias by passing pid
          // Redirect module will auto-create a redirect if enabled
          // Save path alias
          $path = [
            'source' => '/node/'.$nid,
            'alias' => $path.$title,
            'language' => $langcode,
          ];
          $existing_alias = [
            'pid' => $alias['pid']
          ];
          $this->aliasStorage->save($path, $existing_alias);

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
      if(!$this->aliasRepository->lookupByAlias($path.$title.'-'.$i,$langcode)){

        $title = $title.'-'.$i;

        return $title;
        break;

      }

    }

  }


}
