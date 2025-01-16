<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250116144904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE participate (id SERIAL NOT NULL, member_id INT NOT NULL, project_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D02B1387597D3FE ON participate (member_id)');
        $this->addSql('CREATE INDEX IDX_D02B138166D1F9C ON participate (project_id)');
        $this->addSql('ALTER TABLE participate ADD CONSTRAINT FK_D02B1387597D3FE FOREIGN KEY (member_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE participate ADD CONSTRAINT FK_D02B138166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE participate DROP CONSTRAINT FK_D02B1387597D3FE');
        $this->addSql('ALTER TABLE participate DROP CONSTRAINT FK_D02B138166D1F9C');
        $this->addSql('DROP TABLE participate');
    }
}
