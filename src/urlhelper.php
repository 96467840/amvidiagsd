<?php
/**
 * @package         Google Structured Data
 * @version         3.6.4 Free
 *
 * @author          Andrey Murin <96467840@mail.ru>
 * @link            https://www.amvidia.com
 * @copyright       Copyright (c) Amvidia
 * @license         The MIT License (MIT)
 */

	class AmvidiaUrlHelper
	{
		//private $href = null;
		
		public static function getCanonical($params)
		{
			$sef_on = self::get_joomla_config('sef', false);
			$domain = $params->get('domain', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
			$proto  = $params->get('protocol', null);
			if (empty($domain)) {
				return;
			}
			$app = JFactory::getApplication();
			$doc = JFactory::getDocument();
			if ($app->getName() != 'site' or $doc->getType() != 'html') {
				return;
			}
			$c_url = null;
			foreach ($doc->_links as $url => $attr) {
				if ($attr['relation'] === 'canonical') {
					$c_url = $url;
					unset($doc->_links[$url]);
				}
			}
			$ctx = $app->input->getCmd('option', '') . '.' . $app->input->getCmd('view', '');
			if ($sef_on) {
				if (strpos($ctx, 'com_virtuemart.') === 0 || $params->get('use_docbase', false)) {
					$base = $doc->getBase();
					if (!empty($base)) {
						$c_url = $base;
					}
				}
			}
			$uri = JURI::getInstance($c_url ? $c_url : 'SERVER');
			$uri->setHost(self::fix_domain_input($domain));
			if (!empty($proto)) {
				$uri->setScheme($proto);
			}
			if ($sef_on && strpos($uri->getQuery(), 'id=') !== false && $ctx == 'com_content.article') {
				$o = self::get_db_object_by_id($app->input->getInt('id', 0), 'content', array(
																							  'id',
																							  'alias',
																							  'catid'
																							  ));
				if (!empty($o) && isset($o->id)) {
					list($comp, $view) = explode('.', $ctx);
					$nsef = 'index.php?' . 'option=' . $comp . '&' . 'view=' . $view . '&' . 'id=' . $o->id . ':' . $o->alias . '&' . 'catid=' . $o->catid;
					$lang = $app->input->getCmd('lang', '');
					if (!empty($lang)) {
						$nsef .= '&lang=' . $lang;
					}
					$itemid = $app->input->getInt('Itemid', '');
					if (!empty($itemid)) {
						$nsef .= '&Itemid=' . $itemid;
					}
					$sef_url = JRoute::_($nsef);
					if (strpos($sef_url, '?') === false) {
						$uri->setQuery('');
						$uri->setPath($sef_url);
					}
				}
			}
			if (strpos($uri->getPath(), '/') !== 0) {
				$uri->setPath('/' . $uri->getPath());
			}
			if ($sef_on && $params->get('remove_query_string', false)) {
				if ($uri->getQuery() !== '') {
					$uri->setQuery('');
				}
			} else {
				$QueryString = $_SERVER['QUERY_STRING'];
				if (!empty($QueryString)) {
					$uri->setQuery($QueryString);
				}
			}
			if ($sef_on && !version_compare(JVERSION, '3.5', '<') && $uri->getQuery() == '' && preg_match('#/index\.php/'.'*$#', $uri->getPath())) {
				$uri->setPath(preg_replace('#/index\.php/'.'*$#', '/', $uri->getPath()));
			}
			//$doc->addHeadLink(htmlspecialchars($uri->toString(), ENT_COMPAT, 'UTF-8', false), 'canonical');
			return $uri->toString();
		}

		static private function fix_domain_input($domain)
		{
			return preg_replace('/\/.*$/', '', preg_replace('/^[^\/]*?:\/\/+/', '', trim($domain)));
		}

		static private function get_joomla_config($key, $dflt = null)
		{
			$app = JFactory::getApplication();
			if (version_compare(JVERSION, '3.3.0', '<')) {
				return $app->getCfg($key, $dflt);
			} else {
				return $app->get($key, $dflt);
			}
		}

		static private function get_db_object_by_id($id, $tbl, $flds)
		{
			if (empty($id) or !is_numeric($id) or empty($tbl) or !is_string($tbl) or empty($flds) or !is_array($flds)) {
				return null;
			}
			$o = null;
			try {
				$db = JFactory::getDbo();
				$q  = $db->getQuery(true);
				$q->select($db->quoteName($flds))->from('#__' . $tbl)->where($db->quoteName('id') . '=' . $db->quote($id));
				$db->setQuery($q);
				$o = $db->loadObject();
				if (!is_object($o)) {
					$o = null;
				}
			}
			catch (Exception $e) {
			}
			return $o;
		}
}
