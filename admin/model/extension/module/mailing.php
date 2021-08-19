<?php
class ModelExtensionModuleMailing extends Model {
    public function install() {
        $this->log('Installing module');
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mailing` (
			`mailing_id` INT(11) NOT NULL AUTO_INCREMENT,
			`mailing_category_id` INT(11) NOT NULL,
			`name` varchar(50) NOT NULL,
			`counter_letters` INT(11) NOT NULL DEFAULT 10,
			`date_start` datetime NOT NULL,
			`date_last_start` datetime DEFAULT NULL,
			`repeat` INT(11) NOT NULL,
			`date_added` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY (`mailing_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mailing_description` (
            `mailing_id` INT(11) NOT NULL,
            `language_id` INT(11) NOT NULL DEFAULT 1,
            `theme` VARCHAR(50) NOT NULL,
            PRIMARY KEY (`mailing_id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_to_mailing` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `product_id` INT(11) NOT NULL,
            `block_data_id` INT(11) NOT NULL,
            PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mailing_social_links` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `mailing_id` INT(11) NOT NULL,
            `icon_id` INT(11) NOT NULL,
            `link` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "customer_to_mailing` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `mailing_category_id` INT(11) NOT NULL,
            `customer_id` INT(11) NOT NULL,
            PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mailing_blocks` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `mailing_id` INT(11) NOT NULL,
            `grid_id` INT(11) NOT NULL,
            `bg_color` VARCHAR(10) DEFAULT NULL,
            `bg_image` VARCHAR(255) DEFAULT NULL,
            `width` INT(11) DEFAULT NULL,
            `width_type` VARCHAR(10) DEFAULT NULL,
            `padding` VARCHAR(20) DEFAULT NULL,
            `sort_ordinal` INT(11) DEFAULT NULL,
            PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mailing_blocks_data` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `block_id` INT(11) NOT NULL,
            `col_id` INT(11) NOT NULL,
            `block_grid_width` VARCHAR(20) DEFAULT NULL,
            `text` TEXT DEFAULT NULL,
            `text_ordinal` INT(11) DEFAULT NULL,
            `products_ordinal` INT(11) DEFAULT NULL,
            `products_grid_id` INT(11) NOT NULL,
            `connections_mailing_type` INT(11) NOT NULL,
            `connections_products_count` INT(11) NOT NULL,
            `connections_products_grid_id` INT(11) NOT NULL,
            `connections_ordinal` INT(11) NOT NULL,
            `bg_color` VARCHAR(10) DEFAULT NULL,
            `bg_image` VARCHAR(255) DEFAULT NULL,
            `width` INT(11) DEFAULT NULL,
            `width_type` VARCHAR(10) NOT NULL,
            `padding` VARCHAR(20) DEFAULT NULL,
            PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mailing_category` (
            `mailing_category_id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY (`mailing_category_id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "category_to_mailing` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `mailing_id` INT(11) NOT NULL,
            `category_id` INT(11) NOT NULL,
            PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "category_to_block_data` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `block_data_id` INT(11) NOT NULL,
            `category_id` INT(11) NOT NULL,
            PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mailing`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mailing_description`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "product_to_mailing`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mailing_social_links`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "customer_to_mailing`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mailing_blocks`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mailing_blocks_data`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mailing_category`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "category_to_mailing`");

        $this->log('Module uninstalled');
    }

    public function add($data) {
        if($data['count_letters'] == 0) {
            $data['count_letters'] = 10;
        }

        $this->db->query("INSERT INTO " . DB_PREFIX . "mailing SET `mailing_category_id` = '" . (int)$data['mailing_category_id'] . "', `name` = '" . $this->db->escape($data['template_name']) . "', counter_letters = '" . (int)$data['count_letters'] . "', date_start = '" . $this->db->escape($data['date_automailing']) . "', `repeat` = '" . (int)$data['repeat'] . "', date_added = NOW()");

        $mailing_id = $this->db->getLastId();

        $this->db->query("INSERT INTO " . DB_PREFIX . "mailing_description SET mailing_id = '" . (int)$mailing_id . "', language_id = '" . (int)1 . "', theme = '" . $this->db->escape($data['letter_theme']) . "'");

        if(isset($data['mailing_customers'])) {
            foreach ($data['mailing_customers'] as $customer_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "customer_to_mailing SET mailing_category_id = '" . (int)$data['mailing_category_id'] . "', customer_id = '" . (int)$customer_id . "'");
            }
        }

        if(isset($data['template_categories'])) {
            foreach ($data['template_categories'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "category_to_mailing SET mailing_id = '" . (int)$mailing_id . "', category_id = '" . (int)$category_id . "'");
            }
        }
        
        $this->cache->delete('mailing');

        return $mailing_id;
    }

    public function edit($mailing_id, $data) {
        $this->db->query("UPDATE " . DB_PREFIX . "mailing SET `mailing_category_id` = '" . (int)$data['mailing_category_id'] . "', `name` = '" . $this->db->escape($data['template_name']) . "', counter_letters = '" . $this->db->escape($data['count_letters']) . "', date_start = '" . $this->db->escape($data['date_automailing']) . "', `repeat` = '" . (int)$data['repeat'] . "' WHERE mailing_id = '" . (int)$mailing_id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "mailing_description WHERE mailing_id = '" . (int)$mailing_id . "'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "mailing_description SET mailing_id = '" . (int)$mailing_id . "', language_id = '" . (int)1 . "', theme = '" . $this->db->escape($data['letter_theme']) . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "category_to_mailing WHERE mailing_id = '" . (int)$mailing_id . "'");
        if(isset($data['template_categories'])) {
            foreach ($data['template_categories'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "category_to_mailing SET mailing_id = '" . (int)$mailing_id . "', category_id = '" . (int)$category_id . "'");
            }
        }

        $this->cache->delete('mailing');
    }

    public function editMailingStartDate($mailing_id, $date) {
        $this->db->query("UPDATE " . DB_PREFIX . "mailing SET `date_start` = '" . $this->db->escape($date) . "' WHERE mailing_id = '" . (int)$mailing_id . "'");
    }

    public function editMailingLastStartDate($mailing_id, $date) {
        $this->db->query("UPDATE " . DB_PREFIX . "mailing SET `date_last_start` = '" . $this->db->escape($date) . "' WHERE mailing_id = '" . (int)$mailing_id . "'");
    }

    public function copyMailing($mailing_id) {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "mailing m WHERE m.mailing_id = '" . (int)$mailing_id . "'");

        if ($query->num_rows) {
            $data = $query->row;

            $query = $this->getMailingDescriptions($mailing_id);
            $data['template_name'] = $data['name'];
            $data['date_automailing'] = $data['date_start'];
            $data['count_letters'] = $data['counter_letters'];
            $data['letter_theme'] = $query['letter_theme'];

            $blocks = $this->getBlocks($mailing_id);

            $new_mailing_id = $this->add($data);
            $this->copyBlocks($new_mailing_id, $blocks);

            $customers = $this->getMailingCustomersId($mailing_id);

            if(!empty($customers)) {
                foreach ($customers as $customer) {
                    $this->subscribeToMailing($new_mailing_id, $customer);
                }
            }

            $template_categories = $this->getMailingCategoryId($mailing_id);

            if (!empty($template_categories)) {
                foreach ($template_categories as $template_category_id) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "category_to_mailing SET mailing_id = '" . (int)$new_mailing_id . "', category_id = '" . (int)$template_category_id . "'");
                }
            }
        }
    }
    
    public function delete($mailing_id) {
        $blocks = $this->getBlocks($mailing_id);
        foreach ($blocks as $block) {
            $blocks_data = $this->getBlockData($block['id']);
            foreach ($blocks_data as $block_data) {
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_mailing WHERE block_data_id = '" . (int)$block_data['id'] . "'");
                $this->db->query("DELETE FROM " . DB_PREFIX . "category_to_block_data WHERE block_data_id = '" . (int)$block_data['id'] . "'");
            }
            $this->db->query("DELETE FROM " . DB_PREFIX . "mailing_blocks_data WHERE block_id = '" . (int)$block['id'] . "'");
        }
		$this->db->query("DELETE FROM " . DB_PREFIX . "mailing WHERE mailing_id = '" . (int)$mailing_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "mailing_description WHERE mailing_id = '" . (int)$mailing_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "mailing_social_links WHERE mailing_id = '" . (int)$mailing_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "mailing_blocks WHERE mailing_id = '" . (int)$mailing_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_to_mailing WHERE mailing_id = '" . (int)$mailing_id . "'");

		$this->cache->delete('mailing');
	}

    public function getMailingCategories($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "mailing_category mc";

        $sort_data = array(
            'mc.name',
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY mc.name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getMailingCategory($mailing_category_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mailing_category WHERE mailing_category_id = '" . (int)$mailing_category_id ."'");

        return $query->row;
    }

    public function addMailingCategory($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "mailing_category SET `name` = '" . $this->db->escape($data['name']) . "'");

        $mailing_id = $this->db->getLastId();

        $this->cache->delete('mailing_category');

        return $mailing_id;
    }

    public function editMailingCategory($mailing_category_id, $data) {
        $this->db->query("UPDATE " . DB_PREFIX . "mailing_category SET `name` = '" . $this->db->escape($data['name']) . "' WHERE mailing_category_id = '" . (int)$mailing_category_id ."'");

        $this->cache->delete('mailing_category');
    }

    public function deleteMailingCategory($mailing_category_id) {
        $mailing_id = $this->getMailingByMailingCategoryId($mailing_category_id);

        if ($mailing_id) {
            $this->delete($mailing_id);
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "mailing_category WHERE mailing_category_id = '" . (int)$mailing_category_id ."'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_to_mailing WHERE mailing_category_id = '" . (int)$mailing_category_id . "'");

        $this->cache->delete('mailing_category');
    }

	public function copyBlocks($mailing_id, $blocks) {
        foreach ($blocks as $data) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "mailing_blocks SET `mailing_id` = '" . $mailing_id . "', `grid_id` = '" . $data['grid_id'] . "', `bg_color` = '" . $this->db->escape($data['bg_color']) . "', `bg_image` = '" . $this->db->escape($data['bg_image']) . "', `width` = '" . (int)$data['width'] . "', `width_type` = '" . $this->db->escape($data['width_type']) . "', `padding` = '" . $this->db->escape($data['padding']) . "', `sort_ordinal` = '" . (int)$data['sort_ordinal'] . "'");
            $new_block_id = $this->db->getLastId();
            $blockData = $this->getBlockData($data['id']);

            foreach ($blockData as $bd) {
                $this->copyBlocksData($new_block_id, $bd);
            }
        }
        $this->cache->delete('mailing_blocks');
    }

    public function copyBlocksData($block_id, $data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "mailing_blocks_data SET `block_id` = '" . (int)$block_id . "', `col_id` = '" . (int)$data['col_id'] . "', `block_grid_width` = '" . $this->db->escape($data['block_grid_width']) . "',`text` = '" . $this->db->escape($data['text']) . "', `text_ordinal` = '" . (int)$data['text_ordinal'] . "', `products_ordinal` = '" . (int)$data['products_ordinal'] . "', `products_grid_id` = '" . (int)$data['products_grid_id'] . "', `connections_mailing_type` = '" . (int)$data['connections_mailing_type'] . "', `connections_products_count` = '" . (int)$data['connections_products_count'] . "', `connections_products_grid_id` = '" . (int)$data['connections_products_grid_id'] . "', `connections_ordinal` = '" . (int)$data['connections_ordinal'] . "', `bg_color` = '" . $this->db->escape($data['bg_color']) . "', `bg_image` = '" . $this->db->escape($data['bg_image']) . "', `width` = '" . (int)$data['width'] . "', `width_type` = '" . $this->db->escape($data['width_type']) . "', `padding` = '" . $this->db->escape($data['padding']) . "'");

        $new_block_data_id = $this->db->getLastId();

        if(isset($data['products'])) {
            foreach ($data['products'] as $added_product_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_mailing SET product_id = '" . (int)$added_product_id['product_id'] . "', block_data_id = '" . (int)$new_block_data_id . "'");
            }
        }

        if(isset($data['categories'])) {
            foreach ($data['categories'] as $category) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "category_to_block_data SET block_data_id = '" . (int)$new_block_data_id . "', category_id = '" . (int)$category['category_id'] . "'");
            }
        }

        $this->cache->delete('mailing_blocks_data');
        $this->cache->delete('product_to_mailing');
    }

    public function addBlock($mailing_id, $grid_id) {
        $query = $this->db->query("SELECT MAX(`sort_ordinal`) as max_ordinal FROM " . DB_PREFIX . "mailing_blocks WHERE `mailing_id` = '" . (int)$mailing_id . "'");
        $max_ordinal = (int)$query->row['max_ordinal'] + 1;

        $this->db->query("INSERT INTO " . DB_PREFIX . "mailing_blocks SET `mailing_id` = '" . (int)$mailing_id . "', `grid_id` = '" . (int)$grid_id . "', `sort_ordinal` = '" . $max_ordinal . "'");

        $block_id = $this->db->getLastId();

        $this->cache->delete('mailing_blocks');

        return $block_id;
    }

    public function editBlock($block_id, $data) {
        $this->db->query("UPDATE " . DB_PREFIX . "mailing_blocks SET `bg_color` = '" . $this->db->escape($data['bg_color']) . "', `bg_image` = '" . $this->db->escape($data['bg_image']) . "', `width` = '" . (int)$data['width'] . "', `width_type` = '" . $this->db->escape($data['width_type']) . "', `padding` = '" . $this->db->escape($data['padding']) . "', `sort_ordinal` = '" . (int)$data['sort_ordinal'] . "' WHERE `id` = '" . (int)$block_id . "'");

        $this->cache->delete('mailing_blocks');
    }

    public function deleteBlock($block_id) {
        $blocks_data = $this->getBlockData($block_id);
        foreach ($blocks_data as $block_data) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_mailing WHERE block_data_id = '" . (int)$block_data['id'] . "'");
            $this->db->query("DELETE FROM " . DB_PREFIX . "category_to_block_data WHERE block_data_id = '" . (int)$block_data['id'] . "'");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "mailing_blocks WHERE `id` = '" . (int)$block_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "mailing_blocks_data WHERE `block_id` = '" . (int)$block_id . "'");

        $this->cache->delete('mailing_blocks');
        $this->cache->delete('mailing_blocks_data');
    }

    public function getBlocks($mailing_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mailing_blocks WHERE `mailing_id` = '" . (int)$mailing_id . "' ORDER BY `sort_ordinal` ASC");

        return $query->rows;
    }

    public function getBlock($block_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mailing_blocks WHERE `id` = '" . (int)$block_id . "'");

        return $query->row;
    }

    public function addBlockData($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "mailing_blocks_data SET `block_id` = '" . (int)$data['block_id'] . "', `col_id` = '" . (int)$data['col_id'] . "', `block_grid_width` = '" . $this->db->escape($data['block_grid_width']) . "', `text` = '" . $this->db->escape($data['block_data']['text']) . "', `text_ordinal` = '" . (int)$data['block_data']['text_ordinal'] . "', `products_ordinal` = '" . (int)$data['block_data']['products_ordinal'] . "', `products_grid_id` = '" . (((int)$data['block_data']['products_grid_id']) ? (int)$data['block_data']['products_grid_id'] : 1) . "', `connections_mailing_type` = '" . (int)$data['block_data']['connections_mailing_type'] . "', `connections_products_count` = '" . (int)$data['block_data']['connections_products_count'] . "', `connections_products_grid_id` = '" . (((int)$data['block_data']['connections_products_grid_id']) ? (int)$data['block_data']['connections_products_grid_id'] : 1) . "', `connections_ordinal` = '" . (int)$data['block_data']['connections_ordinal'] . "', `bg_color` = '" . $this->db->escape($data['block_data']['bg_color']) . "', `bg_image` = '" . $this->db->escape($data['block_data']['bg_image']) . "', `width` = '" . (int)$data['block_data']['width'] . "', `width_type` = '" . $this->db->escape($data['block_data']['width_type']) . "', `padding` = '" . $this->db->escape($data['block_data']['padding']) . "'");

        $block_data_id = $this->db->getLastId();

//        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_mailing WHERE mailing_id = '" . (int)$mailing_id . "'");
        if(isset($data['added_products_id'])) {
            foreach ($data['added_products_id'] as $added_product_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_mailing SET product_id = '" . (int)$added_product_id . "', block_data_id = '" . (int)$block_data_id . "'");
            }
        }

        if(isset($data['block_categories_display'])) {
            foreach ($data['block_categories_display'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "category_to_block_data SET block_data_id = '" . (int)$block_data_id . "', category_id = '" . (int)$category_id . "'");
            }
        }

        $this->cache->delete('mailing_blocks_data');
        $this->cache->delete('product_to_mailing');

        return $block_data_id;
    }

    public function editBlockData($data) {
        $this->db->query("UPDATE " . DB_PREFIX . "mailing_blocks_data SET `text` = '" . $this->db->escape($data['block_data']['text']) . "', `text_ordinal` = '" . (int)$data['block_data']['text_ordinal'] . "', `products_ordinal` = '" . (int)$data['block_data']['products_ordinal'] . "', `products_grid_id` = '" . (int)$data['block_data']['products_grid_id'] . "', `connections_mailing_type` = '" . (int)$data['block_data']['connections_mailing_type'] . "', `connections_products_count` = '" . (int)$data['block_data']['connections_products_count'] . "', `connections_products_grid_id` = '" . (int)$data['block_data']['connections_products_grid_id'] . "', `connections_ordinal` = '" . (int)$data['block_data']['connections_ordinal'] . "', `bg_color` = '" . $this->db->escape($data['block_data']['bg_color']) . "', `bg_image` = '" . $this->db->escape($data['block_data']['bg_image']) . "', `width` = '" . (int)$data['block_data']['width'] . "', `width_type` = '" . $this->db->escape($data['block_data']['width_type']) . "', `padding` = '" . $this->db->escape($data['block_data']['padding']) . "' WHERE `id` = '" . (int)$data['block_data_id'] . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_mailing WHERE `block_data_id` = '" . (int)$data['block_data_id'] . "'");
        if(isset($data['added_products_id'])) {
            foreach ($data['added_products_id'] as $added_product_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_mailing SET product_id = '" . (int)$added_product_id . "', block_data_id = '" . (int)$data['block_data_id'] . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "category_to_block_data WHERE `block_data_id` = '" . (int)$data['block_data_id'] . "'");
        if(isset($data['block_categories_display'])) {
            foreach ($data['block_categories_display'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "category_to_block_data SET block_data_id = '" . (int)$data['block_data_id'] . "', category_id = '" . (int)$category_id . "'");
            }
        }

        $this->cache->delete('mailing_blocks_data');
        $this->cache->delete('product_to_mailing');
    }

    public function deleteBlockData($block_data_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "mailing_blocks_data WHERE `id` = '" . (int)$block_data_id . "'");

        $this->cache->delete('mailing_blocks_data');
    }

    public function getBlockData($block_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mailing_blocks_data WHERE `block_id` = '" . (int)$block_id . "'");

        $block_data_info = $query->rows;

        foreach ($block_data_info as $k => $block_data) {
            $query2 = $this->db->query("SELECT `product_id` FROM " . DB_PREFIX . "product_to_mailing WHERE `block_data_id` = '" . (int)$block_data['id'] . "'");
            $block_data_info[$k]['products'] = $query2->rows;
        }

        foreach ($block_data_info as $k => $block_data) {
            $query3 = $this->db->query("SELECT `category_id` FROM " . DB_PREFIX . "category_to_block_data WHERE `block_data_id` = '" . (int)$block_data['id'] . "'");
            $block_data_info[$k]['categories'] = $query3->rows;
        }

        return $block_data_info;
    }

    public function getBlockDataByBlockDataId($block_data_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mailing_blocks_data WHERE `id` = '" . (int)$block_data_id . "'");

        $block_data_info = $query->row;

        $query2 = $this->db->query("SELECT `product_id` FROM " . DB_PREFIX . "product_to_mailing WHERE `block_data_id` = '" . (int)$block_data_id . "'");
        $block_data_info['products'] = $query2->rows;

        $query3 = $this->db->query("SELECT `category_id` FROM " . DB_PREFIX . "category_to_block_data WHERE `block_data_id` = '" . (int)$block_data_id . "'");
        $block_data_info['categories'] = $query3->rows;

        return $block_data_info;
    }

    public function getMailings($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "mailing m LEFT JOIN " . DB_PREFIX . "mailing_description md ON (m.mailing_id = md.mailing_id) WHERE md.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        $sort_data = array(
            'm.mailing_id',
            'date_added',
            'm.name',
            'date_start'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY m.mailing_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
        }
        
        $query = $this->db->query($sql);

		return $query->rows;
    }

    public function getMailingsAutocomplete($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "mailing WHERE `name` LIKE '%" . $this->db->escape($data['filter_name']) . "%'";

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getMailing($mailing_id) {
		$mailing_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mailing WHERE mailing_id = '" . (int)$mailing_id . "'");

		foreach ($query->rows as $result) {
			$mailing_data = array(
			    'mailing_id'       => $result['mailing_id'],
			    'mailing_category_id' => $result['mailing_category_id'],
				'name'             => $result['name'],
				'counter_letters'  => $result['counter_letters'],
				'date_start'       => date('Y-m-d\TH:i', strtotime($result['date_start'])),
				'repeat'           => $result['repeat'],
                'date_added'       => $result['date_added']
			);
		}

		return $mailing_data;
	}

    public function getMailingDescriptions($mailing_id) {
		$mailing_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mailing_description WHERE mailing_id = '" . (int)$mailing_id . "'");

		foreach ($query->rows as $result) {
			$mailing_description_data = array(
				'letter_theme'     => $result['theme'],
			);
		}

		return $mailing_description_data;
	}

	public function getMailingProducts($mailing_id) {
        $mailing_products_data = array();

        $sql = "SELECT p.product_id, p.image, p.price, pd.name FROM " . DB_PREFIX . "product p JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) JOIN " . DB_PREFIX . "product_to_mailing ptm ON (pd.product_id = ptm.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ptm.mailing_id = '" . (int)$mailing_id . "'";

        $query = $this->db->query($sql);

		return $query->rows;
	}

	public function getMailingSocialLinks($mailing_id) {
        $social_links = array();
        $query = $this->db->query("SELECT icon_id, link FROM " . DB_PREFIX . "mailing_social_links WHERE mailing_id = '" . (int)$mailing_id . "'");

        foreach ($query->rows as $result) {
            $social_links[] = array(
                'icon_id' => intval($result['icon_id']),
                'link'    => $result['link']
            );
        }

        return $social_links;
    }

    public function getMailingCustomersId($mailing_category_id) {
        $query = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customer_to_mailing WHERE mailing_category_id = '" . (int)$mailing_category_id . "'");

        $new_array = array();

        // make array as need for us
        foreach ($query->rows as $key => $row) {  // loop over the array of arrays
            foreach ($row as $kkey => $value) {  // loop over each sub-array (even if just 1 item)
                $new_array[$key] = $value;      // set the output array key to the value
            }
        }

        return $new_array;
    }

    public function getCustomersMail($customers_id) {
        $customer_mails_temp = array();
        $customer_mails = array();

        foreach($customers_id as $k => $v) {
            $query = $this->db->query("SELECT email FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$v . "'");
            $customer_mails_temp[] = $query->rows[0];
        }

        foreach ($customer_mails_temp as $key => $row) {  // loop over the array of arrays
            foreach ($row as $kkey => $value) {  // loop over each sub-array (even if just 1 item)
                $customer_mails[$key] = $value;      // set the output array key to the value
            }
        }

        return $customer_mails;
    }

    public function getMailingByMailingCategoryId($mailing_category_id) {
        $query = $this->db->query("SELECT mailing_id FROM `" . DB_PREFIX . "mailing` WHERE mailing_category_id = '" . (int)$mailing_category_id . "'");

        return isset($query->row['mailing_id']) ? $query->row['mailing_id'] : false;
    }

    public function getMailingCategoryId($mailing_id) {
        $query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category_to_mailing WHERE mailing_id = '" . (int)$mailing_id . "'");

        $new_array = array();

        // make array as need for us
        foreach ($query->rows as $key => $row) {  // loop over the array of arrays
            foreach ($row as $kkey => $value) {  // loop over each sub-array (even if just 1 item)
                $new_array[$key] = (int)$value;      // set the output array key to the value
            }
        }

        return $new_array;
    }

    public function unsubscribe($customer_id) {
        $this->db->query("UPDATE " . DB_PREFIX . "customer SET newsletter = 0 WHERE customer_id = '" . (int)$customer_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_to_mailing WHERE customer_id = '" . (int)$customer_id . "'");
    }

    public function unsubscribeFromMailing($mailing_category_id, $customer_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_to_mailing WHERE mailing_category_id = '" . (int)$mailing_category_id . "' AND customer_id = '" . (int)$customer_id . "'");
    }

    public function subscribeToMailing($mailing_category_id, $customer_id) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer_to_mailing SET mailing_category_id = '" . (int)$mailing_category_id . "', customer_id = '" . (int)$customer_id . "'");
    }

    public function unsubscribeAllFromMailing($mailing_category_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_to_mailing WHERE mailing_category_id = '" . (int)$mailing_category_id . "'");
    }

    public function subcribeAllToMailing($mailing_category_id) { // add s to name
        $query = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customer WHERE newsletter = 1");
        $queryy = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customer_to_mailing WHERE mailing_category_id = '" . $mailing_category_id ."'");

        $customers = array();
        foreach ($queryy->rows as $key => $row) {
            $customers[] = (int)$row['customer_id'];
        }

        foreach ($query->rows as $row) {
            if(!in_array($row['customer_id'], $customers)) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "customer_to_mailing SET mailing_category_id = '" . (int)$mailing_category_id . "', customer_id = '" . (int)$row['customer_id'] . "'");
            }
        }
    }

    public function getCustomers($data = array()) {
        $sql = "SELECT *, CONCAT(c.firstname, ' ', c.lastname) AS name, cgd.name AS customer_group FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id)";

        if (!empty($data['filter_affiliate'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "customer_affiliate ca ON (c.customer_id = ca.customer_id)";
        }

        $sql .= " WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        $implode = array();

        if (!empty($data['filter_name'])) {
            $implode[] = "CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (!empty($data['filter_email'])) {
            $implode[] = "c.email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
        }

        if (isset($data['filter_newsletter']) && !is_null($data['filter_newsletter'])) {
            $implode[] = "c.newsletter = '" . (int)$data['filter_newsletter'] . "'";
        }

        if (!empty($data['filter_customer_group_id'])) {
            $implode[] = "c.customer_group_id = '" . (int)$data['filter_customer_group_id'] . "'";
        }

        if (!empty($data['filter_affiliate'])) {
            $implode[] = "ca.status = '" . (int)$data['filter_affiliate'] . "'";
        }

        if (!empty($data['filter_ip'])) {
            $implode[] = "c.customer_id IN (SELECT customer_id FROM " . DB_PREFIX . "customer_ip WHERE ip = '" . $this->db->escape($data['filter_ip']) . "')";
        }

        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $implode[] = "c.status = '" . (int)$data['filter_status'] . "'";
        }

        if (!empty($data['filter_date_added'])) {
            $implode[] = "DATE(c.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
        }

        if ($implode) {
            $sql .= " AND " . implode(" AND ", $implode);
        }

        $sort_data = array(
            'name',
            'c.email',
            'customer_group',
            'c.status',
            'c.ip',
            'c.date_added'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getCategories($product_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

        return $query->rows;
    }

    public function getCategoriesForTree() {
        $sql = "SELECT cp.category_id AS `id`, cd2.name AS name, c1.parent_id FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        $sql .= " GROUP BY cp.category_id";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getSelectedCategoriesForTree($mailing_id) {
        $sql = "SELECT cp.category_id AS `id`, cd2.name AS name, c1.parent_id FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) LEFT JOIN " . DB_PREFIX . "category_to_mailing ctm ON (cp.category_id = ctm.category_id) WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ctm.mailing_id = '" . (int)$mailing_id . "'";

        $sql .= " GROUP BY cp.category_id";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getCategoryDescription($category_id) {
        $query = $this->db->query("SELECT category_id, `name` FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') ."'");

        return $query->row;
    }

    public function getLastProductsByCategory($category_id, $limit = 10) {
        $query = $this->db->query("SELECT p.product_id, p.price, p.image, pd.name, p2c.category_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2c.category_id = '" . (int)$category_id . "' ORDER BY p.product_id DESC LIMIT " . (int)$limit);

        return $query->rows;
    }

    public function getLastProductsByCategoryAndDate($category_id, $date, $limit) {
        $query = $this->db->query("SELECT p.product_id, p.price, p.image, pd.name, p2c.category_id, p.date_added FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2c.category_id = '" . (int)$category_id . "' AND p.date_added > '" . $date . "' LIMIT " . (int)$limit);

        return $query->rows;
    }

    // Function for get all categories path
    public function getCategoryPathHighestLevel($category_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_path WHERE category_id = '" . (int)$category_id . "' ORDER BY `level`, `path_id`");

        return $query->rows;
    }

    public function log($data) {
        // if ($this->config->has('payment_stripe_logging') && $this->config->get('payment_stripe_logging')) {
        $log = new Log('mailing.log');

        $log->write($data);
        // }
    }
}