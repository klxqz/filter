<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopFilterPluginFilterDialogAction extends waViewAction {

    public function execute() {
        $route_hash = waRequest::get('route_hash');
        if ($id = waRequest::get('id', 0, waRequest::TYPE_INT)) {
            $filter_model = new shopFilterPluginModel();
            $filter = $filter_model->getById($id);
        }
        $category_id = waRequest::get('category_id', 0, waRequest::TYPE_INT);

        $cache_id = md5('shopFilterPlugin::getCategoryTree');
        $cache_time = wa()->getConfig()->isDebug() ? 0 : 7200;
        $cache = new waSerializeCache($cache_id, $cache_time, 'shop/plugins/filter');

        if ($cache && $cache->isCached()) {
            $categories_html = $cache->get();
        } else {
            $categories = shopFilterHelper::getCategoryTree();
            $categories_html = $this->categoriesTreeHtml($categories);
            if ($cache) {
                $cache->set($categories_html);
            }
        }

        $this->view->assign(array(
            'route_hash' => $route_hash,
            'category_id' => $category_id,
            'filter' => ifset($filter),
            'categories_html' => $categories_html,
        ));
    }

    private function categoriesTreeHtml($categories) {
        $html = '';
        foreach ($categories as $category) {
            if ($category['status']) {
                $name = htmlspecialchars($category['name'], ENT_QUOTES, null, true);
                $url = wa()->getRouting()->getUrl('shop/frontend/category', array('category' => $category['full_url']));
                $depth = str_repeat('-- ', $category['depth'] + 1);
                $html .= <<<HTML
            <option value="{$category['id']}" data-name="{$name}" data-url="{$url}">
                {$depth} {$name}
            </option>
HTML;
                if ($category['childs']) {
                    $html .= $this->categoriesTreeHtml($category['childs']);
                }
            }
        }
        return $html;
    }

}
