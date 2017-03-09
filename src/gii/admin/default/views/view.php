<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator extras\gii\admin\Generator */

$urlParams = $generator->generateUrlParams();
$modelClass = empty($generator->adminModelClass) ? $generator->modelClass : $generator->adminModelClass;
$modelName = empty($generator->modelName) ? Inflector::camel2words(StringHelper::basename($modelClass)) : $generator->modelName;

echo "<?php\n";
?>

use app\helpers\Html;
use yii\helpers\StringHelper;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($modelClass, '\\') ?> */

$this->title = <?= $generator->generateString('查看{modelName}：', ['modelName' => $modelName]) ?> . $model-><?= $generator->getNameAttribute() ?>;
$this->params['description'] = <?= $generator->generateString('查看' . $modelName . '数据') ?>;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString($modelName) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = StringHelper::truncate($model-><?= $generator->getNameAttribute() ?>, 40);
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($modelClass)) ?>-view box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= "<?= " ?>$model-><?= $generator->getNameAttribute() ?> ?></h3>
        <div class="box-tools pull-right-not-xs">
        <?= "<?= " ?>Html::a(<?= $generator->generateString('编辑') ?>, ['update', <?= $urlParams ?>], ['class' => 'btn btn-primary btn-sm', 'fa' => 'pencil']) ?>
        <?= "<?= " ?>Html::a(<?= $generator->generateString('删除') ?>, ['delete', <?= $urlParams ?>], [
            'class' => 'btn btn-danger btn-sm',
            'fa' => 'trash',
            'data' => [
                'confirm' => <?= $generator->generateString('确定要删除此' . $modelName . '么？') ?>,
                'method' => 'post',
            ],
        ]) ?>
        </div>
    </div>
    <?= "<?= " ?>DetailView::widget([
        'model' => $model,
        'attributes' => [
<?php
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        echo "            '" . $name . "',\n";
    }
} else {
    foreach ($generator->getTableSchema()->columns as $column) {
        $format = $generator->generateColumnFormat($column);
        echo "            '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
    }
}
?>
        ],
    ]) ?>
</div>
