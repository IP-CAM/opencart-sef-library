opencart-sef-library
====================

SEF Library for OpenCart. Automatic prepare, filter, transliteration &amp; save incoming RAW string into the DB as OpenCart URL (SEF)

Release by OpenCart Ukrainian Community (http://opencart-ukraine.tumblr.com)
Eugene Lifescale (a.k.a. Shaman)

Installation:
https://github.com/shaman/opencart-sef-library/wiki/Installation

Ask the question:
https://github.com/shaman/opencart-sef-library/issues

Community support:
http://opencart-ukraine.tumblr.com/submit

Install
1. Add library files
2. Include library to /admin/index.php

Example:

Find:
require_once('library/length.php');

If using vQmod, Find:
require_once(VQMod::modCheck(DIR_SYSTEM . 'library/length.php'));

Add after:
require_once('library/length.php');

or
require_once(VQMod::modCheck(DIR_SYSTEM . 'library/sef.php'));

Find:
// Database
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$registry->set('db', $db);

Add after:
// SEF
$sef = new Sef($registry);
$registry->set('sef', $sef);

3. Replace default insert/update keyword constructions in the models, for example:

Open:
/admin/model/catalog/product.php

Find:
$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id. "'");

if ($data['keyword']) {
	$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
}

Replace:
$this->sef->save($data['keyword'], 'product_id=' . (int) $product_id, $data['product_description'][$this->config->get('config_language_id')]['name']);
