<?php declare(strict_types=1);

namespace AdminElement\Elements;

use Admin\App\Model\ConfigureSection;
use Nette\Forms\Container;


/**
 * Class ForeignSelectElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class ForeignSelectElement extends AbstractFkSelectElement
{
    // define general constant
    const
        DESCRIPTION = 'dynamic select in M:N [+:M in extra] for FK foreign table',
        USAGE = [ConfigureSection::PRESENTER_FOREIGN];


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

        // first level
        $fkItems = $this->wrapperSection->getListDatabaseFk();  // all FK
        $form->addSelect('fk', $translator->translate($prefix . 'fk'))
            ->setPrompt($translator->translate($prefix . 'fk-prompt'))
            ->setItems($fkItems)
            ->setTranslator(null);

        // second level - optional
        $fkItems = $this->wrapperSection->getListDatabaseFk();  // all FK
        $form->addSelect('fkextra', $translator->translate($prefix . 'fkextra'))
            ->setPrompt($translator->translate($prefix . 'fk-prompt'))
            ->setItems($fkItems)
            ->setTranslator(null);

        // enable null value
        $form->addText('prompt', $prefix . 'prompt');

        $form->addText('preview', $prefix . 'preview')
            ->setRequired($prefix . 'preview-required');
    }
}
