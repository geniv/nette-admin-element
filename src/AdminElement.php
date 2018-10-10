<?php declare(strict_types=1);

namespace AdminElement;

use Nette\SmartObject;


/**
 * Class AdminElement
 *
 * @author  geniv
 * @package AdminElement
 */
class AdminElement
{
    use SmartObject;

    /** @var array */
    private $elements;


    /**
     * AdminElement constructor.
     *
     * @param array $elements
     */
    public function __construct(array $elements)
    {
        $this->elements = $elements;
    }


    /**
     * Get elements.
     *
     * @param string|null $usage
     * @return array
     */
    public function getElements(string $usage = null): array
    {
        return array_filter($this->elements, function ($class) use ($usage) {
            return ($usage ? in_array($usage, $class::USAGE) : true);
        });
    }


    /**
     * Get element.
     *
     * @param $element
     * @return bool|self
     */
    public function getElement($element)
    {
        if (isset($this->elements[$element])) {
            return $this->elements[$element];
        }
        return false;
    }
}
