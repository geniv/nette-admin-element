<?php declare(strict_types=1);

namespace AdminElement\Elements;

use AdminElement\WrapperSection;
use DateTime;
use Dibi\Fluent;
use Nette\Forms\Container;


/**
 * Class ArchiveElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class ArchiveElement extends HiddenElement
{
    const
        DESCRIPTION = 'archive/deleted element for archive row in 1:N table',
        ACTION_TYPES = [WrapperSection::ACTION_ARCHIVE];


    /**
     * Get form container admin.
     *
     * @param Container $form
     * @param string    $prefix
     */
    public function getFormContainerAdmin(Container $form, string $prefix)
    {
        parent::getFormContainerAdmin($form, $prefix);  // first position

        $form->addCheckbox('autohide', $prefix . 'autohide')
            ->setDefaultValue(true)
            ->setOption('hint', $prefix . 'autohide-hint');
    }


    /**
     * Pre process update values.
     *
     * @param array $values
     * @return string|null
     */
    public function preProcessUpdateValues(array $values)
    {
        $values[$this->idElement] = new DateTime();

        return parent::preProcessUpdateValues($values);
    }


    /**
     * Get source.
     *
     * @param Fluent $fluent
     */
    public function getSource(Fluent $fluent)
    {
        if ($this->configure['autohide'] ?? true || $this->wrapperSection->isArchive()) {
            // if auto hide enabled
            if ($this->configure['foreign']) {
                $foreign = $this->wrapperSection->getDatabaseTableListFk();
                $fk = $foreign[$this->configure['foreign']];
                $fluent->where([$this->wrapperSection->getDatabaseAliasName($fk['referenced_table_name']) . '.' . $this->configure['name'] => null]);
            } else {
                $fluent->where([$this->wrapperSection->getDatabaseAliasName($this->wrapperSection->getDatabaseTableName()) . '.' . $this->configure['name'] => null]);
            }
        }
    }
}
