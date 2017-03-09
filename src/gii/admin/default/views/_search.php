<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator extras\gii\admin\Generator */

$modelClass = empty($generator->adminModelClass) ? $generator->modelClass : $generator->adminModelClass;

echo "<?php\n";
?>

use app\admin\widgets\ActiveForm;
use app\helpers\Html;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->searchModelClass, '\\') ?> */
/* @var $form yii\bootstrap\ActiveForm */

?>
<div class="<?= Inflector::camel2id(StringHelper::basename($modelClass)) ?>-search hidden-xs hidden-sm">

    <?= "<?php " ?>$form = ActiveForm::begin([
        'method' => 'get',
        'enableClientValidation' => false,
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-sm-8\">{input}</div>",
            'labelOptions' => ['class' => 'col-sm-4 control-label'],
        ],
    ]); ?>

<?php
echo "    <div class=\"row\">\n";
$count = 0;
foreach ($generator->getColumnNames() as $attribute) {
    echo "        <div class=\"col-sm-4\">\n";
    echo "            <?= " . $generator->generateActiveSearchField($attribute) . " ?>\n";
    echo "        </div>\n";
    if (++$count == 6) {
        echo "<?php /*\n";
    }
}
if ($count >= 6) {
    echo "*/ ?>\n";
}
echo "    </div>\n";
?>
    <div class="form-group row">
        <div class="col-sm-4">
            <div class="col-sm-offset-4 col-sm-8">
                <?= "<?= " ?>Html::submitButton(<?= $generator->generateString('搜索') ?>, ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
        <div class="col-sm-offset-4 col-sm-4">
            <?= "<?= " ?>Html::resetButton(<?= $generator->generateString('重置') ?>, ['class' => 'btn btn-default pull-right']) ?>
        </div>
    </div>

    <?= "<?php " ?>ActiveForm::end(); ?>

</div>
