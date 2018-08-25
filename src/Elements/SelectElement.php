<?php declare(strict_types=1);

namespace AdminElement\Elements;

use Nette\Application\UI\Form;
use Nette\Forms\Container;


/**
 * Class SelectElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class SelectElement extends AbstractSelectElement
{
    // define general constant
    const
        DESCRIPTION = 'static select element in 1:N table';


    /**
     * Get items.
     *
     * @param array $configure
     * @return array
     */
    public function getSelectItems(array $configure): array
    {
        // separate row ";", separate key-value ":"
        $result = [];
        if (isset($configure['items']) && $configure['items']) {
            if (is_string($configure['items'])) {
                $items = explode(';', $configure['items']);
                foreach ($items as $value) {
                    list($key, $value) = explode(':', $value);
                    $result[$key] = $value;
                }
                $result = array_map('strip_tags', $result);
            }

            if (is_array($configure['items'])) {
                $result = $configure['items'];
            }
        }
        return $result;
    }


    /**
     * Get form container admin.
     *
     * @param Container $form
     * @param string    $prefix
     */
    public function getFormContainerAdmin(Container $form, string $prefix)
    {
        parent::getFormContainerAdmin($form, $prefix);  // first position

        $form->addText('items', $prefix . 'items')
            ->setOption('hint', 'separate row ";", separate key-value ":"');

        // enable null value
        $form->addText('prompt', $prefix . 'prompt');
    }


    /**
     * Get form container content.
     *
     * @param Form $form
     */
    public function getFormContainerContent(Form $form)
    {
        if ($this->wrapperSection->getSubSectionId()) {
            $subElement = $this->wrapperSection->getSubElementName();
            // select one select element
            if ($subElement == $this->idElement) {
                $this->configure['defaultvalue'] = $this->wrapperSection->getSubSectionId();
            }
        }

        $items = $this->getSelectItems($this->configure);

        // select element
        $form->addSelect($this->idElement, $this->getTranslateNameContent())
            ->setItems($items)
            ->setTranslator(null);

        if (isset($this->configure['prompt']) && $this->configure['prompt']) {
            $form[$this->idElement]->setPrompt($this->configure['prompt']);
        }
        parent::getFormContainerContent($form); // last position
    }


    /**
     * Get render row.
     *
     * @param $data
     * @return string
     */
    public function getRenderRow($data): string
    {
        $items = $this->getSelectItems($this->configure);
        return $items[$data[$this->idElement]] ?? $this->configure['defaultvalue'];
    }
}
