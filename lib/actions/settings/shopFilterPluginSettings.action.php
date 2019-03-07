<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopFilterPluginSettingsAction extends waViewAction {

    public function execute() {
        $this->view->assign(array(
            'lang' => substr(wa()->getLocale(), 0, 2),
            'plugin' => wa()->getPlugin('filter'),
            'route_hashs' => shopFilterRouteHelper::getRouteHashs(),
        ));
    }

}
