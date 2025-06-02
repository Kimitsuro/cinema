<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250528181845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE admin (admin_login VARCHAR(50) NOT NULL, admin_password VARCHAR(255) NOT NULL, admin_name VARCHAR(100) NOT NULL, PRIMARY KEY(admin_login))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE employee (employee_login VARCHAR(50) NOT NULL, admin_login VARCHAR(50) NOT NULL, employee_password VARCHAR(255) NOT NULL, employee_name VARCHAR(100) NOT NULL, employee_position VARCHAR(200) NOT NULL, PRIMARY KEY(employee_login))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5D9F75A1A2230EA ON employee (admin_login)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE goer (goer_email VARCHAR(320) NOT NULL, goer_name VARCHAR(100) NOT NULL, goer_phone VARCHAR(10) DEFAULT NULL, goer_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, goer_password VARCHAR(255) NOT NULL, PRIMARY KEY(goer_email))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE hall (id SERIAL NOT NULL, hall_capacity SMALLINT NOT NULL, hall_type VARCHAR(20) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE movie (id SERIAL NOT NULL, admin_login VARCHAR(50) NOT NULL, movie_title VARCHAR(100) NOT NULL, movie_genre VARCHAR(50) NOT NULL, movie_duration SMALLINT NOT NULL, movie_format VARCHAR(100) NOT NULL, movie_age SMALLINT NOT NULL, movie_poster VARCHAR(255) NOT NULL, movie_description TEXT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1D5EF26FA2230EA ON movie (admin_login)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE purchase (id SERIAL NOT NULL, session_id INT NOT NULL, goer_email VARCHAR(320) NOT NULL, purchase_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, purchase_amount NUMERIC(10, 2) NOT NULL, purchase_status VARCHAR(100) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6117D13B613FECDF ON purchase (session_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6117D13B6CFF679C ON purchase (goer_email)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE refund (id SERIAL NOT NULL, goer_email VARCHAR(320) NOT NULL, ticket_id INT NOT NULL, refund_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, refund_reason VARCHAR(500) NOT NULL, refund_status VARCHAR(50) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5B2C14586CFF679C ON refund (goer_email)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5B2C1458700047D2 ON refund (ticket_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE seat (id SERIAL NOT NULL, hall_id INT NOT NULL, ticket_id INT DEFAULT NULL, sear_number SMALLINT NOT NULL, seat_row SMALLINT NOT NULL, seat_type VARCHAR(50) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3D5C366652AFCFD6 ON seat (hall_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3D5C3666700047D2 ON seat (ticket_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE session (id SERIAL NOT NULL, employee_login VARCHAR(50) DEFAULT NULL, hall_id INT NOT NULL, movie_id INT NOT NULL, session_data TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, session_price NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D044D5D44205C669 ON session (employee_login)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D044D5D452AFCFD6 ON session (hall_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D044D5D48F93B6FC ON session (movie_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ticket (id SERIAL NOT NULL, session_id INT NOT NULL, refund_id INT DEFAULT NULL, purchase_id INT NOT NULL, seat_id INT NOT NULL, ticket_status VARCHAR(100) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_97A0ADA3613FECDF ON ticket (session_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_97A0ADA3189801D5 ON ticket (refund_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_97A0ADA3558FBEB9 ON ticket (purchase_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_97A0ADA3C1DAFE35 ON ticket (seat_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE employee ADD CONSTRAINT FK_5D9F75A1A2230EA FOREIGN KEY (admin_login) REFERENCES admin (admin_login) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE movie ADD CONSTRAINT FK_1D5EF26FA2230EA FOREIGN KEY (admin_login) REFERENCES admin (admin_login) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B613FECDF FOREIGN KEY (session_id) REFERENCES session (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B6CFF679C FOREIGN KEY (goer_email) REFERENCES goer (goer_email) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE refund ADD CONSTRAINT FK_5B2C14586CFF679C FOREIGN KEY (goer_email) REFERENCES goer (goer_email) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE refund ADD CONSTRAINT FK_5B2C1458700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE seat ADD CONSTRAINT FK_3D5C366652AFCFD6 FOREIGN KEY (hall_id) REFERENCES hall (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE seat ADD CONSTRAINT FK_3D5C3666700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session ADD CONSTRAINT FK_D044D5D44205C669 FOREIGN KEY (employee_login) REFERENCES employee (employee_login) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session ADD CONSTRAINT FK_D044D5D452AFCFD6 FOREIGN KEY (hall_id) REFERENCES hall (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session ADD CONSTRAINT FK_D044D5D48F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3613FECDF FOREIGN KEY (session_id) REFERENCES session (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3189801D5 FOREIGN KEY (refund_id) REFERENCES refund (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchase (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3C1DAFE35 FOREIGN KEY (seat_id) REFERENCES seat (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE employee DROP CONSTRAINT FK_5D9F75A1A2230EA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE movie DROP CONSTRAINT FK_1D5EF26FA2230EA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE purchase DROP CONSTRAINT FK_6117D13B613FECDF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE purchase DROP CONSTRAINT FK_6117D13B6CFF679C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE refund DROP CONSTRAINT FK_5B2C14586CFF679C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE refund DROP CONSTRAINT FK_5B2C1458700047D2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE seat DROP CONSTRAINT FK_3D5C366652AFCFD6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE seat DROP CONSTRAINT FK_3D5C3666700047D2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session DROP CONSTRAINT FK_D044D5D44205C669
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session DROP CONSTRAINT FK_D044D5D452AFCFD6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session DROP CONSTRAINT FK_D044D5D48F93B6FC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ticket DROP CONSTRAINT FK_97A0ADA3613FECDF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ticket DROP CONSTRAINT FK_97A0ADA3189801D5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ticket DROP CONSTRAINT FK_97A0ADA3558FBEB9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ticket DROP CONSTRAINT FK_97A0ADA3C1DAFE35
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE admin
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE employee
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE goer
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE hall
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE movie
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE purchase
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE refund
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE seat
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE session
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ticket
        SQL);
    }
}
