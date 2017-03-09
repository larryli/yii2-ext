<?php

namespace larryli\yii\extras\assets;

use yii\web\AssetBundle;

/**
 * Font Awesome 图标
 */
class FontAwesomeAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@bower/font-awesome';
    /**
     * @var array
     */
    public $css = ['css/font-awesome.min.css'];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->publishOptions['only'] = ['css/*', 'fonts/*'];
        parent::init();
    }
}
