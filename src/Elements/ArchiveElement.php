<?php declare(strict_types=1);

namespace AdminElement\Elements;

use AdminElement\WrapperSection;
use DateTime;
use Dibi\Fluent;


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
        if ($this->wrapperSection->isArchive()) {
            // if archive disabled (default false)
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
