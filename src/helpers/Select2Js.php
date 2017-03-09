<?php

namespace extras\helpers;

use yii\web\JsExpression;

/**
 * Class Js
 */
class Select2Js
{
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
