<?php declare(strict_types=1);

namespace AdminElement\Elements;

use AdminElement\IConfigureSection;
use AdminElement\WrapperSection;
use Nette\Application\UI\Form;
use Nette\Forms\Container;


/**
 * Class ForeignFkPkElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class ForeignFkPkElement extends AbstractElement
{
    // define general constant
    const
        DESCRIPTION = 'FK PK (:N)',
        USAGE = [IConfigureSection::PRESENTER_FOREIGN],
        ACTION_TYPES = [WrapperSection::ACTION_ADD, WrapperSection::ACTION_EDIT];


    /**
     * Get form container admin.
     *
     * @param Container $form
     * @param string    $prefix
     */
    public function getFormContainerAdmin(Container $form, string $prefix)
    {
        parent::getFormContainerAdmin($form, $prefix);  // first position

        // remove default order
        unset($form['orderdefault']);

        $translator = $form->getForm()->getTranslator();

        $fkItems = $this->wrapperSection->getListDatabaseFk($this->wrapperSection->getDatabaseTableName());
        $form->addSelect('foreign', $translator->translate($prefix . 'foreign'))
            ->setRequired($prefix . 'foreign-required')
            ->setPrompt($translator->translate($prefix . 'foreign-prompt'))
            ->setItems($fkItems)
            ->setTranslator(null);

        $this->wrapperSection->setForeign($this, 'fkpk');
    }


    /**
     * Get form container content.
     *
     * @param Form $form
     */
    public function getFormContainerContent(Form $form)
    {
        // hidden element
        $form->addHidden($this->idElement); // only hidden => show minimum for add+edit!

        parent::getFormContainerContent($form); // last position
    }
}
