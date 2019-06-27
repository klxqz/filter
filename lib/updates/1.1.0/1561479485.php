<?php

$model = new waModel();
try {
    $sql = 'ALTER TABLE `shop_filter` ADD `params` TEXT NULL AFTER `description`';
    $model->query($sql);
} catch (waDbException $ex) {

}

$model = new waModel();
try {
    $sql = "ALTER TABLE `shop_filter` ADD `enabled` TINYINT(1) NOT NULL DEFAULT '1' AFTER `id`";
    $model->query($sql);
} catch (waDbException $ex) {

}

