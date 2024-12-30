<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241206161843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE receipts DROP FOREIGN KEY FK_1DEBE3A22FC0CB0F');
        $this->addSql('ALTER TABLE receipts ADD CONSTRAINT FK_1DEBE3A22FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transactions (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE receipts DROP FOREIGN KEY FK_1DEBE3A22FC0CB0F');
    }
}
