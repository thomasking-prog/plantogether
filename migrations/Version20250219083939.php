<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250219083939 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Temporarily rename the old column
        $this->addSql('ALTER TABLE task RENAME COLUMN estimated_time TO estimated_time_old');

        // Add a new column with the correct type
        $this->addSql('ALTER TABLE task ADD COLUMN estimated_time DOUBLE PRECISION');

        // Convert and copy the values, handling NULL cases
        $this->addSql('UPDATE task SET estimated_time = EXTRACT(EPOCH FROM estimated_time_old) WHERE estimated_time_old IS NOT NULL');

        // Remove the old column
        $this->addSql('ALTER TABLE task DROP COLUMN estimated_time_old');
    }

    public function down(Schema $schema): void
    {
        // Reverse the migration in case of rollback
        $this->addSql('ALTER TABLE task ADD COLUMN estimated_time_old TIMESTAMP');
        $this->addSql('UPDATE task SET estimated_time_old = TO_TIMESTAMP(estimated_time) WHERE estimated_time IS NOT NULL');
        $this->addSql('ALTER TABLE task DROP COLUMN estimated_time');
        $this->addSql('ALTER TABLE task RENAME COLUMN estimated_time_old TO estimated_time');
    }
}
