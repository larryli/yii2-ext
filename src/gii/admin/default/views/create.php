<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator extras\gii\admin\Generator */

$modelClass = empty($generator->adminModelClass) ? $generator->modelClass : $generator->adminModelClass;
$modelName = empty($generator->modelName) ? Inflector::camel2words(StringHelper::basename($modelClass)) : $generator->modelName;

echo "<?php\n";
?>

/* @var $this yii\web\View */
/* @var $model <?= ltrim($modelClass, '\\') ?> */

$this->title = <?= $generator->generateString('增加' . $modelName) ?>;
$this->params['description'] = <?= $generator->generateString('增加新的' . $modelName . '数据') ?>;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString($modelName) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = '增加';
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($modelClass)) ?>-create box">
    <div class="box-header with-border">
        <h3 class="box-title">新<?= $modelName ?></h3>
    </div>
    <?= "<?= " ?>$this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
