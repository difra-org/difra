<?php

namespace Difra\Plugins\Catalog;

class Plugin extends \Difra\Plugin
{
	protected $version = 5;
	protected $description = 'Catalog';
	protected $require = 'mysql';

	public function init()
	{

		\Difra\Events::register('dispatch', '\Difra\Plugins\Catalog', 'addCategoryXML');
	}

	public function getSitemap()
	{

		$urls = [];
		$urlPrefix = 'http://' . \Difra\Envi::getHost();
		$categories = Category::getList(true);
		if (!empty($categories)) {
			foreach ($categories as $category) {
				$urls[] = [
					'loc' => $urlPrefix . $category->getFullLink()
				];
			}
		}
		$items = Item::getList(null, -1, 1, null, true);
		if (!empty($items)) {
			foreach ($items as $item) {
				$urls[] = [
					'loc' => $urlPrefix . $item->getFullLink()
				];
			}
		}
		if (empty($urls)) {
			return false;
		}
		return $urls;
	}
}
