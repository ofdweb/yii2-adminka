<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for the Twitter bootstrap css files.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BootstrapAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'app-assets/css/vendors.css',
        'app-assets/css/bootstrap.css',
        'app-assets/css/bootstrap-extended.css',
    ];
    public $js = [
        '//unpkg.com/popper.js/dist/umd/popper.min.js',
        'app-assets/vendors/js/vendors.min.js',
        'app-assets/js/core/libraries/bootstrap.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset'
    ];
}
