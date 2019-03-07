<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
return array(
    'name' => 'SEO-фильтр',
    'description' => 'Формирования отдельных страниц для результатов фильтрации',
    'vendor' => 985310,
    'version' => '1.0.1',
    'img' => 'img/filter.png',
    'shop_settings' => true,
    'frontend' => true,
    'handlers' => array(
        'frontend_category' => 'frontendCategory',
        'routing' => 'routing',
        'sitemap' => 'sitemap',
        'frontend_product' => 'frontendProduct',
    ),
);
//EOF
