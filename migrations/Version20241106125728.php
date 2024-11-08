<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour déplacer delivery_id et delivery_mode dans la table 'order'.
 */
final class Version20241106125728 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Déplace delivery_id et delivery_mode dans la table "order" et les supprime de "order_line".';
    }

    public function up(Schema $schema): void
    {
        // Ajout des colonnes delivery_id et delivery_mode dans la table order si elles n'existent pas déjà
        $orderTable = $schema->getTable('order');
        if (!$orderTable->hasColumn('delivery_id')) {
            $this->addSql('ALTER TABLE `order` ADD delivery_id INT DEFAULT NULL, ADD delivery_mode VARCHAR(255) DEFAULT NULL');
        }

        // Ajouter la contrainte de clé étrangère sur delivery_id
        if (!$orderTable->hasForeignKey('FK_F529939812136921')) {
            $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939812136921 FOREIGN KEY (delivery_id) REFERENCES delivery (id)');
        }

        // Créer l'index unique sur delivery_id dans order
        if (!$orderTable->hasIndex('UNIQ_F529939812136921')) {
            $this->addSql('CREATE UNIQUE INDEX UNIQ_F529939812136921 ON `order` (delivery_id)');
        }

        // Gestion des colonnes dans la table order_line
        $orderLineTable = $schema->getTable('order_line');

        // Supprimer la contrainte de clé étrangère de order_line avant de la modifier
        if ($orderLineTable->hasForeignKey('FK_9CE58EE112136921')) {
            $this->addSql('ALTER TABLE order_line DROP FOREIGN KEY FK_9CE58EE112136921');
        }

        // Supprimer l'index de order_line
        $this->addSql('DROP INDEX IDX_9CE58EE112136921 ON order_line');

        // Supprimer les colonnes delivery_id, delivery_mode et delivery_address de order_line
        $this->addSql('ALTER TABLE order_line DROP delivery_id, DROP delivery_mode, DROP delivery_address');
    }

    public function down(Schema $schema): void
    {
        // Annuler les changements dans la table 'order'
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939812136921');
        $this->addSql('DROP INDEX UNIQ_F529939812136921 ON `order`');
        $this->addSql('ALTER TABLE `order` DROP delivery_id, DROP delivery_mode');

        // Rétablir les colonnes dans la table 'order_line'
        $this->addSql('ALTER TABLE order_line ADD delivery_id INT DEFAULT NULL, ADD delivery_mode VARCHAR(255) NOT NULL, ADD delivery_address VARCHAR(255) DEFAULT NULL');

        // Rétablir la contrainte de clé étrangère sur order_line
        $this->addSql('ALTER TABLE order_line ADD CONSTRAINT FK_9CE58EE112136921 FOREIGN KEY (delivery_id) REFERENCES delivery (id) ON UPDATE NO ACTION ON DELETE NO ACTION');

        // Recréer l'index sur order_line
        $this->addSql('CREATE INDEX IDX_9CE58EE112136921 ON order_line (delivery_id)');
    }
}
