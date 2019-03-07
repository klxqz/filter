<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopFilterPluginFilterSaveController extends waJsonController {

    public function execute() {
        try {
            $post = waRequest::post('filter');
            if (!$post) {
                throw new Exception('Ошибка передачи данных');
            }
            if (!waRequest::post('condition')) {
                throw new Exception('Укажите условия фильтрации');
            }
            if (empty($post['name'])) {
                throw new Exception('Укажите название');
            }
            if (empty($post['url'])) {
                throw new Exception('Укажите URL');
            }

            $route = shopFilterRouteHelper::getRouteByHash($post['route_hash']);


            $condition = waRequest::post('condition', array());
            $post['conditions'] = shopFilterHelper::getConditions($condition);
            $post['conditions_txt'] = shopFilterHelper::getTxtConditions($post['conditions']);

            $filter_model = new shopFilterPluginModel();
            if ($post['id']) {
                $filter_model->updateById($post['id'], $post);
            } else {
                $post['id'] = $filter_model->insert($post);
            }

            $category_model = new shopCategoryModel();

            $post['category'] = $category = $category_model->getById($post['category_id']);

            $params = array(
                'plugin' => 'filter',
                'category_url' => $route['url_type'] == 1 ? $category['url'] : $category['full_url'],
                'filter_url' => $post['url'],
            );

            $post['full_url'] = wa()->getRouteUrl('shop/frontend/category', $params, true, $route['domain'], $route['url']);

            $this->response = $post;
        } catch (Exception $ex) {
            $this->setError($ex->getMessage());
        }
    }

}
