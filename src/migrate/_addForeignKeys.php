<?php
/* @var $table string the table name */
/* @var $foreignKeys array the foreign keys */

foreach ($foreignKeys as $column => $fkData): ?>

        // creates index for column `<?= $column ?>`
        $this->createIndex(
            '<?= $fkData['idx']  ?>',
            '{{%<?= $table ?>}}',
            '<?= $column ?>'
        );

        // add foreign key for table `{{%<?= $fkData['relatedTable'] ?>}}`
        $this->addForeignKey(
            '<?= $fkData['fk'] ?>',
            '{{%<?= $table ?>}}',
            '<?= $column ?>',
            '{{%<?= $fkData['relatedTable'] ?>}}',
            'id',
            'CASCADE'
        );
<?php endforeach;