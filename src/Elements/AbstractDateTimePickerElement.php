<?php declare(strict_types=1);

namespace AdminElement\Elements;

use Nette\Application\UI\Form;


/**
 * Class AbstractDateTimePickerElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
abstract class AbstractDateTimePickerElement extends AbstractDateTimeElement
{

    /**
     * Get form container content element.
     *
     * @param Form $form
     */
    protected function getFormContainerContentElement(Form $form)
    {
        // text element
        $form->addText($this->idElement, $this->getTranslateNameContent())
            ->setAttribute('readonly', true);
    }
}
