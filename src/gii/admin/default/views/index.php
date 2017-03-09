<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator extras\gii\admin\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();
$modelClass = empty($generator->adminModelClass) ? $generator->modelClass : $generator->adminModelClass;
$modelName = empty($generator->modelName) ? Inflector::camel2words(StringHelper::basename($modelClass)) : $generator->modelName;

echo "<?php\n";
?>

use app\admin\widgets\ActionColumn;
use <?= $generator->indexWidgetType === 'grid' ? "app\\admin\\widgets\\GridView;" : "app\\admin\\widgets\\ListView" ?>;
use app\admin\widgets\SerialColumn;
use app\helpers\Html;
<?= $generator->enablePjax ? 'use yii\widgets\Pjax;' : '' ?>

/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = <?= $generator->generateString($modelName . '管理') ?>;
$this->params['description'] = <?= $generator->generateString('管理' . $modelName . '数据') ?>;
$this->params['breadcrumbs'][] = <?= $generator->generateString($modelName) ?>;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($modelClass)) ?>-index box">
    <div class="box-header with-border">
        <h3 class="box-title">搜索<?= $modelName ?></h3>
        <div class="box-tools pull-right-not-xs">
            <?= "<?= " ?>Html::a(<?= $generator->generateString('增加' . $modelName) ?>, ['create'], ['class' => 'btn btn-success btn-sm', 'fa' => 'plus']) ?>
        </div>
    </div>
    <div class="box-body<?= "<?php " ?>Yii::$app->detect->isMobile && print ' no-padding'; ?>">
<?php if(!empty($generator->searchModelClass)): ?>
        <?= "<?php " ?>Yii::$app->detect->isMobile || print $this->render('_search', ['model' => $searchModel]); ?>
<?php endif; ?>

<?= $generator->enablePjax ? '<?php Pjax::begin(); ?>' : '' ?>
<?php if ($generator->indexWidgetType === 'grid'): ?>
        <?= "<?= " ?>GridView::widget([
            'dataProvider' => $dataProvider,
            <?= !empty($generator->searchModelClass) ? "// 'filterModel' => \$searchModel,\n            'columns' => [\n" : "'columns' => [\n"; ?>
                [
                    'class' => SerialColumn::class,
                    'hiddenXs' => true,
                ],

<?php
$count = 0;
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        if (++$count < 6) {
            echo "                '" . $name . "',\n";
        } else {
            echo "                // '" . $name . "',\n";
        }
    }
} else {
    foreach ($tableSchema->columns as $column) {
        $format = $generator->generateColumnFormat($column);
        if (++$count < 6) {
            echo "                '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
        } else {
            echo "                // '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
        }
    }
}
?>

                ['class' => ActionColumn::class],
            ],
        ]); ?>
<?php else: ?>
        <?= "<?= " ?>ListView::widget([
            'dataProvider' => $dataProvider,
            'itemOptions' => ['class' => 'item'],
            'itemView' => function ($model, $key, $index, $widget) {
                return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
            },
        ]) ?>
<?php endif; ?>
<?= $generator->enablePjax ? '<?php Pjax::end(); ?>' : '' ?>
    </div>
</div>
