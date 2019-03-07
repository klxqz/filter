<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
return array(
    'shop_filter' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'route_hash' => array('varchar', 32, 'null' => 0, 'default' => ''),
        'category_id' => array('int', 11, 'null' => 0),
        'url' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'name' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'meta_title' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'meta_keywords' => array('text'),
        'meta_description' => array('text'),
        'description' => array('text'),
        'conditions' => array('text'),
        'conditions_txt' => array('text'),
        'sort' => array('int', 11, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'PRIMARY' => array('id'),
        ),
    )
);
