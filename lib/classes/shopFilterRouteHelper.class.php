<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopFilterRouteHelper {

    private static $plugin = 'filter';

    public static function getRouteSettings($route = null, $setting = null) {
        if ($route === null) {
            $route = self::getCurrentRouteHash();
        }
        $routes = wa('shop')->getPlugin(self::$plugin)->getSettings('routes');
        if (!empty($routes[$route])) {
            $route_settings = $routes[$route];
        } else {
            $route_settings = array();
        }

        if (!$setting) {
            return $route_settings;
        } elseif (!empty($route_settings[$setting])) {
            return $route_settings[$setting];
        } else {
            return null;
        }
    }

    public static function getCurrentRouteHash() {
        $domain = wa()->getRouting()->getDomain(null, true);
        $route = wa()->getRouting()->getRoute();
        return md5($domain . '/' . $route['url']);
    }

    public static function getRouteHashs() {
        $route_hashs = array();
        $routing = wa()->getRouting();
        $domain_routes = $routing->getByApp('shop');
        foreach ($domain_routes as $domain => $routes) {
            foreach ($routes as $route) {
                $route_url = $domain . '/' . $route['url'];
                $route_hashs[$route_url] = md5($route_url);
            }
        }
        return $route_hashs;
    }

    public static function getRouteByHash($route_hash) {
        $route_hashs = array();
        $routing = wa()->getRouting();
        $domain_routes = $routing->getByApp('shop');
        foreach ($domain_routes as $domain => $routes) {
            foreach ($routes as $route) {
                $route_url = $domain . '/' . $route['url'];
                $route['domain'] = $domain;
                $route_hashs[md5($route_url)] = $route;
            }
        }
        if (!empty($route_hashs[$route_hash])) {
            return $route_hashs[$route_hash];
        } else {
            $domain = wa()->getRouting()->getDomain(null, true);
            $routes = wa()->getRouting()->getByApp('shop');
            return $route = end($routes[$domain]);
        }
    }

}
