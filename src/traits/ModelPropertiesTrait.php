<?php

namespace larryli\yii\extras\traits;

use Closure;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Trait ModelPropertiesTrait
 *
 * @method boolean isRelationPopulated(string $name)
 * @method ActiveQueryInterface getRelation(string $name, $throwException = true)
 */
trait ModelPropertiesTrait
{
    /**
     * @var array
     */
    private static $_staticProperties = [];
    /**
     * @var array
     */
    private $_properties = [];

    /**
     * @param string $name
     * @param callback $callback
     * @return mixed
     */
    public static function staticGetter($name, $callback = null)
    {
        if (isset(self::$_staticProperties[$name]) || array_key_exists($name, self::$_staticProperties)) {
            return self::$_staticProperties[$name];
        }
        return self::$_staticProperties[$name] = ($callback instanceof Closure) ? $callback() : (method_exists(static::class, $name) ? static::$name() : null);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public static function staticSetter($name, $value)
    {
        self::$_staticProperties[$name] = $value;
    }

    /**
     * @param string $name
     * @param string $class
     * @param string $from
     * @param string $to
     * @param string|array|Closure $filter
     * @param string $group
     * @return array
     */
    public static function staticItems($name, $class, $from, $to, $filter = null, $group = null)
    {
        return self::staticGetter($name, function () use ($class, $from, $to, $filter, $group) {
            /* @var ActiveRecord $class */
            $query = $class::find();
            if ($filter instanceof Closure) {
                call_user_func($filter, $query);
            } elseif ($filter !== null) {
                $query->andWhere($filter);
            }
            $select = [$from, $to];
            if ($group !== null) {
                $select[] = $group;
            }
            return ArrayHelper::map($query->select($select)->asArray()->all(), $from, $to, $group);
        });
    }

    /**
     * @param string $name
     * @param callback $callback
     * @return mixed
     */
    public function getter($name, $callback = null)
    {
        if (isset($this->_properties[$name]) || array_key_exists($name, $this->_properties)) {
            return $this->_properties[$name];
        }
        if ($callback instanceof Closure) {
            return $this->_properties[$name] = call_user_func($callback);
        } elseif (method_exists($this, $name)) {
            return $this->_properties[$name] = call_user_func([$this, $name]);
        }
        return $this->_properties[$name] = ($callback instanceof Closure) ? $callback() : (method_exists($this, $name) ? $this->$name() : null);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setter($name, $value)
    {
        $this->_properties[$name] = $value;
    }

    /**
     * @param string $name
     * @param string $prefix
     * @return bool
     */
    public function existsRelation($name, $prefix = 'exists')
    {
        $exists = $prefix . ucfirst($name);
        return $this->getter($exists, function () use ($name)  {
            if ($this->isRelationPopulated($name)) {
                return !empty($this->$name);
            } elseif (($relation = $this->getRelation($name, false)) !== null) {
                /* @var $relation ActiveQueryInterface */
                return $relation->limit(1)->exists();
            }
            return false;
        });
    }

    /**
     * @param string $name
     * @param string $prefix
     * @return integer
     */
    public function countRelation($name, $prefix = 'count')
    {
        $count = $prefix . ucfirst($name);
        return $this->getter($count, function () use ($name)  {
            if ($this->isRelationPopulated($name)) {
                return count($this->$name);
            } elseif (($relation = $this->getRelation($name, false)) !== null) {
                /* @var $relation ActiveQueryInterface */
                return $relation->count();
            }
            return 0;
        });
    }
}
