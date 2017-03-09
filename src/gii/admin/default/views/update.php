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

/* @var $this yii\web\View */
/* @var $model <?= ltrim($modelClass, '\\') ?> */

$this->title = <?= $generator->generateString('编辑{modelName}：', ['modelName' => $modelName]) ?> . StringHelper::truncate($model-><?= $generator->getNameAttribute() ?>, 20);
$this->params['description'] = <?= $generator->generateString('修改' . $modelName . '数据') ?>;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString($modelName) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => StringHelper::truncate($model-><?= $generator->getNameAttribute() ?>, 20), 'url' => ['view', <?= $urlParams ?>]];
$this->params['breadcrumbs'][] = <?= $generator->generateString('编辑') ?>;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($modelClass)) ?>-update box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= "<?= " ?>$model-><?= $generator->getNameAttribute() ?>?></h3>
        <div class="box-tools pull-right-not-xs">
            <?= "<?= " ?>Html::a(<?= $generator->generateString('查看') ?>, ['view', <?= $urlParams ?>], ['class' => 'btn btn-info btn-sm', 'fa' => 'eye']) ?>
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
    <?= "<?= " ?>$this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
