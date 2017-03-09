<?php

namespace extras\assets;

use yii\web\AssetBundle;

/**
 * Wechat UI asset bundle.
 */
class WeUIAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@bower/weui/dist/style';
    /**
     * @var array
     */
    public $css = ['weui.css'];
}
