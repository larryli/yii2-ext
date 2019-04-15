<?php
/* @var $table string the table name */
/* @var $fields array the fields */
/* @var $foreignKeys array the foreign keys */

foreach ($fields as $field): ?>
        $this->addColumn('<?=
            $table
        ?>', '<?=
            $field['property']
        ?>', $this-><?=
            $field['decorators']
        ?>);
<?php endforeach;

echo $this->render('_addForeignKeys', [
    'table' => $table,
    'foreignKeys' => $foreignKeys,
]);
