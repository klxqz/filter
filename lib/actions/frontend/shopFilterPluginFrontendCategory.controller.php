<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopFilterPluginFrontendCategoryController extends shopFrontendProductController {

    public function execute() {
        if (waRequest::param('url_type') == 1 || shopFilterHelper::getCurrentFilter()) {
            $this->executeAction(new shopFilterPluginFrontendCategoryAction());
        } else {
            $category_model = new shopCategoryModel();
            $category_full_url = waRequest::param('category_url') . '/' . waRequest::param('filter_url');
            $c = $category_model->getByField('full_url', $category_full_url);
            if ($c) {
                waRequest::setParam('category_url', $category_full_url);
                waRequest::setParam('filter_url', '');
                $this->executeAction(new shopFrontendCategoryAction());
            } else {
                waRequest::setParam('product_url', waRequest::param('filter_url'));
                waRequest::setParam('filter_url', '');
                parent::execute();
            }
        }
        waSystem::popActivePlugin();
    }

}
