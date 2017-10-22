<?php

use Phinx\Migration\AbstractMigration;

class CreateImageTables extends AbstractMigration
{
  public function up()
  {
    $this->execute("CREATE TABLE `image` (
      `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
      `name` VARCHAR(1024) NOT NULL,
      `size` INT(10) UNSIGNED NOT NULL,
      `type` INT(10) UNSIGNED NOT NULL,
      `checksum` BLOB(256) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY(`checksum`(256))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $this->execute("CREATE TABLE `image_folder` (
      `folderId` INT(10) UNSIGNED NOT NULL,
      `imageId` INT(10) UNSIGNED NOT NULL,
      `folderOrder` INT(10) UNSIGNED NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  }
  public function down()
  {
    $this->execute("DROP TABLE `image`");
    $this->execute("DROP TABLE `image_folder`");
  }
}
