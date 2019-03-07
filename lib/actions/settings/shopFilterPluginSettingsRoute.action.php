<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopFilterPluginSettingsRouteAction extends waViewAction {

    public function execute() {
        $route_hash = waRequest::get('route_hash');
        $filter_model = new shopFilterPluginModel();
        $filters = $filter_model->getByField('route_hash', $route_hash, true);
        $category_model = new shopCategoryModel();
        $route = shopFilterRouteHelper::getRouteByHash($route_hash);
        foreach ($filters as &$filter) {
            $category = $category_model->getById($filter['category_id']);
            $filter['category'] = $category;
            $params = array(
                'plugin' => 'filter',
                'category_url' => $route['url_type'] == 1 ? $category['url'] : $category['full_url'],
                'filter_url' => $filter['url'],
            );
            $filter['full_url'] = wa()->getRouteUrl('shop/frontend/category', $params, true, $route['domain'], $route['url']);
        }
        unset($filter);

        $categories = new shopCategories();
        if (!$categories->isExpanded()) {
            shopCategories::setExpanded(0);
        }

        $view = wa()->getView();
        $view->assign(array(
            'route_hash' => $route_hash,
            'route_settings' => shopFilterRouteHelper::getRouteSettings($route_hash),
            'filters' => $filters,
            'categories' => $categories,
        ));
    }

}
