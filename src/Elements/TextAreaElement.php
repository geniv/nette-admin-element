<?php declare(strict_types=1);

namespace AdminElement\Elements;

use Nette\Application\UI\Form;


/**
 * Class TextAreaElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class TextAreaElement extends AbstractTextElement
{
    const
        DESCRIPTION = 'textarea element for big plain text in 1:N table';


    /**
     * Get form container content.
     *
     * @param Form $form
     */
    public function getFormContainerContent(Form $form)
    {
        // textarea element
        $form->addTextArea($this->idElement, $this->getTranslateNameContent());

        parent::getFormContainerContent($form); // last position
    }
}
