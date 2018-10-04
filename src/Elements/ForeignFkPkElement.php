<?php declare(strict_types=1);

namespace AdminElement\Elements;

use AdminElement\IConfigureSection;
use AdminElement\WrapperSection;
use Dibi\Fluent;
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
        ACTION_TYPES = [WrapperSection::ACTION_ADD, WrapperSection::ACTION_EDIT, WrapperSection::ACTION_ARCHIVE];


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


    /**
     * Get source.
     *
     * @param Fluent $fluent
     */
    public function getSource(Fluent $fluent,bool $rawSource=false)
    {
        $foreign = $this->wrapperSection->getDatabaseTableListFk();
        $fk = $foreign[$this->configure['foreign']];

        // fkpk
        $aliasTableName = $this->wrapperSection->getDatabaseAliasName($fk['referenced_table_name']);

        $fluent->select([$aliasTableName . '.' . $fk['referenced_column_name'] => $this->idElement]);

        $fluent->rightJoin($fk['referenced_table_name'])->as($aliasTableName)->on('[' . $aliasTableName . '].[' . $fk['referenced_column_name'] . ']=[' . $this->wrapperSection->getDatabaseAliasName($fk['table_name']) . '].[' . $fk['column_name'] . ']');
    }
}
