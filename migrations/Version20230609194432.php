<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230609194432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_login_codes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED DEFAULT NULL, code VARCHAR(6) NOT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, expiration DATETIME NOT NULL, INDEX IDX_4AC6CF4CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_login_codes ADD CONSTRAINT FK_4AC6CF4CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_login_codes DROP FOREIGN KEY FK_4AC6CF4CA76ED395');
        $this->addSql('DROP TABLE user_login_codes');
    }
}
