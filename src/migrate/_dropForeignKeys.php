<?php
/* @var $table string the table name */
/* @var $foreignKeys array the foreign keys */

foreach ($foreignKeys as $column => $fkData): ?>
        // drops foreign key for table `<?= $fkData['relatedTable'] ?>`
        $this->dropForeignKey(
            '<?= $fkData['fk'] ?>',
            '<?= $table ?>'
        );

        // drops index for column `<?= $column ?>`
        $this->dropIndex(
            '<?= $fkData['idx'] ?>',
            '<?= $table ?>'
        );

<?php endforeach;
