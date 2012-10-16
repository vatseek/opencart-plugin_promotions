<?php
/**
 * ModelModulePromotion
 *
 * Manage all admin promotion operation
 */
class ModelModulePromotion extends Model {

    /**
     * Save promotion data to DB
     *
     * @param array $promotionData
     * @return bool is_success
     */
    public function savePromotion($promotionData)
    {
        if ( isset($promotionData['promotion_id']) && isset($promotionData['title']) && isset($promotionData['status']) && isset($promotionData['picture']) && isset($promotionData['order']) && isset($promotionData['position'])) {
            if (!$promotionData['promotion_id']) {
               $promotionData['promotion_id'] = 'NULL';
            }

            $lastInsertedId = (int)$promotionData['promotion_id'];
            $dataTitle = $this->db->escape($promotionData['title']);
            $dataPicture = $this->db->escape($promotionData['picture']);
            $dataPosition = $this->db->escape($promotionData['position']);
            $dataOrder = (int)$promotionData['order'];
            $dataStatus = (int)$promotionData['status'];

            $query = "INSERT INTO `" . DB_PREFIX . "promotion` SET  ";
            $subQuery = "`promotion_id` =  {$lastInsertedId}, ";
            $subQuery .= "`title` =  '{$dataTitle}', ";
            $subQuery .= "`status` = '{$dataStatus}', ";
            $subQuery .= "`picture` = '{$dataPicture}', ";
            $subQuery .= "`order` = '{$dataOrder}', ";
            $subQuery .= "`position` = '{$dataPosition}' ";
            $query .= $subQuery ." ON DUPLICATE KEY UPDATE ". $subQuery;
            $this->db->query($query);

            if ($this->db->getLastId()) {
                $lastInsertedId = $this->db->getLastId();
            }

            $this->savePromotionDescription($promotionData['promotion_description'], $lastInsertedId );

            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Save promotion descriptions
     *
     * @param array $promotionDescriptionData
     * @param int $promotionId
     * @return bool is_success
     */
    public function savePromotionDescription($promotionDescriptionData, $promotionId)
    {
        $query = "DELETE FROM `" . DB_PREFIX . "promotion_description` WHERE promotion_id = '{$promotionId}'";
        $result = $this->db->query($query);

        foreach($promotionDescriptionData as $language_id =>$description) {

            $dataDescriptionId = 'NULL';
            $dataPromotionId = $promotionId;
            $dataName = $this->db->escape($description['name']);
            $dataDescription = $this->db->escape($description['description']);
            $dataMetaDescription = $this->db->escape($description['meta_description']);
            $dataImage = $this->db->escape($description['image']);
            $dataBannerId = 0;

            $query = "INSERT INTO `" . DB_PREFIX . "promotion_description` SET ";
            $subQuery = "`description_id` =  {$dataDescriptionId}, ";
            $subQuery .= "`promotion_id` =  '{$dataPromotionId}', ";
            $subQuery .= "`name` = '{$dataName}', ";
            $subQuery .= "`language_id` = '{$language_id}', ";
            $subQuery .= "`description` = '{$dataDescription}', ";
            $subQuery .= "`meta_description` = '{$dataMetaDescription}', ";
            $subQuery .= "`image` = '{$dataImage}', ";
            $subQuery .= "`banner_id` = '{$dataBannerId}' ";
            $query .= $subQuery ." ON DUPLICATE KEY UPDATE ". $subQuery;

            $this->db->query($query);
        }

        return false;
    }

    /**
     * @param array $promotionsList list of promotions IDs to deleted
     */
    public function deletePromotions($promotionsList)
    {
        $subQuery = '(' . implode ($promotionsList, ',') . ')';

        $query = "DELETE FROM `" . DB_PREFIX . "promotion` WHERE promotion_id IN {$subQuery}";
        $this->db->query($query);

        $query = "DELETE FROM `" . DB_PREFIX . "promotion_description` WHERE promotion_id IN {$subQuery}";
        $this->db->query($query);
    }

    /**
     * get promotions list
     *
     * @param bool $activeOnly (true is select only active promotions)
     * @return array promotions data
     */
    public function getPromotions($activeOnly = false)
    {
        $query = "SELECT * FROM `" . DB_PREFIX . "promotion` AS _p";
        if ($activeOnly) {
            $query .= "WHERE `_p.status` = '1'; ";
        }
        $result = $this->db->query($query);

        return $result->rows;
    }

    /**
     * @param int $promotionId ID of promotion
     * @return array Promotion data
     */
    public function getPromotionById($promotionId)
    {
        $query = "SELECT * FROM `" . DB_PREFIX . "promotion` AS _p WHERE promotion_id='{$promotionId}'";
        $result = $this->db->query($query);

        return $result->row;
    }

    /**
     * @param int $promotionId
     * @return array Promotion description data
     */
    public function getPromotionDescriptions($promotionId)
    {
        $promotionSortedDescription = array();
        $query = "SELECT * FROM `" . DB_PREFIX . "promotion_description` AS _p WHERE promotion_id='{$promotionId}'";
        $result = $this->db->query($query);

        foreach ($result->rows as $promotionDescription) {
            $promotionSortedDescription[$promotionDescription['language_id']] = $promotionDescription;
        }

        return $promotionSortedDescription;
    }

    /**
     * @param string $tableName table name
     * @return array fields list
     */
    public function getTableDBFields($tableName)
    {
        $fieldNames = array();
        $query = "DESC `" . DB_PREFIX . "{$tableName}`; ";
        $result = $this->db->query($query);

        foreach($result->rows as $field) {
            $fieldNames[$field['Field']] = '';
        }

        return $fieldNames;
    }

    /**
     * installing of tables on module install
     */
    public function install()
    {
        $query = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "promotion` (
            `promotion_id` INT(10) NOT NULL AUTO_INCREMENT,
	        `title` VARCHAR(255),
	        `status` TINYINT(1) NOT NULL DEFAULT '0',
        	`picture` VARCHAR(255),
	        `position` VARCHAR(60),
	        `order` TINYINT(3) NOT NULL DEFAULT '0',
	        `banner_id` TINYINT(3) NOT NULL DEFAULT '0',
	        PRIMARY KEY `promotion_id`(`promotion_id`)
            )ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
        $this->db->query($query);

        $query = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "promotion_description` (
            `description_id` INT(10) NOT NULL AUTO_INCREMENT,
	        `promotion_id` INT(10) NOT NULL DEFAULT '0',
	        `language_id` INT(10) NOT NULL DEFAULT '0',
	        `name` VARCHAR(255) NOT NULL,
	        `description` TEXT,
	        `meta_description` VARCHAR(255) NOT NULL DEFAULT '',
	        `image` VARCHAR(255)  NOT NULL DEFAULT '',
	        `banner_id` TINYINT(3) NOT NULL DEFAULT '0',
	        PRIMARY KEY `description_id` (`description_id`),
	        UNIQUE KEY (`promotion_id`, `language_id`),
	        KEY `language_id`(`language_id`)
            )ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
        $this->db->query($query);

        $result = $this->db->query("DESC `" . DB_PREFIX . "product`;");

        $columnExists = false;
        foreach ($result->rows as $column) {
            if($column['Field'] == 'promotion_id') {
                $columnExists = true;
            }
        }

        if (!$columnExists) {
            $query = "ALTER TABLE `" . DB_PREFIX . "product` ADD COLUMN `promotion_id` INT(10) NOT NULL DEFAULT '0'; ";
            $this->db->query($query);
        }

        //rename file to enabled for vqmod
        $path = str_replace('system/config','vqmod/xml',DIR_CONFIG);
        rename($path . 'promotion.bak',$path . 'promotion.xml');
    }

    /**
     * uninstall module operations
     */
    public function uninstall()
    {
        //rename file to disables for vqmod
        $path = str_replace('system/config','vqmod/xml',DIR_CONFIG);
        rename($path . 'promotion.xml',$path . 'promotion.bak');
    }
}