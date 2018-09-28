<?php declare(strict_types=1);

namespace AdminElement\Elements;

use AdminElement\IConfigureSection;
use Nette\Forms\Container;


/**
 * Class ForeignArchiveElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class ForeignArchiveElement extends ArchiveElement
{
    const
        DESCRIPTION = 'foreign archive/deleted element for archive row from table by FK for M:N',
        USAGE = [IConfigureSection::PRESENTER_FOREIGN];


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

        $fkItems = $this->wrapperSection->getListDatabaseFk($this->wrapperSection->getDatabaseTableName());
        $form->addSelect('foreign', $translator->translate($prefix . 'foreign'))
            ->setRequired($prefix . 'foreign-required')
            ->setPrompt($translator->translate($prefix . 'foreign-prompt'))
            ->setItems($fkItems)
            ->setTranslator(null);
    }
}
