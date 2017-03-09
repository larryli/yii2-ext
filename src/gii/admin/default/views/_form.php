<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator extras\gii\admin\Generator */
/* @var $model yii\db\ActiveRecord */

$model = new $generator->modelClass();
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}
$modelClass = empty($generator->adminModelClass) ? $generator->modelClass : $generator->adminModelClass;
$template = '{label}\n<div class=\"col-sm-8\">{input}</div>\n<div class=\"col-sm-offset-2 col-sm-10\">{error}</div>';

echo "<?php\n";
?>

use app\admin\widgets\ActiveForm;
use app\helpers\Html;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($modelClass, '\\') ?> */
/* @var $form yii\bootstrap\ActiveForm */

?>
<div class="<?= Inflector::camel2id(StringHelper::basename($modelClass)) ?>-form box-body">

    <?= "<?php " ?>$form = ActiveForm::begin(['id' => '<?= Inflector::camel2id(StringHelper::basename($modelClass)) ?>-form']); ?>

<?php foreach ($generator->getColumnNames() as $attribute) {
    if (in_array($attribute, $safeAttributes)) {
        echo "    <?= " . $generator->generateActiveField($attribute) . " ?>\n\n";
    }
} ?>
</div>
<div class="form-group box-footer">
    <div class="col-sm-offset-2 col-sm-4">
        <?= "<?= " ?>Html::submitButton($model->isNewRecord ? <?= $generator->generateString('增加') ?> : <?= $generator->generateString('修改') ?>, ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
</div>

<?= "<?php " ?>ActiveForm::end(); ?>
