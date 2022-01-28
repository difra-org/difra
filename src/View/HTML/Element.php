<?php

declare(strict_types=1);

namespace Difra\View\HTML;

/**
 * HTML Element
 */
abstract class Element
{
    /** @var bool */
    protected static bool $unique = false;

    /** @var string|null */
    protected ?string $id = null;
    /** @var static|null */
    protected ?Element $parent = null;
    /** @var bool[] */
    protected array $classes = [];
    /** @var string[] */
    protected array $attributes = [];
    /** @var Element[] */
    protected array $children = [];

    /**
     * Set attribute value
     * @param string $name
     * @param string $value
     * @return static
     */
    public function setAttribute(string $name, string $value): static
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
     * @return static
     */
    public function removeAttribute(string $name): static
    {
        $this->attributes[$name] = null;
        return $this;
    }

    /**
     * Add child element
     * @param Element $child
     * @return static
     */
    public function addChild(Element $child): static
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
     * @return static
     */
    protected function setParent(Element $parent): static
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Add class
     * @param string $class
     * @return static
     */
    public function addClass(string $class): static
    {
        $this->classes[$class] = true;
        return $this;
    }

    /**
     * Remove class
     * @param string $class
     * @return static
     */
    public function removeClass(string $class): static
    {
        $this->classes[$class] = false;
        return $this;
    }

    /**
     * Toggle class
     * @param string $class
     * @return static
     */
    public function toggleClass(string $class): static
    {
        $this->classes[$class] = !$this->hasClass($class);
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
     * @return static
     */
    public function setId(?string $id): static
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
     * @param \DOMElement $node
     * @return \DOMElement
     */
    public function getXML(\DOMElement $node): \DOMElement
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