<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopFilterPluginFilterDeleteController extends waJsonController {

    public function execute() {
        try {
            $id = waRequest::post('id', 0, waRequest::TYPE_INT);
            if (!$id) {
                throw new Exception('Ошибка передачи данных');
            }
            $filter_model = new shopFilterPluginModel();
            $filter_model->deleteById($id);
        } catch (Exception $ex) {
            $this->setError($ex->getMessage());
        }
    }

}
