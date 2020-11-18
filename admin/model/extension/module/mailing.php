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
            `product_id` INT(11) NOT NULL,
            `mailing_id` INT(11) NOT NULL,
            PRIMARY KEY (`product_id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mailing_social_links` (
            `mailing_id` INT(11) NOT NULL,
            `icon` varchar(50) NOT NULL,
            `link` varchar(255) NOT NULL,
            PRIMARY KEY (`mailing_id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "customer_to_mailing` (
            `mailing_id` INT(11) NOT NULL,
            `customer_id` INT(11) NOT NULL,
            PRIMARY KEY (`mailing_id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        // $this->db->query("CREATE INDEX `idx_mailing` ON `" . DB_PREFIX . "product_to_mailing` (`mailing_id`)");
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mailing`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mailing_description`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "product_to_mailing`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mailing_social_links`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "customer_to_mailing`");
        // $this->db->query("ALTER TABLE `" . DB_PREFIX . "product_to_mailing` DROP INDEX `idx_mailing`");

        $this->log('Module uninstalled');
    }

    public function add($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "mailing SET `name` = '" . $this->db->escape($data['template_name']) . "', counter_letters = '" . $this->db->escape($data['count_letters']) . "', date_start = '" . $this->db->escape($data['date_automailing']) . "', date_added = NOW()");

        $mailing_id = $this->db->getLastId();

        $this->db->query("INSERT INTO " . DB_PREFIX . "mailing_description SET mailing_id = '" . (int)$mailing_id . "', language_id = '" . (int)1 . "', theme = '" . $this->db->escape($data['letter_theme']) . "', text = '" . $this->db->escape($data['letter_text']) . "'");

        if(false) { //if (isset($data['product_store'])) {
			foreach ($data['socials'] as $social) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
			}
        }
        
        $this->cache->delete('mailing');

        return $mailing_id;
    }

    public function edit() {}
    
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
        
        $query = $this->db->query($sql);

		return $query->rows;
    }

    public function getMailing($mailing_id) {
		$mailing_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mailing WHERE mailing_id = '" . (int)$mailing_id . "'");

		foreach ($query->rows as $result) {
			$mailing_data = array(
				'name'             => $result['name'],
				'count_letters'    => $result['counter_letters'],
				'date_start'       => date('Y-m-d\TH:i', strtotime($result['date_start']))
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

    public function unsubscribe($customer_id) {
        $this->db->query("UPDATE " . DB_PREFIX . "customer SET newsletter = 0 WHERE customer_id = '" . (int)$customer_id . "'");
    }


    public function log($data) {
        // if ($this->config->has('payment_stripe_logging') && $this->config->get('payment_stripe_logging')) {
        $log = new Log('mailing.log');

        $log->write($data);
        // }
    }
}