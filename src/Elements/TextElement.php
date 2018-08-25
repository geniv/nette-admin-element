<?php declare(strict_types=1);

namespace AdminElement\Elements;

use Nette\Application\UI\Form;


/**
 * Class TextElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class TextElement extends AbstractTextElement
{
    const
        DESCRIPTION = 'text element for plain text in 1:N table';


    /**
     * Get form container content.
     *
     * @param Form $form
     */
    public function getFormContainerContent(Form $form)
    {
        // text element
        $form->addText($this->idElement, $this->getTranslateNameContent());

        parent::getFormContainerContent($form); // last position
    }
}
