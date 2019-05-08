<?php
declare(strict_types=1);

namespace Paysera\PhpStormHelper\Service;

use DOMDocument;
use DOMElement;
use DOMNode;
use RuntimeException;

class DomHelper
{
    public function loadDocument(string $pathToXml): DOMDocument
    {
        if (!file_exists($pathToXml)) {
            throw new RuntimeException(sprintf('File %s does not exist', $pathToXml));
        }

        $document = $this->createEmptyDomDocument();
        $this->loadXmlContents($document, $pathToXml);
        return $document;
    }

    public function loadOrCreateDocument(string $pathToXml): DOMDocument
    {
        $document = $this->createEmptyDomDocument();

        if (!file_exists($pathToXml)) {
            return $document;
        }

        $this->loadXmlContents($document, $pathToXml);

        return $document;
    }

    private function createEmptyDomDocument(): DOMDocument
    {
        $document = new DOMDocument();
        $document->preserveWhiteSpace = true;
        $document->formatOutput = true;
        return $document;
    }

    private function loadXmlContents(DOMDocument $document, string $pathToXml)
    {
        libxml_clear_errors();
        if (!$document->loadXML(file_get_contents($pathToXml))) {
            $error = libxml_get_last_error();
            $message = $error === false ? 'Cannot load XML file' : $error->message;
            throw new RuntimeException($message);
        }
    }

    public function saveDocument(string $path, DOMDocument $document)
    {
        file_put_contents($path, $document->saveXML());
    }

    public function findNode(DOMNode $node, string $tagName, array $attributes = null): DOMNode
    {
        $node = $this->findOptionalNode($node, $tagName, $attributes);
        if ($node === null) {
            throw new RuntimeException(sprintf('Node <%s> not found', $tagName));
        }

        return $node;
    }

    public function findOptionalNode(DOMNode $node, string $tagName, array $attributes = null)
    {
        $nodes = $this->findNodes($node, $tagName, $attributes);
        if (count($nodes) > 1) {
            throw new RuntimeException(sprintf('Expected only single <%s> tag', $tagName));
        } elseif (count($nodes) === 0) {
            return null;
        }

        return $nodes[0];
    }

    public function findOrCreateChildNode(DOMNode $node, string $tagName, array $attributes = null): DOMElement
    {
        $internalNode = $this->findOptionalNode($node, $tagName, $attributes);
        if ($internalNode === null) {
            $ownerDocument = $node instanceof DOMDocument ? $node : $node->ownerDocument;
            $internalNode = $ownerDocument->createElement($tagName);
            $this->applyAttributesToElement($internalNode, $attributes ?? []);
            $node->appendChild($internalNode);
        }
        return $internalNode;
    }

    public function applyAttributesToElement(DOMElement $node, array $attributes)
    {
        foreach ($attributes as $name => $value) {
            $node->setAttribute($name, $value);
        }
    }

    /**
     * @param DOMNode $node
     * @param string $tagName
     * @param array|null $attributes
     * @return array|DomNode[]
     */
    public function findNodes(DOMNode $node, string $tagName, array $attributes = null): array
    {
        $result = [];

        /** @var DOMNode $childNode */
        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeName === $tagName && $this->matchesAttributes($childNode, $attributes)) {
                $result[] = $childNode;
            }
        }

        return $result;
    }

    private function matchesAttributes(DOMNode $childNode, array $attributes = null): bool
    {
        if ($attributes === null) {
            return true;
        }

        foreach ($attributes as $key => $value) {
            $attributeNode = $childNode->attributes->getNamedItem($key);
            $attributeValue = $attributeNode !== null ? $attributeNode->nodeValue : null;
            if ($attributeValue !== $value) {
                return false;
            }
        }

        return true;
    }
}
