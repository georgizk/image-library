<?php

use Phinx\Migration\AbstractMigration;

class CreateFolderTable extends AbstractMigration
{
  public function up()
  {
    $this->execute("CREATE TABLE `folder` (
      `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
      `name` VARCHAR(1024) NOT NULL,
      `date` DATETIME NOT NULL,
      `status` INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `date` (`date`),
    INDEX `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  }
  public function down()
  {
    $this->execute("DROP TABLE `folder`");
  }
}
