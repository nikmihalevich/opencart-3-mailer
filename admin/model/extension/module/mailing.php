<?php
class ModelExtensionModuleMailing extends Model {
    public function install() {
        $this->log('Installing module');
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mailing` (
			`mailing_id` INT(11) NOT NULL,
			`name` varchar(50) NOT NULL,
			`counter_letters` INT(11) NOT NULL DEFAULT 10,
			`date_start` datetime NOT NULL,
			`date_added` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY (`mailing_template_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mailing_description` (
            `mailing_id` INT(11) NOT NULL,
            `theme` VARCHAR(50) NOT NULL,
            `text` TEXT NOT NULL DEFAULT ' ',
            PRIMARY KEY (`mailing_id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_to_mailing` (
            `mailing_id` INT(11) NOT NULL,
            `product_id` varchar(255) NOT NULL,
            PRIMARY KEY (`mailing_id`),
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
            PRIMARY KEY (`mailing_id`),
            PRIMARY KEY (`customer_id`),
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        $this->db->query("CREATE INDEX `idx_mailing` ON `" . DB_PREFIX . "product_to_mailing` (`mailing_id`)");
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mailing`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mailing_description`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "product_to_mailing`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mailing_social_links`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "customer_to_mailing`");
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "product_to_mailing` DROP INDEX `idx_mailing` IF EXISTS");

        $this->log('Module uninstalled');
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