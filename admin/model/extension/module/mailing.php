<?php
class ModelExtensionModuleMailing extends Model {
    public function install() {
        $this->log('Installing module');
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mailing` (
			`mailing_id` INT(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(50) NOT NULL,
			`counter_letters` INT(11) NOT NULL DEFAULT 10,
			`date_start` datetime NOT NULL,
			`date_added` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY (`mailing_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mailing_description` (
            `mailing_id` INT(11) NOT NULL,
            `language_id` INT(11) NOT NULL DEFAULT 1,
            `theme` VARCHAR(50) NOT NULL,
            `text` TEXT NOT NULL,
            PRIMARY KEY (`mailing_id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_to_mailing` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `product_id` INT(11) NOT NULL,
            `mailing_id` INT(11) NOT NULL,
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
            `mailing_id` INT(11) NOT NULL,
            `customer_id` INT(11) NOT NULL,
            PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "social_icons` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `icon_id` INT(11) NOT NULL,
            `name` VARCHAR(50) NOT NULL,
            `image` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        $this->db->query("INSERT INTO " . DB_PREFIX . "social_icons SET `icon_id` = '1', `name` = 'Facebook', `image` = 'social/facebook.png'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "social_icons SET `icon_id` = '2', `name` = 'Instagram', `image` = 'social/instagram.png'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "social_icons SET `icon_id` = '3', `name` = 'OK', `image` = 'social/odnoklassniki.png'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "social_icons SET `icon_id` = '4', `name` = 'Twitter', `image` = 'social/twitter.png'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "social_icons SET `icon_id` = '5', `name` = 'VK', `image` = 'social/VK.png'");
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mailing`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mailing_description`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "product_to_mailing`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mailing_social_links`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "customer_to_mailing`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "social_icons`");

        $this->log('Module uninstalled');
    }

    public function add($data) {
        if($data['count_letters'] == 0) {
            $data['count_letters'] = 10;
        }

        $this->db->query("INSERT INTO " . DB_PREFIX . "mailing SET `name` = '" . $this->db->escape($data['template_name']) . "', counter_letters = '" . (int)$data['count_letters'] . "', date_start = '" . $this->db->escape($data['date_automailing']) . "', date_added = NOW()");

        $mailing_id = $this->db->getLastId();

        $this->db->query("INSERT INTO " . DB_PREFIX . "mailing_description SET mailing_id = '" . (int)$mailing_id . "', language_id = '" . (int)1 . "', theme = '" . $this->db->escape($data['letter_theme']) . "', text = ' '");

        if(isset($data['added_products_id'])) {
			foreach ($data['added_products_id'] as $added_product_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_mailing SET product_id = '" . (int)$added_product_id . "', mailing_id = '" . (int)$mailing_id . "'");
			}
        }

        if(isset($data['social_link'])) {
            foreach ($data['social_link'] as $key => $value) {
                if($value != '') {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "mailing_social_links SET mailing_id = '" . (int)$mailing_id . "', icon_id = '" . ($key + 1) . "', link = '" . $value . "'");
                }
            }
        }

        if(isset($data['mailing_customers'])) {
            foreach ($data['mailing_customers'] as $customer_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "customer_to_mailing SET mailing_id = '" . (int)$mailing_id . "', customer_id = '" . (int)$customer_id . "'");
            }
        }
        
        $this->cache->delete('mailing');

        return $mailing_id;
    }

    public function edit($mailing_id, $data) {
        $this->db->query("UPDATE " . DB_PREFIX . "mailing SET `name` = '" . $this->db->escape($data['template_name']) . "', counter_letters = '" . $this->db->escape($data['count_letters']) . "', date_start = '" . $this->db->escape($data['date_automailing']) . "' WHERE mailing_id = '" . (int)$mailing_id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "mailing_description WHERE mailing_id = '" . (int)$mailing_id . "'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "mailing_description SET mailing_id = '" . (int)$mailing_id . "', language_id = '" . (int)1 . "', theme = '" . $this->db->escape($data['letter_theme']) . "', text = '" . $this->db->escape($data['letter_text']) . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_mailing WHERE mailing_id = '" . (int)$mailing_id . "'");
        if(isset($data['added_products_id'])) {
            foreach ($data['added_products_id'] as $added_product_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_mailing SET product_id = '" . (int)$added_product_id . "', mailing_id = '" . (int)$mailing_id . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "mailing_social_links WHERE mailing_id = '" . (int)$mailing_id . "'");
        if(isset($data['social_link'])) {
            foreach ($data['social_link'] as $key => $value) {
                if($value != '') {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "mailing_social_links SET mailing_id = '" . (int)$mailing_id . "', icon_id = '" . ($key + 1) . "', link = '" . $value . "'");
                }
            }
        }

//        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_to_mailing WHERE mailing_id = '" . (int)$mailing_id . "'");
//        if(isset($data['mailing_customers'])) {
//            foreach ($data['mailing_customers'] as $customer_id) {
//                $this->db->query("INSERT INTO " . DB_PREFIX . "customer_to_mailing SET mailing_id = '" . (int)$mailing_id . "', customer_id = '" . (int)$customer_id . "'");
//            }
//        }

        $this->cache->delete('mailing');
    }
    
    public function delete($mailing_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "mailing WHERE mailing_id = '" . (int)$mailing_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "mailing_description WHERE mailing_id = '" . (int)$mailing_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_mailing WHERE mailing_id = '" . (int)$mailing_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "mailing_social_links WHERE mailing_id = '" . (int)$mailing_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_to_mailing WHERE mailing_id = '" . (int)$mailing_id . "'");

		$this->cache->delete('mailing');
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
				'name'             => $result['name'],
				'counter_letters'    => $result['counter_letters'],
				'date_start'       => date('Y-m-d\TH:i', strtotime($result['date_start'])),
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
				'letter_text'      => $result['text'],
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

    public function getSocialIcons() {
        $social_icons = array();
        $query = $this->db->query("SELECT icon_id, `name`, image FROM " . DB_PREFIX . "social_icons");

        foreach ($query->rows as $result) {
            $social_icons[] = array(
                'icon_id' => intval($result['icon_id']),
                'name'    => $result['name'],
                'image'   => $result['image']
            );
        }

        return $social_icons;
    }

    public function getMailingCustomersId($mailing_id) {
        $query = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customer_to_mailing WHERE mailing_id = '" . (int)$mailing_id . "'");

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

    public function unsubscribe($customer_id) {
        $this->db->query("UPDATE " . DB_PREFIX . "customer SET newsletter = 0 WHERE customer_id = '" . (int)$customer_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_to_mailing WHERE customer_id = '" . (int)$customer_id . "'");
    }

    public function unsubscribeFromMailing($mailing_id, $customer_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_to_mailing WHERE mailing_id = '" . (int)$mailing_id . "' AND customer_id = '" . (int)$customer_id . "'");
    }

    public function subscribeToMailing($mailing_id, $customer_id) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer_to_mailing SET mailing_id = '" . (int)$mailing_id . "', customer_id = '" . (int)$customer_id . "'");
    }

    public function unsubscribeAllFromMailing($mailing_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_to_mailing WHERE mailing_id = '" . (int)$mailing_id . "'");
    }

    public function subcribeAllToMailing($mailing_id) {
        $query = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customer WHERE newsletter = 1");
        $queryy = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customer_to_mailing WHERE mailing_id = '" . $mailing_id ."'");

        $customers = array();
        foreach ($queryy->rows as $key => $row) {
            $customers[] = (int)$row['customer_id'];
        }

        foreach ($query->rows as $row) {
            if(!in_array($row['customer_id'], $customers)) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "customer_to_mailing SET mailing_id = '" . (int)$mailing_id . "', customer_id = '" . (int)$row['customer_id'] . "'");
            }
        }
    }

    public function log($data) {
        // if ($this->config->has('payment_stripe_logging') && $this->config->get('payment_stripe_logging')) {
        $log = new Log('mailing.log');

        $log->write($data);
        // }
    }
}