<?php
/**
 * SearchSphinx! Module Entry Point
 * 
 * @subpackage Modules
 * @license    GNU/GPL, see LICENSE.php
 * mod_zsearchsphinx is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\ModuleHelper;

// Include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';
JHtml::stylesheet(Juri::base() . 'modules/mod_zsearchsphinx/css/style.css');   
JHtml::script(Juri::base() . 'modules/mod_zsearchsphinx/js/search.js');
$docs = ModZSearchSphinxHelper::getSearch();
require JModuleHelper::getLayoutPath('mod_zsearchsphinx');
