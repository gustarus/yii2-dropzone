<?php
/**
 * Created by PhpStorm.
 * User: supreme
 * Date: 16.04.14
 * Time: 0:59
 */

namespace gustarus\dropzone\assets;

use yii\web\AssetBundle;

class Asset extends AssetBundle {

  /**
   * @inheritdoc
   */
  public $sourcePath = '@gustarus/dropzone/public';

  /**
   * @inheritdoc
   */
  public $js = [
    'js/dropzone.js',
  ];

  /**
   * @inheritdoc
   */
  public $css = [
    'css/defaults.css',
    'css/single.css',
    'css/multiple.css',
  ];

  /**
   * @inheritdoc
   */
  public $depends = [
    'yii\web\JqueryAsset',
  ];
}
