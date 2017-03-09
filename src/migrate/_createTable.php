<?php

/**
 * Creates a call for the method `yii\db\Migration::createTable()`
 */
/* @var $table string the name table */
/* @var $fields array the fields */
/* @var $foreignKeys array the foreign keys */

?>        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            // http://stackoverflow.com/questions/35125933/mysql-utf8mb4-errors-when-saving-emojis
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%<?= $table ?>}}', [
<?php foreach ($fields as $field):
    if (empty($field['decorators'])): ?>
            '<?= $field['property'] ?>',
<?php else: ?>
            <?= "'{$field['property']}' => \$this->{$field['decorators']}" ?>,
<?php endif;
endforeach; ?>
        ], $tableOptions);
<?= $this->render('_addForeignKeys', [
    'table' => $table,
    'foreignKeys' => $foreignKeys,
]);
