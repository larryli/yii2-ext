<?php

namespace LarryLi\Yii\Extras\Helpers;

use kartik\select2\Select2;
use yii\base\Model;
use yii\web\JsExpression;

/**
 * Class Js
 */
class Select2Js
{
    /**
     * @param Model $model
     * @param string $attribute
     * @param string $url
     * @param string $text
     * @return array
     */
    public static function ajaxSelect2($model, $attribute, $url, $text)
    {
        return [
            'model' => $model,
            'attribute' => $attribute,
            'initValueText' => $text,
            'theme' => Select2::THEME_DEFAULT,
            'pluginOptions' => [
                'placeholder' => '',
                'allowClear' => true,
                'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    'delay' => 250,
                    'data' => static::data(),
                    'processResults' => static::processResults(),
                    'cache' => true,
                ],
                'templateResult' => static::templateResult(),
                'templateSelection' => static::templateSelection(),
            ],
        ];
    }

    /**
     * @param string $term
     * @param string $page
     * @return JsExpression
     */
    public static function data($term = 'text', $page = 'page')
    {
        return new JsExpression('function(params){return{' . $term . ':params.term,' . $page . ':params.page}}');
    }

    /**
     * @param string $results
     * @param string $meta
     * @return JsExpression
     */
    public static function processResults($results = 'results', $meta = 'meta')
    {
        return new JsExpression('function(data,params){return{results:data.'
            . $results . ',pagination:{more:data.'
            . $meta . '.currentPage<data.'
            . $meta . '.pageCount}}}');
    }

    /**
     * 显示为 Html
     * @return JsExpression
     */
    public static function escapeMarkup()
    {
        return new JsExpression('function(markup){return markup}');
    }

    /**
     * @param string $term
     * @param string $class
     * @return JsExpression
     */
    public static function templateResult($term = 'text', $class = 'nowrap')
    {
        return new JsExpression('function(data,container){if(data.loading)return data.text;$(container).addClass("' .
            $class . '");return data.' . $term . '}');
    }

    /**
     * @param string $term
     * @return JsExpression
     */
    public static function templateSelection($term = 'text')
    {
        return new JsExpression('function(data){return data.' . $term . '||data.text}');
    }
}
