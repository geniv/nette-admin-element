<?php declare(strict_types=1);

namespace AdminElement\Elements;

use Nette\Application\UI\Form;
use Nette\Forms\Container;


/**
 * Class HiddenElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class HiddenElement extends AbstractElement
{
    const
        DESCRIPTION = 'hidden element for transport plain text in 1:N table';


    /**
     * Get form container admin.
     *
     * @param Container $form
     * @param string    $prefix
     */
    public function getFormContainerAdmin(Container $form, string $prefix)
    {
        parent::getFormContainerAdmin($form, $prefix);  // first position

        unset($form['alias'], $form['required'], $form['defaultvalue'], $form['ordering'], $form['orderdefault'], $form['hint']);
    }


    /**
     * Get form container content.
     *
     * @param Form $form
     */
    public function getFormContainerContent(Form $form)
    {
        $form->addHidden($this->idElement);

        parent::getFormContainerContent($form); // last position
    }
}
