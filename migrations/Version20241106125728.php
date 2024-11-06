<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241106125728 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` ADD delivery_id INT DEFAULT NULL, ADD delivery_mode VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939812136921 FOREIGN KEY (delivery_id) REFERENCES delivery (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F529939812136921 ON `order` (delivery_id)');
        $this->addSql('ALTER TABLE order_line DROP FOREIGN KEY FK_9CE58EE112136921');
        $this->addSql('DROP INDEX IDX_9CE58EE112136921 ON order_line');
        $this->addSql('ALTER TABLE order_line DROP delivery_id, DROP delivery_mode, DROP delivery_address');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939812136921');
        $this->addSql('DROP INDEX UNIQ_F529939812136921 ON `order`');
        $this->addSql('ALTER TABLE `order` DROP delivery_id, DROP delivery_mode');
        $this->addSql('ALTER TABLE order_line ADD delivery_id INT DEFAULT NULL, ADD delivery_mode VARCHAR(255) NOT NULL, ADD delivery_address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE order_line ADD CONSTRAINT FK_9CE58EE112136921 FOREIGN KEY (delivery_id) REFERENCES delivery (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_9CE58EE112136921 ON order_line (delivery_id)');
    }
}
