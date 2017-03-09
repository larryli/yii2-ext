<?php
/* @var $table string the table name */
/* @var $fields array the fields */
/* @var $foreignKeys array the foreign keys */

echo  $this->render('_dropForeignKeys', [
    'table' => $table,
    'foreignKeys' => $foreignKeys,
]);

foreach ($fields as $field): ?>
        $this->dropColumn('{{%<?= $table ?>}}', '<?= $field['property'] ?>');
<?php endforeach;
