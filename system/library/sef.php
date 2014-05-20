<?php

/**
 * OpenCart Ukrainian Community
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License, Version 3
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/copyleft/gpl.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email

 *
 * @category   OpenCart
 * @package    OCU SEF Library
 * @copyright  Copyright (c) 2011 Eugene Lifescale (a.k.a. Shaman) by OpenCart Ukrainian Community (http://opencart-ukraine.tumblr.com)
 * @license    http://www.gnu.org/copyleft/gpl.html     GNU General Public License, Version 3
 * @version    $Id: catalog/model/shipping/ocu_ukrposhta.php 1.2 2011-12-11 22:34:40
 */



/**
 * @category   OpenCart
 * @package    OCU SEF Library
 * @copyright  Copyright (c) 2011 Eugene Lifescale (a.k.a. Shaman) by OpenCart Ukrainian Community (http://opencart-ukraine.tumblr.com)
 * @license    http://www.gnu.org/copyleft/gpl.html     GNU General Public License, Version 3
 */

class Sef {

    // Basic variables
    private $_db;

    // Default config
    private $_separator = '-';
    private $_transliteration = true;
    private $_transliteration_language = 'UA';

    public function __construct($registry) {
        $this->_db = $registry->get('db');
    }

    /*
     * SEF API
     * Prepare, filter, validate & save incoming raw string into the DB as OpenCart SEO URL (SEF)
     *
     * @param   string  $keyword Incoming Keyword for OC url_alias table
     * @param   string  $query   Incoming Query for OC url_alias table
     * @param   string  $title   Incoming Title for automatic transliteration if Keyword is empty. Optional.
     *
     * @return  null
     */
    public function save($keyword, $query, $title = '') {

        // Get current alias
        // $old_url_alias = $this->_db->query("SELECT * FROM " . DB_PREFIX . "url_alias WHERE query = '" . $this->_db->escape($query) . "' LIMIT 1");


        // Filter
        $new_keyword = strtolower($keyword);
        $new_keyword = preg_replace('~[^-a-z0-9]+~u', $this->_separator, $new_keyword);
        $new_keyword = trim($new_keyword, $this->_separator);


        // Transliteration
        if ($this->_transliteration && empty($new_keyword)) {
            $new_keyword = $this->_transliteration($title);
        }


        // Save
        if (!empty($new_keyword)) {

            // Delete current alias
            $this->_db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = '" . $this->_db->escape($query) . "'");

            // Check for duplicates & add postfix if duplicates exists
            $i = 0;
            $tmp_keyword = $new_keyword;
            do {
                if ($i) {
                    $duplicate_url_alias = $this->_db->query("SELECT NULL FROM " . DB_PREFIX . "url_alias WHERE keyword = '" . $this->_db->escape($tmp_keyword . $this->_separator . $i) . "'");
                    $new_keyword = $tmp_keyword . $this->_separator . $i;
                } else {
                    $duplicate_url_alias = $this->_db->query("SELECT NULL FROM " . DB_PREFIX . "url_alias WHERE keyword = '" . $this->_db->escape($tmp_keyword) . "'");
                }
                $i++;

            } while ($duplicate_url_alias->num_rows);


            // Add new alias
            $this->_db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = '" . $this->_db->escape($query) . "', keyword = '" . $this->_db->escape($new_keyword) . "'");
        }


        // Add redirect 301
        //if ($old_url_alias->num_rows && $old_url_alias->row['keyword'] != $keyword) {
            // todo: add redirect 301 if $old_url_alias->keyword != $new_keyword
        //}
    }


    /*
     * Transliteration
     * Convert to UTF-8 & transliteration for cyrillic languages
     *
     * @param   string Cyrillic string
     * @return  string Transliteration UTF-8 string
     */
    private function _transliteration($string) {

        // Ukrainian Transliteration Table
        $replace['UA'] = array(
           '"'=>'',
           '`'=>'',
           '\''=>'',
           '’'=>'',
           'а'=>'a','А'=>'a',
           'б'=>'b','Б'=>'b',
           'в'=>'v','В'=>'v',
           'г'=>'g','Г'=>'g',
           'д'=>'d','Д'=>'d',
           'е'=>'e','Е'=>'e',
           'ж'=>'zh','Ж'=>'zh',
           'з'=>'z','З'=>'z',
           'и'=>'i','И'=>'i',
           'й'=>'y','Й'=>'y',
           'к'=>'k','К'=>'k',
           'л'=>'l','Л'=>'l',
           'м'=>'m','М'=>'m',
           'н'=>'n','Н'=>'n',
           'о'=>'o','О'=>'o',
           'п'=>'p','П'=>'p',
           'р'=>'r','Р'=>'r',
           'с'=>'s','С'=>'s',
           'т'=>'t','Т'=>'t',
           'у'=>'u','У'=>'u',
           'ф'=>'f','Ф'=>'f',
           'х'=>'h','Х'=>'h',
           'ц'=>'c','Ц'=>'c',
           'ч'=>'ch','Ч'=>'ch',
           'ш'=>'sh','Ш'=>'sh',
           'щ'=>'sch','Щ'=>'sch',
           'ъ'=>'','Ъ'=>'',
           'ы'=>'y','Ы'=>'y',
           'ь'=>'','Ь'=>'',
           'э'=>'e','Э'=>'e',
           'ю'=>'yu','Ю'=>'yu',
           'я'=>'ya','Я'=>'ya',
           'і'=>'i','І'=>'i',
           'ї'=>'yi','Ї'=>'yi',
           'є'=>'e','Є'=>'e'
        );

        // Cars replacement
        if (isset($replace[$this->_transliteration_language])) {
            return iconv('UTF-8', 'UTF-8//IGNORE', strtr($string, $replace[$this->_transliteration_language]));
        } else {
            return $string;
        }
    }
}

