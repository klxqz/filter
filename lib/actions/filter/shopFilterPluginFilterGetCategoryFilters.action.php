<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopFilterPluginFilterGetCategoryFiltersAction extends waViewAction {

    public function execute() {
        $id = waRequest::post('id', 0, waRequest::TYPE_INT);
        $category_model = new shopCategoryModel();
        $category = $category_model->getById($id);

        if ($category['filter']) {
            $collection = new shopProductsCollection('category/' . $category['id']);
            $filter_ids = explode(',', $category['filter']);

            $feature_model = new shopFeatureModel();
            $features = $feature_model->getById(array_filter($filter_ids, 'is_numeric'));
            if ($features) {
                $features = $feature_model->getValues($features);
            }
            $category_value_ids = $collection->getFeatureValueIds();

            $filters = array();
            foreach ($filter_ids as $fid) {
                if ($fid == 'price') {
                    $range = $collection->getPriceRange();
                    if ($range['min'] != $range['max']) {
                        $filters['price'] = array(
                            'min' => shop_currency($range['min'], null, null, false),
                            'max' => shop_currency($range['max'], null, null, false),
                        );
                    }
                } elseif (isset($features[$fid]) && isset($category_value_ids[$fid])) {
                    $filters[$fid] = $features[$fid];
                    $min = $max = $unit = null;
                    foreach ($filters[$fid]['values'] as $v_id => $v) {
                        if (!in_array($v_id, $category_value_ids[$fid])) {
                            unset($filters[$fid]['values'][$v_id]);
                        } else {
                            if ($v instanceof shopRangeValue) {
                                $begin = $this->getFeatureValue($v->begin);
                                if ($min === null || $begin < $min) {
                                    $min = $begin;
                                }
                                $end = $this->getFeatureValue($v->end);
                                if ($max === null || $end > $max) {
                                    $max = $end;
                                    if ($v->end instanceof shopDimensionValue) {
                                        $unit = $v->end->unit;
                                    }
                                }
                            } else {
                                $tmp_v = $this->getFeatureValue($v);
                                if ($min === null || $tmp_v < $min) {
                                    $min = $tmp_v;
                                }
                                if ($max === null || $tmp_v > $max) {
                                    $max = $tmp_v;
                                    if ($v instanceof shopDimensionValue) {
                                        $unit = $v->unit;
                                    }
                                }
                            }
                        }
                    }
                    if (!$filters[$fid]['selectable'] && ($filters[$fid]['type'] == 'double' ||
                            substr($filters[$fid]['type'], 0, 6) == 'range.' ||
                            substr($filters[$fid]['type'], 0, 10) == 'dimension.')) {
                        if ($min == $max) {
                            unset($filters[$fid]);
                        } else {
                            $type = preg_replace('/^[^\.]*\./', '', $filters[$fid]['type']);
                            if ($type != 'double') {
                                $filters[$fid]['base_unit'] = shopDimension::getBaseUnit($type);
                                $filters[$fid]['unit'] = shopDimension::getUnit($type, $unit);
                                if ($filters[$fid]['base_unit']['value'] != $filters[$fid]['unit']['value']) {
                                    $dimension = shopDimension::getInstance();
                                    $min = $dimension->convert($min, $type, $filters[$fid]['unit']['value']);
                                    $max = $dimension->convert($max, $type, $filters[$fid]['unit']['value']);
                                }
                            }
                            $filters[$fid]['min'] = $min;
                            $filters[$fid]['max'] = $max;
                        }
                    }
                }
            }
        }

        $filter_id = waRequest::post('filter_id');
        $filter_model = new shopFilterPluginModel();
        $filter = $filter_model->getById($filter_id);
        $filter['conditions'] = shopFilterHelper::parseConditions($filter['conditions']);


        $this->view->assign(array(
            'category_id' => $category['id'],
            'currency' => wa()->getConfig()->getCurrency(),
            'filter' => $filter,
            'filters' => ifset($filters, array()),
        ));
    }

    protected function getFeatureValue($v) {
        if ($v instanceof shopDimensionValue) {
            return $v->value_base_unit;
        }
        if (is_object($v)) {
            return $v->value;
        }
        return $v;
    }

}
