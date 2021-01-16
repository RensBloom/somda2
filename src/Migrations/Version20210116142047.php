<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210116142047 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription() : string
    {
        return 'Add day-number to spots table for faster joining with route-operation-days';
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE `somda_spots` ADD `dag` INT(11) NULL');
        $this->addSql('UPDATE `somda_spots` SET `dag` = DAYOFWEEK(`datum`)');
        $this->addSql('UPDATE `somda_spots` SET `dag` = 7 WHERE `dag` = 0');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // Not applicable
    }
}
