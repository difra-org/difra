<?php

namespace Difra\View\HTML;

abstract class Element
{
    /** @var bool */
    protected static $unique = false;

    /** @var string|null */
    protected $id = null;
    /** @var Element */
    protected $parent = null;
    /** @var bool[] */
    protected $classes = [];
    /** @var string[] */
    protected $attributes = [];
    /** @var Element[] */
    protected $children = [];

    /**
     * Set attribute value
     * @param string $name
     * @param string $value
     */
    public function setAttribute(string $name, string $value): self
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Get attribute value
     * @param string $name
     * @return string|null
     */
    public function getAttribute(string $name): ?string
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Remove attribute
     * @param string $name
     */
    public function removeAttribute(string $name): self
    {
        $this->attributes[$name] = null;
        return $this;
    }

    /**
     * Add child element
     * @param Element $child
     */
    public function addChild(Element $child): self
    {
        $child->setParent($this);
        $this->children[] = $child;
        return $this;
    }

    /**
     * Get all children elements
     * @return Element[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Set parent element
     * @param Element $parent
     */
    protected function setParent(Element $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Add class
     * @param string $class
     */
    public function addClass(string $class): self
    {
        $this->classes[$class] = true;
        return $this;
    }

    /**
     * Remove class
     * @param string $class
     */
    public function removeClass(string $class): self
    {
        $this->classes[$class] = false;
        return $this;
    }

    /**
     * Toggle class
     * @param string $class
     */
    public function toggleClass(string $class): self
    {
        $this->classes[$class] = $this->hasClass($class) ? false : true;
        return $this;
    }

    /**
     * Has class
     * @param string $class
     * @return bool
     */
    public function hasClass(string $class): bool
    {
        return !empty($this->classes[$class]);
    }

    /**
     * Set ID
     * @param string|null $id
     */
    public function setId(?string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get ID
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param \DOMNode|\DOMElement $node
     */
    public function getXML($node)
    {
        $currentName = explode('\\', static::class);
        $currentNode = $node->appendChild(
            $node->ownerDocument->createElement(
                strtolower(end($currentName))
            )
        );

        if ($this->id) {
            $currentNode->setAttribute('id', $this->id);
        }

        if (!empty($this->classes)) {
            $classStr = [];
            foreach ($this->classes as $class => $enabled) {
                if ($enabled) {
                    $classStr[] = $class;
                }
            }
            $currentNode->setAttribute('class', implode(' ', $classStr));
        }

        if (!empty($this->attributes)) {
            foreach ($this->attributes as $name => $value) {
                $currentNode->setAttribute($name, $value);
            }
        }

        if (!empty($this->children)) {
            foreach ($this->children as $child) {
                $child->getXML($currentNode);
            }
        }

        return $currentNode;
    }
}