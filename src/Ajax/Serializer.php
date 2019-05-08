<?php

namespace LarryLi\Yii\Extras\Ajax;

use Yii;
use yii\base\Arrayable;
use yii\base\Component;
use yii\base\Model;
use yii\data\DataProviderInterface;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\Request;
use yii\web\Response;

class Serializer extends Component
{
    /**
     * @var int
     */
    public $errorStatus = 422;
    /**
     * @var string
     */
    public $errorName = 'Data Validation Failed.';
    /**
     * @var string the name of the envelope (e.g. `results`) for returning the resource objects in a collection.
     * This is used when serving a resource collection. When this is set and pagination is enabled, the serializer
     * will return a collection in the following format:
     *
     * ```php
     * [
     *     'results' => [...],  // assuming collectionEnvelope is "results"
     *     'meta' => {  // meta information as returned by Pagination::toArray()
     *         'totalCount' => 100,
     *         'pageCount' => 5,
     *         'currentPage' => 1,
     *         'perPage' => 20,
     *     },
     * ]
     * ```
     *
     * If this property is not set, the resource arrays will be directly returned without using envelope.
     * The pagination information as shown in `_meta` can be accessed from the response HTTP headers.
     */
    public $collectionEnvelope = 'results';
    /**
     * @var string the name of the envelope (e.g. `_meta`) for returning the pagination object.
     * It takes effect only, if `collectionEnvelope` is set.
     * @since 2.0.4
     */
    public $metaEnvelope = 'meta';
    /**
     * @var Request the current request. If not set, the `request` application component will be used.
     */
    public $request;
    /**
     * @var Response the response to be sent. If not set, the `response` application component will be used.
     */
    public $response;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->request === null) {
            $this->request = Yii::$app->getRequest();
        }
        if ($this->response === null) {
            $this->response = Yii::$app->getResponse();
        }
    }

    /**
     * Serializes the given data into a format that can be easily turned into other formats.
     * This method mainly converts the objects of recognized types into array representation.
     * It will not do conversion for unknown object types or non-object data.
     * The default implementation will handle [[Model]] and [[DataProviderInterface]].
     * You may override this method to support more object types.
     * @param mixed $data the data to be serialized.
     * @return mixed the converted data.
     */
    public function serialize($data)
    {
        if ($data instanceof Model && $data->hasErrors()) {
            return $this->serializeModelErrors($data);
        } elseif ($data instanceof Arrayable) {
            return $this->serializeModel($data);
        } elseif ($data instanceof DataProviderInterface) {
            return $this->serializeDataProvider($data);
        } else {
            return $data;
        }
    }

    /**
     * Serializes a data provider.
     * @param DataProviderInterface $dataProvider
     * @return array the array representation of the data provider.
     */
    protected function serializeDataProvider($dataProvider)
    {
        $models = [];
        foreach ($dataProvider->getModels() as $i => $model) {
            if ($model instanceof Arrayable) {
                $models[$i] = $model->toArray();
            } elseif (is_array($model)) {
                $models[$i] = ArrayHelper::toArray($model);
            }
        }
        $result = [
            $this->collectionEnvelope => $models,
        ];
        if (($pagination = $dataProvider->getPagination()) !== false) {
            $result[$this->metaEnvelope] = [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->getPageCount(),
                'currentPage' => $pagination->getPage() + 1,
                'perPage' => $pagination->getPageSize(),
            ];
        }
        return $result;
    }

    /**
     * Serializes a model object.
     * @param Arrayable $model
     * @return array the array representation of the model
     */
    protected function serializeModel($model)
    {
        return $model->toArray();
    }

    /**
     * Serializes the validation errors in a model.
     * @param Model $model
     * @return array the array representation of the errors
     */
    protected function serializeModelErrors($model)
    {
        $this->response->setStatusCode($this->errorStatus, $this->errorName);
        $result = [
            'name' => $this->errorName,
            'message' => implode("\n", $model->getFirstErrors()),
            'status' => $this->errorStatus,
            'errors' => [],
        ];
        foreach ($model->getFirstErrors() as $attribute => $errors) {
            $result['errors'][] = [
                'id' => Html::getInputId($model, $attribute),
                'name' => Html::getInputName($model, $attribute),
                'field' => $attribute,
                'message' => $errors,
            ];
        }
        return $result;
    }
}
