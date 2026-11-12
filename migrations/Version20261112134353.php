<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261112134353 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE weather_data (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME DEFAULT NULL, temperature DOUBLE PRECISION DEFAULT NULL, humidity DOUBLE PRECISION DEFAULT NULL, weather_station_id INT DEFAULT NULL, INDEX IDX_3370691A9E475DA2 (weather_station_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE weather_station (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, uuid BINARY(16) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE weather_data ADD CONSTRAINT FK_3370691A9E475DA2 FOREIGN KEY (weather_station_id) REFERENCES weather_station (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE weather_data DROP FOREIGN KEY FK_3370691A9E475DA2');
        $this->addSql('DROP TABLE weather_data');
        $this->addSql('DROP TABLE weather_station');
    }
}
