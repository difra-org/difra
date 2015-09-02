<?php

/**
 * Class AdmStatusIndexController
 * Displays some stats.
 */
class AdmStatusIndexController extends Difra\Controller
{
    public function dispatch()
    {
        \Difra\View::$instance = 'adm';
    }

    public function indexAction()
    {
        /** @var \DOMElement $statusNode */
        $statusNode = $this->root->appendChild($this->xml->createElement('status'));

        // stats/difra
        $statusNode->setAttribute('difra', \Difra\Envi\Version::getBuild());
        $statusNode->setAttribute('cache', \Difra\Cache::getInstance()->adapter);
        $statusNode->setAttribute('webserver', $_SERVER['SERVER_SOFTWARE']);
        $statusNode->setAttribute('phpversion', phpversion());

        // stats/plugins
        /** @var $pluginsNode \DOMElement */
        $plugins = \Difra\Plugger::getAllPlugins();
        $enabledPlugins = $disabledPlugins = [];
        foreach ($plugins as $plugin) {
            if ($plugin->isEnabled()) {
                $enabledPlugins[] = $plugin->getName();
            } else {
                $disabledPlugins[] = $plugin->getName();
            }
        }
        $statusNode->setAttribute('enabledPlugins', implode(', ', $enabledPlugins));
        $statusNode->setAttribute('disabledPlugins', implode(', ', $disabledPlugins));

        // stats/mysql
        /** @var \DOMElement $mysqlNode */
        $mysqlNode = $statusNode->appendChild($this->xml->createElement('mysql'));
        try {
            \Difra\MySQL\Parser::getStatusXML($mysqlNode);
        } catch (Exception $ex) {
            $mysqlNode->setAttribute('error', $ex->getMessage() . ': ' . \Difra\MySQL::getInstance()->getError());
        }

        // stats of Unify tables
        $unifyNode = $statusNode->appendChild($this->xml->createElement('unify'));
        \Difra\Unify\DBAPI::getDbStatusXML($unifyNode);

        // stats/extensions
        /** @var $extensionsNode \DOMElement */
        $extensionsNode = $statusNode->appendChild($this->xml->createElement('extensions'));
        $extensions = get_loaded_extensions();
        $extensionsOk = [];
        $extensionsExtra = [];
        $extensionsRequired = [
            'dom',
            'SimpleXML',
            'xsl',
            'zlib',
            'ctype',
            'json',
            'mbstring',
            'Reflection',
            'Phar',
            'imagick',
            'mysqli'
        ];
        foreach ($extensions as $extension) {
            if (in_array($extension, $extensionsRequired)) {
                $extensionsOk[] = $extension;
                unset($extensionsRequired[array_search($extension, $extensionsRequired)]);
            } else {
                $extensionsExtra[] = $extension;
            }
        }
        natcasesort($extensionsOk);
        natcasesort($extensionsRequired);
        natcasesort($extensionsExtra);
        $extensionsNode->setAttribute('ok', implode(', ', $extensionsOk));
        $extensionsNode->setAttribute('required', implode(', ', $extensionsRequired));
        $extensionsNode->setAttribute('extra', implode(', ', $extensionsExtra));

        /** @var $permNode \DOMElement */
        $permNode = $statusNode->appendChild($statusNode->ownerDocument->createElement('permissions'));
        if (!is_dir(DIR_DATA)) {
            $permNode->setAttribute('data', 'Directory ' . DIR_DATA . ' does not exist!');
        } elseif (!is_writable(DIR_DATA)) {
            $permNode->setAttribute('data', 'Directory ' . DIR_DATA . ' is not writeable!');
        }
    }
}
