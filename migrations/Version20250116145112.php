<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250116145112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE affect (id SERIAL NOT NULL, member_id INT NOT NULL, task_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2DF1D3E47597D3FE ON affect (member_id)');
        $this->addSql('CREATE INDEX IDX_2DF1D3E48DB60186 ON affect (task_id)');
        $this->addSql('ALTER TABLE affect ADD CONSTRAINT FK_2DF1D3E47597D3FE FOREIGN KEY (member_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE affect ADD CONSTRAINT FK_2DF1D3E48DB60186 FOREIGN KEY (task_id) REFERENCES task (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE affect DROP CONSTRAINT FK_2DF1D3E47597D3FE');
        $this->addSql('ALTER TABLE affect DROP CONSTRAINT FK_2DF1D3E48DB60186');
        $this->addSql('DROP TABLE affect');
    }
}
