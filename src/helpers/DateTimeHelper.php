<?php

namespace extras\helpers;

/**
 * Class DateTimeHelper
 */
class DateTimeHelper
{
    /**
     * 时间段比较
     *
     * ```php
     * $query->andFilterWhere(DateTimeHelper::betweenDateRange('created_at', $this->created_at);
     * ```
     *
     * @param string $attribute
     * @param string $value
     * @return array
     */
    public static function betweenDateRange($attribute, $value)
    {
        list($start, $end) = static::dateRange($value);
        if ($start !== false && $end !== false) {
            return ['between', $attribute, $start, $end];
        }
        return [$attribute => ''];
    }

    /**
     * @param string $value
     * @return array
     */
    public static function dateRange($value)
    {
        if (!is_null($value) && strpos($value, ' - ') !== false) {
            list($start, $end) = explode(' - ', $value);
            return [strtotime($start . ' midnight'), strtotime($end . ' tomorrow')];
        }
        return [false, false];
    }
}
