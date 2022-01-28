<?php

declare(strict_types=1);

namespace Difra\Resourcer\Abstracts;

/**
 * Abstract adapter for XML resources
 */
abstract class XML extends Common
{
    /**
     * Assemble resources to single XML
     * @param string $instance
     * @param bool $withFilenames
     * @return string|bool
     */
    protected function processData(string $instance, bool $withFilenames = false): string|bool
    {
        $files = $this->getFiles($instance);

        $newXml = new \SimpleXMLElement("<$this->type></$this->type>");
        foreach ($files as $file) {
            $filename = $withFilenames ? $file['raw'] : false;
            $xml = simplexml_load_file($file['raw']);
            $this->mergeXML($newXml, $xml, $filename);
            foreach ($xml->attributes() as $key => $value) {
                $newXml->addAttribute($key, $value);
            }
        }
        if (method_exists($this, 'postprocess')) {
            $this->postprocess($newXml, $instance);
        }
        return $newXml->asXML();
    }

    /**
     * Recursively merge two XML trees
     * @param \SimpleXMLElement $xml1
     * @param \SimpleXMLElement $xml2
     * @param string $filename
     * @noinspection PhpVariableVariableInspection
     */
    private function mergeXML(\SimpleXMLElement $xml1, \SimpleXMLElement $xml2, string &$filename)
    {
        /** @var \SimpleXMLElement $node */
        foreach ($xml2 as $name => $node) {
            if (!$filename and property_exists($xml1, $name)) {
                $attr = $xml1->$name->attributes();
                foreach ($node->attributes() as $key => $value) {
                    if (!isset($attr[$key])) {
                        $xml1->$name->addAttribute($key, $value);
                    } elseif ($value != '') {
                        $xml1->$name->attributes()->$key = $value;
                    }
                }
                $this->mergeXML($xml1->$name, $node, $filename);
            } else {
                $new = $xml1->addChild($name, trim($node) ? $node : '');
                if ($filename) {
                    $new->addAttribute('source', $filename);
                }
                foreach ($node->attributes() as $key => $value) {
                    $new->addAttribute($key, $value);
                }
                $this->mergeXML($new, $node, $filename);
            }
        }
    }
}
