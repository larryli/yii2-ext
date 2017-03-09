<?php
/**
 * This is the template for generating admin CRUD model class of the specified model.
 */

use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator extras\gii\admin\Generator */

$modelClass = StringHelper::basename($generator->modelClass);
$adminModelClass = StringHelper::basename($generator->adminModelClass);
if ($modelClass === $adminModelClass) {
    $modelAlias = 'Base' . $modelClass;
}
$rules = $generator->generateRules();
$labels = $generator->generateSearchLabels();

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->searchModelClass, '\\')) ?>;

use <?= ltrim($generator->modelClass, '\\') . (isset($modelAlias) ? " as $modelAlias" : "") ?>;

/**
 * <?= $adminModelClass ?> represents the admin model form about `<?= $generator->modelClass ?>`.
 */
class <?= $adminModelClass ?> extends <?= isset($modelAlias) ? $modelAlias : $modelClass ?>

{
//    /**
//     * @inheritdoc
//     */
//    public function rules()
//    {
//        return [
//            <?= implode(",\n//            ", $rules) ?>,
//        ];
//    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
<?php foreach ($labels as $name => $label): ?>
            <?= "'$name' => " . $generator->generateString($label) . ",\n" ?>
<?php endforeach; ?>
        ];
    }
}
