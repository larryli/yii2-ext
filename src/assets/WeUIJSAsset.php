<?php

namespace extras\assets;

use yii\web\AssetBundle;

/**
 * Wechat UI asset bundle.
 */
class WeUIJSAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@npm/weui.js/dist';
    /**
     * @var array
     */
    public $js = ['weui.js'];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->publishOptions['except'] = ['example'];
        parent::init();
    }
}
