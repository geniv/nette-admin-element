<?php declare(strict_types=1);

namespace AdminElement\Elements;

use Admin\App\Model\ConfigureSection;
use Nette\Application\UI\Form;
use Nette\Forms\Container;


/**
 * Class ForeignTextElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class ForeignTextElement extends AbstractTextElement
{
    // define general constant
    const
        DESCRIPTION = 'foreign text from table by FK for M:N',
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


    /**
     * Get form container content.
     *
     * @param Form $form
     */
    public function getFormContainerContent(Form $form)
    {
        // text element
        $form->addText($this->idElement, $this->getTranslateNameContent());

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
        // if value is empty will be save NULL
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
        // if value is empty will be save NULL
        return (isset($values[$this->idElement]) && $values[$this->idElement] ? $values[$this->idElement] : null);
    }
}
