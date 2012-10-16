<?php
/**
 * ModelModulePromotion
 *
 * @copyright  2012 Stfalcon (http://stfalcon.com/)
 */
class ModelModulePromotion extends Model {

    /**
     * returned all promotions
     *
     * @return array promotions data list
     */
    public function getPromotions() {
        $currentLanguageId = (int)$this->config->get('config_language_id');

		$query = "SELECT _p.promotion_id, _pd.description, _pd.meta_description, _pd.image  FROM `" . DB_PREFIX . "promotion` AS _p ";
        $query .= "INNER JOIN `" . DB_PREFIX . "promotion_description` AS _pd ";
        $query .= "ON _pd.promotion_id = _p.promotion_id AND _pd.language_id = '{$currentLanguageId}' ";
        $query .= "WHERE _p.status <> 0 AND LENGTH(_pd.image ) > 0;";

        $result = $this->db->query($query);
        $promotionsList = $result->rows;

        foreach ($promotionsList as $arrayId => $promotion ) {
            if (!file_exists(DIR_IMAGE.$promotion['image'])) {
                unset($promotionsList[$arrayId]);
            }
        }
		return $promotionsList;
	}

    /**
     * return promotion by ID
     *
     * @param int $promotionId ID of promotion
     * @return mixed
     */
    public function getPromotion($promotionId) {
        $currentLanguageId = (int)$this->config->get('config_language_id');
        $promotionId += 0;

        $query = "SELECT _p.*, _pd.*  FROM `" . DB_PREFIX . "promotion` AS _p ";
        $query .= "INNER JOIN `" . DB_PREFIX . "promotion_description` AS _pd ";
        $query .= "ON _pd.promotion_id = _p.promotion_id AND _pd.language_id = '{$currentLanguageId}' ";
        $query .= "WHERE _p.promotion_id = {$promotionId}";
        $result = $this->db->query($query);

        return $result->row;
    }

    /**
     * get all products of promotion
     *
     * @param int $promotionId
     * @return array products data list
     */
    public function getProductsOfPromotion( $promotionId )
    {
        $currentLanguageId = (int)$this->config->get('config_language_id');
        $promotionId += 0;

        if ($this->customer->isLogged()) {
            $customer_group_id = $this->customer->getCustomerGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        $query = "SELECT _p.*, _pd.*, _ps.price AS special  FROM `" . DB_PREFIX . "product` AS _p ";
        $query .= "INNER JOIN `" . DB_PREFIX . "product_description` AS _pd ";
        $query .= "ON _pd.product_id = _p.product_id AND _pd.language_id = '{$currentLanguageId}' ";
        $query .= "LEFT JOIN (SELECT product_id , MIN(priority) as prior FROM `" . DB_PREFIX . "product_special` GROUP BY product_id) _sub  ON _sub.product_id = _p.product_id ";
        $query .= "LEFT JOIN product_special AS _ps ON _ps.product_id = _p.product_id AND _ps.priority = _sub.prior ";
        $query .= "WHERE _p.promotion_id = {$promotionId} ";

        $result = $this->db->query($query);

        return $result->rows;
    }

    /**
     * return promotion banner settings such as height, width etc.. false is banner not set
     *
     * @return array|bool promotion banner settings
     */
    public function getPromotionSettings() {
        $this->load->model('setting/setting');
        $promotion = $this->model_setting_setting->getSetting('promotion');

        $settings = array();
        if (isset($promotion['promotion_module'][0]['width']) && isset($promotion['promotion_module'][0]['height'])) {
            $settings['height'] = $promotion['promotion_module'][0]['height'];
            $settings['width'] = $promotion['promotion_module'][0]['width'];
            $settings['image'] = $promotion['promotion_module'][0]['image'];

            return $settings;
        }
        else {
            return false;
        }
    }

}
?>