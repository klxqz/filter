<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopFilterHelper {

    public static function setFilterGetParams($filter) {
        $conditions = self::parseConditions($filter['conditions']);

        foreach ($conditions as $conditions_type => $_conditions) {
            if ($conditions_type == 'feature') {
                foreach ($_conditions as $feature_id => $condition) {
                    if (!isset($_GET[$feature_id])) {
                        $_GET[$feature_id] = array($condition[1]);
                    } else {
                        $_GET[$feature_id] += array($condition[1]);
                    }
                }
            } elseif ($conditions_type == 'price') {
                foreach ($_conditions as $condition) {
                    if ($condition[0] == '>=') {
                        $_GET['price_min'] = $condition[1];
                    } elseif ($condition[0] == '<=') {
                        $_GET['price_max'] = $condition[1];
                    }
                }
            }
        }
    }

    public static function compareConditions($_get, $conditions, &$last_get) {
        $last_get = $_get;
        foreach ($conditions as $conditions_type => $_conditions) {
            if ($conditions_type == 'feature') {
                foreach ($_conditions as $feature_id => $condition) {
                    if (empty($_get[$feature_id]) || !in_array($condition[1], $_get[$feature_id])) {
                        return false;
                    } else {
                        unset($last_get[$feature_id][array_search($condition[1], $last_get[$feature_id])]);
                    }
                }
            } elseif ($conditions_type == 'price') {
                foreach ($_conditions as $condition) {
                    if ($condition[0] == '>=') {
                        if (empty($_get['price_min']) || $_get['price_min'] != $condition[1]) {
                            return false;
                        } else {
                            unset($last_get['price_min']);
                        }
                    } elseif ($condition[0] == '<=') {
                        if (empty($_get['price_max']) || $_get['price_max'] != $condition[1]) {
                            return false;
                        } else {
                            unset($last_get['price_max']);
                        }
                    }
                }
            }
        }

        foreach ($last_get as $feature_id => $condition) {
            if (empty($last_get[$feature_id])) {
                unset($last_get[$feature_id]);
            }
        }

        $feature_model = new shopFeatureModel();
        $features = $feature_model->getFeatures('selectable', 1, 'code');
        $features += $feature_model->getFeatures('type', 'boolean', 'code');
        foreach ($last_get as $feature_id => $feature_values) {
            if (isset($features[$feature_id])) {
                return false;
            }
        }

        if (isset($last_get['price_min']) || isset($last_get['price_max'])) {
            return false;
        }
        return true;
    }

    public static function conditionsToGetArray($conditions) {
        $conditions = self::parseConditions($conditions);
        $filter_data = array();
        if (!empty($conditions['feature'])) {
            $filter_data += $conditions['feature'];
        }
        if (!empty($conditions['price'])) {
            foreach ($conditions['price'] as $price_filter) {
                if ($price_filter[0] == '>=') {
                    $filter_data += array('price_min' => $price_filter[1]);
                } elseif ($price_filter[0] == '<=') {
                    $filter_data += array('price_max' => $price_filter[1]);
                }
            }
        }
        return $filter_data;
    }

    public static function getTxtConditions($conditions) {
        $conditions = self::parseConditions($conditions);
        $feature_model = new shopFeatureModel();
        $features = $feature_model->getFeatures('selectable', 1, 'code');
        $features += $feature_model->getFeatures('type', 'boolean', 'code');
        $features = $feature_model->getValues($features);

        $conditions_txt = '';
        if (!empty($conditions['price'])) {
            $conditions_txt .= 'Цена';
            foreach ($conditions['price'] as $condition_price) {
                if ($condition_price[0] == '>=') {
                    $conditions_txt .= ' от ' . $condition_price[1];
                } elseif ($condition_price[0] == '<=') {
                    $conditions_txt .= ' до ' . $condition_price[1];
                }
            }
            $conditions_txt .= '; ';
        }
        if (!empty($conditions['feature'])) {
            foreach ($conditions['feature'] as $condition_feature => $condition) {
                if (!empty($features[$condition_feature]) && !empty($features[$condition_feature]['values'][$condition[1]])) {
                    $conditions_txt .= sprintf('%s: %s; ', $features[$condition_feature]['name'], $features[$condition_feature]['values'][$condition[1]]);
                }
            }
        }
        return $conditions_txt;
    }

    public static function getConditions($raw_condition) {
        $conditions = array();
        if (isset($raw_condition['price'])) {
            $raw_condition['price'] = waRequest::post('price');
            if (!empty($raw_condition['price'][0])) {
                $conditions[] = 'price>=' . $raw_condition['price'][0];
            }
            if (!empty($raw_condition['price'][1])) {
                $conditions[] = 'price<=' . $raw_condition['price'][1];
            }
        }

        if (isset($raw_condition['feature'])) {
            $feature_values = waRequest::post('feature_values');
            foreach ($raw_condition['feature'] as $f_code) {
                $conditions[] = $f_code . '.value_id=' . $feature_values[$f_code];
            }
        }

        if (isset($raw_condition['compare_price'])) {
            $conditions[] = 'compare_price>0';
        }

        $conditions = implode('&', $conditions);
        return $conditions;
    }

    public static function parseConditions($conditions) {
        $conditions = shopProductsCollection::parseConditions($conditions);
        foreach ($conditions as $name => $value) {
            if (substr($name, -9) === '.value_id') {
                unset($conditions[$name]);
                $conditions['feature'][substr($name, 0, -9)] = $value;
            }
        }

        return $conditions;
    }

    public static function getCurrentFilter() {
        if (shopFilterRouteHelper::getRouteSettings(null, 'status')) {
            $route_settings = shopFilterRouteHelper::getRouteSettings();
            $route_hash = shopFilterRouteHelper::getCurrentRouteHash();
        } elseif (shopFilterRouteHelper::getRouteSettings(0, 'status')) {
            $route_settings = shopFilterRouteHelper::getRouteSettings(0);
            $route_hash = 0;
        } else {
            return false;
        }

        $url_field = waRequest::param('url_type') == 1 ? 'url' : 'full_url';
        $category_model = new shopCategoryModel();
        $category = $category_model->getByField($url_field, waRequest::param('category_url'));

        if (!$category) {
            return false;
        }

        $fields = array(
            'route_hash' => $route_hash,
            'category_id' => $category['id'],
            'url' => waRequest::param('filter_url'),
        );

        $filter_model = new shopFilterPluginModel();
        $filter = $filter_model->getByField($fields);

        return $filter;
    }

    public static function getCategoryTree() {

        $category_model = new shopCategoryModel();
        $route = null;
        $cats = $category_model->getTree(null, null, false, $route);


        $stack = array();
        $result = array();
        foreach ($cats as $c) {
            $c['childs'] = array();

            // Number of stack items
            $l = count($stack);

            // Check if we're dealing with different levels
            while ($l > 0 && $stack[$l - 1]['depth'] >= $c['depth']) {
                array_pop($stack);
                $l--;
            }

            // Stack is empty (we are inspecting the root)
            if ($l == 0) {
                // Assigning the root node
                $i = count($result);
                $result[$i] = $c;
                $stack[] = &$result[$i];
            } else {
                // Add node to parent
                $i = count($stack[$l - 1]['childs']);
                $stack[$l - 1]['childs'][$i] = $c;
                $stack[] = &$stack[$l - 1]['childs'][$i];
            }
        }
        return $result;
    }

    public static function getCategoryFilters($category_id) {
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
        $filters = $filter_model->getByField(array('category_id' => $category_id, 'route_hash' => $route_hash), true);

        $category_model = new shopCategoryModel();
        foreach ($filters as &$filter) {
            $category = $category_model->getById($filter['category_id']);
            $params = array(
                'plugin' => 'filter',
                'category_url' => waRequest::param('url_type') == 1 ? $category['url'] : $category['full_url'],
                'filter_url' => $filter['url'],
            );
            $filter['full_url'] = wa()->getRouteUrl('shop/frontend/category', $params);
        }
        unset($filter);

        return $filters;
    }

}
