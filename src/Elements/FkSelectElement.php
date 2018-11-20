<?php declare(strict_types=1);

namespace AdminElement\Elements;

use Nette\Forms\Container;


/**
 * Class FkSelectElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class FkSelectElement extends AbstractFkSelectElement
{
    // define general constant
    const
        DESCRIPTION = 'dynamic select element in 1:N [+:M in extra] with FK current table';


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

        // first level
        $fkItems = $this->wrapperSection->getListDatabaseFk($this->wrapperSection->getDatabaseTableName());  // FK from current table
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
        $form->addText('prompt', $prefix . 'prompt')
            ->setOption('hint', $prefix . 'prompt-hint');

        $form->addText('preview', $prefix . 'preview')
            ->setRequired($prefix . 'preview-required');
    }
}
