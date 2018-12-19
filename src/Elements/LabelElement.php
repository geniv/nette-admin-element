<?php declare(strict_types=1);

namespace AdminElement\Elements;

use AdminElement\IWrapperSection;
use Nette\Application\UI\Form;
use Nette\Forms\Container;


/**
 * Class LabelElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class LabelElement extends AbstractElement
{
    const
        DESCRIPTION = 'label element for show plain text in 1:N table',
        ACTION_TYPES = [IWrapperSection::ACTION_EDIT];


    /**
     * Get form container admin.
     *
     * @param Container $form
     * @param string    $prefix
     */
    public function getFormContainerAdmin(Container $form, string $prefix)
    {
        parent::getFormContainerAdmin($form, $prefix);  // first position

        unset($form['required'], $form['defaultvalue']);
    }


    /**
     * Get form container content.
     *
     * @param Form $form
     */
    public function getFormContainerContent(Form $form)
    {
        // label element
        $form->addLabel($this->idElement, $this->getTranslateNameContent());

        parent::getFormContainerContent($form); // last position
    }
}
