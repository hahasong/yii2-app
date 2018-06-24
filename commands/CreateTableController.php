<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;

/**
 * init database and tables.
 */
class CreateTableController extends Controller
{
    /**
     * init table
     * @return int Exit code
     */
    public function actionCreate()
    {
        $sql = <<<EOT
CREATE TABLE `account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `card_id` varchar(16) NOT NULL,
  `balance` double(16,2) NOT NULL DEFAULT '0.00',
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_card_id` (`card_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `transfer_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_card_id` varchar(16) NOT NULL,
  `to_card_id` varchar(16) NOT NULL,
  `amount` double(16,2) NOT NULL DEFAULT '0.00',
  `charge` double(16,2) NOT NULL DEFAULT '0.00',
  `created_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4;
EOT;
        $ret = \Yii::$app->getDb()->createCommand($sql)->execute();

        return $ret;
    }
}
