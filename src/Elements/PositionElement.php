<?php declare(strict_types=1);

namespace AdminElement\Elements;

use AdminElement\IWrapperSection;
use Nette\Application\UI\Form;
use Nette\Forms\Container;


/**
 * Class PositionElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class PositionElement extends AbstractElement
{
    const
        DESCRIPTION = 'position element for sortable in 1:N table',
        ACTION_TYPES = [IWrapperSection::ACTION_LIST, IWrapperSection::ACTION_ADD];


    /**
     * Get form container admin.
     *
     * @param Container $form
     * @param string    $prefix
     */
    public function getFormContainerAdmin(Container $form, string $prefix)
    {
        parent::getFormContainerAdmin($form, $prefix);  // first position

        $translator = $form->getForm()->getTranslator();

        // select group by element
        $form->addSelect('group', $translator->translate($prefix . 'group'))
            ->setItems($this->wrapperSection->getItemsFormatted())
            ->setPrompt($translator->translate($prefix . 'group-prompt'))
            ->setOption('hint', $prefix . 'group-hint')
            ->setTranslator(null);

        unset($form['required'], $form['hint']);
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


    /**
     * Pre process insert values.
     *
     * @param array $values
     * @return int|null|string
     */
    public function preProcessInsertValues(array $values)
    {
        $max = $this->wrapperSection->getMaxValue($this->configure['name'], $values, $this->configure['group']);
        return $max + 1;
    }
}
