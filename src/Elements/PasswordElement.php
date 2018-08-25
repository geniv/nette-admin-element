<?php declare(strict_types=1);

namespace AdminElement\Elements;

use Nette\Application\UI\Form;


/**
 * Class PasswordElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class PasswordElement extends AbstractTextElement
{
    const
        DESCRIPTION = 'password element for plain text in 1:N table';


    /**
     * Get form container content.
     *
     * @param Form $form
     */
    public function getFormContainerContent(Form $form)
    {
        // text element
        $form->addPassword($this->idElement, $this->getTranslateNameContent());

        parent::getFormContainerContent($form); // last position
    }
}
