<?php declare(strict_types=1);

namespace AdminElement\Elements;

use Nette\Application\UI\Form;


/**
 * Class CheckboxElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class CheckboxElement extends AbstractElement
{
    const
        DESCRIPTION = 'checkbox element for bool state in 1:N table';


    /**
     * Get form container content.
     *
     * @param Form $form
     */
    public function getFormContainerContent(Form $form)
    {
        // checkbox element
        $form->addCheckbox($this->idElement, $this->getTranslateNameContent());

        parent::getFormContainerContent($form); // last position
    }


    /**
     * Pre process insert values.
     *
     * @param array $values
     * @return mixed|null|string
     */
    public function preProcessInsertValues(array $values)
    {
        return $this->preProcessUpdateValues($values);
    }


    /**
     * Pre process update values.
     *
     * @param array $values
     * @return mixed|null|string
     */
    public function preProcessUpdateValues(array $values)
    {
        // convert value to bool
        return boolval($values[$this->idElement]);
    }
}
