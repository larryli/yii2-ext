<?php

namespace larryli\yii\extras\traits;

use Throwable;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;

/**
 * ActiveRecord 关联记录操作
 *
 * @method bool isAttributeSafe(string $attribute)
 * @method ActiveQuery getRelation(string $name, $throwException = true)
 * @method string getScenario()
 */
trait ActiveRelatedTrait
{
    /**
     * @var array
     */
    private $_deleteRelated;

    /**
     * @param $attribute
     * @param $value
     */
    public function setRelatedScenario($attribute, $value)
    {
        if ($this->isAttributeSafe($attribute)) {
            $related = $this->$attribute;
            if (is_array($related)) {
                /* @var ActiveRecord[] $related */
                foreach ($related as $model) {
                    $model->setScenario($value);
                }
            } else {
                /* @var ActiveRecord $related */
                $related->setScenario($value);
            }
        }
    }

    /**
     * @param string $attribute 属性，子模型数据必须包含至少一个
     * @param array $data POST 表单数据
     * @param string $formName 表单名，默认 null 将使用子模型的 Model::formName()
     * @param bool $canModify 子模型数组数量是否可以增减修改
     * @param string $key
     * @param callback $callback 回调处理子模型载入 POST 表单数据，取代 Model::load()
     * @return bool
     * @throws InvalidConfigException
     */
    public function loadRelated($attribute, $data, $formName = null, $canModify = true, $key = 'id', $callback = null)
    {
        // 不载入此属性
        if (!$this->isAttributeSafe($attribute)) {
            return true;
        }

        $related = $this->$attribute;
        if (is_array($related)) {
            /* @var ActiveRecord[] $related */
            $first = reset($related);
            if ($first === false) {
                $relation = $this->getRelation($attribute);
                $className = $relation->modelClass;
                $first = new $className;
            } else {
                $className = get_class($first);
            }
            $first->setScenario($this->getScenario());
            $scope = $formName === null ? $first->formName() : $formName;

            if (!empty($scope) && isset($data[$scope])) {
                $post = $data[$scope];
                if ($post && is_array($post)) {
                    $related = ArrayHelper::index($related, $key);
                    $models = [];
                    foreach ($post as $index => $attributes) {
                        $model = null;

                        if (isset($attributes[$key]) && isset($related[$attributes[$key]])) {
                            $id = $attributes[$key];
                            $model = $related[$id];
                            unset($related[$id]);
                        } elseif ($canModify) {
                            $model = new $className;
                        }

                        if (!empty($model)) {
                            $model->setScenario($this->getScenario());
                            if (is_callable($callback)) {
                                call_user_func($callback, $model, [$scope => $attributes], $index);
                            } else {
                                $model->load($attributes, '');
                            }
                            $models[] = $model;
                        }
                    }

                    if ($canModify) {
                        $this->_deleteRelated[$attribute] = $related;
                    } else {
                        $models = array_merge($models, $related);
                    }

                    $this->$attribute = $models;
                    return true;
                }
            } elseif ($canModify) {
                $this->_deleteRelated[$attribute] = ArrayHelper::index($related, $key);
                $this->$attribute = [];
                return true;
            }
        } else {
            /* @var ActiveRecord $related */
            $related->setScenario($this->getScenario());
            $scope = $formName === null ? $related->formName() : $formName;

            if (!empty($scope) && isset($data[$scope])) {
                $post = $data[$scope];
                if ($post && is_array($post)) {
                    if (is_callable($callback)) {
                        call_user_func($callback, $related, [$scope => $post], false);
                    } else {
                        $related->load($post, '');
                    }
                    $this->$attribute = $related;
                    return true;
                }
            } elseif ($canModify) {
                $this->_deleteRelated[$attribute] = [ArrayHelper::getValue($related, $key) => $related];
                $this->$attribute = null;
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $attribute
     * @return array
     */
    public function getDeleteRelated($attribute)
    {
        if ($attribute === null) {
            return $this->_deleteRelated === null ? [] : $this->_deleteRelated;
        } else {
            return isset($this->_deleteRelated[$attribute]) ? $this->_deleteRelated[$attribute] : [];
        }
    }

    /**
     * @param string $attribute
     * @param array $params
     * @return bool
     */
    public function validateRelated($attribute, $params)
    {
        $related = $this->$attribute;
        $valid = true;
        $required = isset($params['required']) && $params['required'];
        $raise = isset($params['raise']) ? $params['raise'] : true;
        $attributeNames = isset($params['attributeNames']) ? $params['attributeNames'] : null;
        if (is_array($related)) {
            /* @var ActiveRecord[] $related */
            if ($required && count($related) <= 0) {
                $this->_addParamError($this, $attribute, $params, 'requiredMessage', '列表不能为空。');
                return false;
            }
            if (isset($params['unique'])) {
                $unique = $params['unique'];
                $counts = array_count_values(array_filter(ArrayHelper::getColumn($related, $unique), function ($v) {
                    return $v !== null;
                }));
            }
            foreach ($related as $model) {
                if ($model->validate($attributeNames)) {
                    if (isset($unique) && isset($counts) && @$counts[$model->$unique] > 1) {
                        $valid = false;
                        $message = $this->_addParamError($model, $unique, $params, 'uniqueMessage', '列表中不允许重复的项目。');
                        if ($raise) {
                            $this->_addParamError($this, $attribute, $params, 'raiseMessage', '列表项目验证错误，请修改列表中的项目。', $message);
                        }
                    }
                } elseif ($raise) {
                    $valid = false;
                    $this->_addParamError($this, $attribute, $params, 'raiseMessage', '列表项目验证错误，请修改列表中的项目。', $model->getFirstErrors());
                }
            }
        } else {
            /* @var ActiveRecord $related */
            if ($required && empty($related)) {
                $this->_addParamError($this, $attribute, $params, 'requiredMessage', '项目不能为空。');
                return false;
            }
            if ($raise && !$related->validate($attributeNames)) {
                $valid = false;
                $this->_addParamError($this, $attribute, $params, 'raiseMessage', '项目验证错误，请修改项目。', $related->getFirstErrors());
            }
        }
        return $valid;
    }

    /**
     * @param string $attribute
     * @param string $relation
     */
    public function linkRelated($attribute, $relation)
    {
        $related = $this->$attribute;
        if (is_array($related)) {
            /* @var ActiveRecord[] $related */
            foreach ($related as $model) {
                /** @var ActiveRecord $this */
                $model->link($relation, $this);
            }
        } else {
            /* @var ActiveRecord $related */
            /** @var ActiveRecord $this */
            $related->link($relation, $this);
        }
    }

    /**
     * @param string $attribute
     * @param string $relation
     * @param string $className
     * @param string $key
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function saveRelated($attribute, $relation, $className = null, $key = 'id')
    {
        $deletes = $this->getDeleteRelated($attribute);
        /* @var ActiveRecord $model */
        if (class_exists($className) && method_exists($className, 'deleteAll')) {
            call_user_func([$className, 'deleteAll'], ['in', $key, $deletes]);
        } else {
            foreach ($deletes as $model) {
                $model->delete();
            }
        }
        /* @var ActiveRecord $this */
        $related = $this->$attribute;
        if (is_array($related)) {
            foreach ($related as $model) {
                /* @var ActiveRecord[] $related */
                if ($model->isNewRecord) {
                    /** @var ActiveRecord $this */
                    $model->link($relation, $this);
                } else {
                    $model->save(false);
                }
            }
        } else {
            /* @var ActiveRecord $related */
            if ($related->isNewRecord) {
                /** @var ActiveRecord $this */
                $related->link($relation, $this);
            } else {
                $related->save(false);
            }
        }
    }

    /**
     * @param string $attribute
     * @param string $className
     * @param string $key
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function deleteRelated($attribute, $className = null, $key = 'id')
    {
        if (class_exists($className) && method_exists($className, 'deleteAll')) {
            $deletes = ArrayHelper::getColumn($this->$attribute, $key, []);
            call_user_func([$className, 'deleteAll'], ['in', $key, $deletes]);
        } else {
            $related = $this->$attribute;
            if (is_array($related)) {
                /* @var ActiveRecord[] $related */
                foreach ($related as $model) {
                    $model->delete();
                }
            } else {
                /* @var ActiveRecord $related */
                $related->delete();
            }
        }
    }

    /**
     * @param $attribute
     * @return ActiveRecord[]|ActiveRecord
     */
    public function getRelated($attribute)
    {
        $related = $this->$attribute;
        if ($related !== null && !empty($related)) {
            return $related;
        }
        /* @var ActiveQuery $relation */
        $relation = $this->getRelation($attribute);
        $className = $relation->modelClass;
        if ($relation->multiple) {
            return [new $className];
        }
        return new $className;
    }

    /**
     * @param ActiveRecord|ActiveRelatedTrait $model
     * @param string $attribute
     * @param array $params
     * @param string $key
     * @param string $message
     * @param string $more
     * @return string
     */
    private function _addParamError($model, $attribute, $params, $key, $message = '发生错误。', $more = '')
    {
        if (is_array($more)) {
            $more = implode('', $more);
        }
        $message = (isset($params[$key]) ? $params[$key] : $message) . $more;
        $model->addError($attribute, $message);
        return $message;
    }
}
