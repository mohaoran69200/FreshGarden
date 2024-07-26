<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240726095707 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_profile (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, image_id INT DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(10) DEFAULT NULL, city VARCHAR(75) DEFAULT NULL, date_birth DATE DEFAULT NULL, phone_number VARCHAR(10) DEFAULT NULL, gender VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_D95AB405A76ED395 (user_id), UNIQUE INDEX UNIQ_D95AB4053DA5256D (image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_profile ADD CONSTRAINT FK_D95AB405A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_profile ADD CONSTRAINT FK_D95AB4053DA5256D FOREIGN KEY (image_id) REFERENCES image (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_profile DROP FOREIGN KEY FK_D95AB405A76ED395');
        $this->addSql('ALTER TABLE user_profile DROP FOREIGN KEY FK_D95AB4053DA5256D');
        $this->addSql('DROP TABLE user_profile');
    }
}
