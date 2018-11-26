<?php

namespace Drupal\image_styles_precomputer\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Image\ImageFactory;
use Drush\Commands\DrushCommands;

/**
 * Drush commands to precompute image styles.
 */
class ImageStylesPrecomputerCommands extends DrushCommands {

  /**
   * The image styles.
   *
   * @var \Drupal\image\Entity\ImageStyle[]
   */
  protected $imageStyles;

  /**
   * File storage.
   *
   * @var \Drupal\file\FileStorage
   */
  protected $fileStorage;

  /**
   * The image factory.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Image\ImageFactory $image_factory
   *   The image factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ImageFactory $image_factory) {
    $this->imageStyles = $entity_type_manager->getStorage('image_style')->loadMultiple();
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->imageFactory = $image_factory;
  }

  /**
   * Drush command to precompute image styles.
   *
   * @command image-styles-precomuter:precompute-image-styles
   *
   * @usage drush image-styles-precomuter:precompute-image-styles
   *   Procomputes image styles for every image stored.
   *
   * @aliases isp-pis
   */
  public function precomputeImageStyles() {
    $images = $this->fileStorage->loadByProperties(['filemime' => ['image/png', 'image/jpg', 'image/jpeg', 'image/gif']]);
    /** @var \Drupal\file\Entity\File $image_entity */
    foreach ($images as $image_entity) {
      $image_uri = $image_entity->getFileUri();
      $image = $this->imageFactory->get($image_uri);
      if ($image->isValid()) {
        foreach ($this->imageStyles as $image_style) {
          $derivative_uri = $image_style->buildUri($image_uri);
          if (!file_exists($derivative_uri)) {
            $image_style->createDerivative($image_uri, $derivative_uri);
          }
        }
      }
    }
  }

}
