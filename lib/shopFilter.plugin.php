<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopFilterPlugin extends shopPlugin {

    public function saveSettings($settings = array()) {
        $route_hash = waRequest::post('route_hash');
        $route_settings = waRequest::post('route_settings');

        if ($routes = $this->getSettings('routes')) {
            $settings['routes'] = $routes;
        } else {
            $settings['routes'] = array();
        }
        $settings['routes'][$route_hash] = $route_settings;
        $settings['route_hash'] = $route_hash;
        parent::saveSettings($settings);
    }

    public function frontendCategory($category) {
        if (!$this->getSettings('status')) {
            return;
        }
        if (shopFilterRouteHelper::getRouteSettings(null, 'status')) {
            $route_settings = shopFilterRouteHelper::getRouteSettings();
            $route_hash = shopFilterRouteHelper::getCurrentRouteHash();
        } elseif (shopFilterRouteHelper::getRouteSettings(0, 'status')) {
            $route_settings = shopFilterRouteHelper::getRouteSettings(0);
            $route_hash = 0;
        } else {
            return;
        }

        $response = wa()->getResponse();
        $response->addHeader("X-Current-Url", wa()->getConfig()->getCurrentUrl());

        if (!empty($category['is_filter'])) {
            return;
        }

        $_get = waRequest::get();
        if (!$_get) {
            return;
        }

        $filter_model = new shopFilterPluginModel();
        $filters = $filter_model->getByField(array('category_id' => $category['id'], 'route_hash' => $route_hash), true);


        if ($filters) {
            foreach ($filters as $filter) {
                $filter['conditions'] = shopFilterHelper::parseConditions($filter['conditions']);
                if (shopFilterHelper::compareConditions($_get, $filter['conditions'], $last_get)) {
                    unset($last_get['_']);
                    $params = array(
                        'plugin' => 'filter',
                        'category_url' => waRequest::param('url_type') == 1 ? $category['url'] : $category['full_url'],
                        'filter_url' => $filter['url'],
                    );
                    $redirect_url = wa()->getRouteUrl('shop/frontend/category', $params) . ($last_get ? '?' . http_build_query($last_get) : '');
                    $response->redirect($redirect_url);
                    break;
                }
            }
        }
    }

    public function routing($route = array()) {
        if (!$this->getSettings('status')) {
            return;
        }

        if ($route['url_type'] == 2) {
            return array(
                '<category_url>/_<filter_url:[^/]+>/' => array(
                    'module' => 'frontend',
                    'plugin' => 'filter',
                    'action' => 'category',
                ),
            );
        } else {
            return array(
                'category/<category_url>/<filter_url:[^/]+>/' => array(
                    'module' => 'frontend',
                    'plugin' => 'filter',
                    'action' => 'category',
                ),
            );
        }
    }

    public function sitemap($route) {
        if (!$this->getSettings('status')) {
            return;
        }
        if (shopFilterRouteHelper::getRouteSettings(null, 'status')) {
            $route_settings = shopFilterRouteHelper::getRouteSettings();
            $route_hash = shopFilterRouteHelper::getCurrentRouteHash();
        } elseif (shopFilterRouteHelper::getRouteSettings(0, 'status')) {
            $route_settings = shopFilterRouteHelper::getRouteSettings(0);
            $route_hash = 0;
        } else {
            return;
        }

        $filter_model = new shopFilterPluginModel();
        $filters = $filter_model->getByField('route_hash', $route_hash, true);

        $urls = array();
        $category_model = new shopCategoryModel();
        foreach ($filters as $filter) {
            $category = $category_model->getById($filter['category_id']);
            $params = array(
                'plugin' => 'filter',
                'category_url' => $route['url_type'] == 1 ? $category['url'] : $category['full_url'],
                'filter_url' => $filter['url'],
            );
            $urls[] = array(
                'loc' => wa()->getRouteUrl('shop/frontend/category', $params, true),
                'changefreq' => waSitemapConfig::CHANGE_MONTHLY,
                'priority' => 0.2
            );
        }
        return $urls;
    }

}
