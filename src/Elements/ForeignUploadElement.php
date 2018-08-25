<?php declare(strict_types=1);

namespace AdminElement\Elements;

use Admin\App\Model\ConfigureSection;
use Nette\Application\UI\Form;
use Nette\Forms\Container;


/**
 * Class ForeignUploadElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class ForeignUploadElement extends UploadElement
{
    // define general constant
    const
        DESCRIPTION = 'foreign upload element by FK for M:N',
        USAGE = [ConfigureSection::PRESENTER_FOREIGN];


    /**
     * Get form container admin.
     *
     * @param Container $form
     * @param string    $prefix
     */
    public function getFormContainerAdmin(Container $form, string $prefix)
    {
        parent::getFormContainerAdmin($form, $prefix);   // first position

        $translator = $form->getForm()->getTranslator();

        $fkItems = $this->wrapperSection->getListDatabaseFk($this->wrapperSection->getDatabaseTableName());
        $form->addSelect('foreign', $translator->translate($prefix . 'foreign'))
            ->setRequired($prefix . 'foreign-required')
            ->setPrompt($translator->translate($prefix . 'foreign-prompt'))
            ->setItems($fkItems)
            ->setTranslator(null);
    }
}
