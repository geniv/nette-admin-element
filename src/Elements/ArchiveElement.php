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
        // set date or reset value
        $values[$this->idElement] = ($this->wrapperSection->isCleanArchive() ? null : new DateTime());

        return parent::preProcessUpdateValues($values);
    }


    /**
     * Get source.
     *
     * @param Fluent $fluent
     */
    public function getSource(Fluent $fluent)
    {
        if ($this->configure['foreign']) {
            $foreign = $this->wrapperSection->getDatabaseTableListFk();
            $fk = $foreign[$this->configure['foreign']];
            $where = $this->wrapperSection->getDatabaseAliasName($fk['referenced_table_name']) . '.' . $this->configure['name'];
        } else {
            $where = $this->wrapperSection->getDatabaseAliasName($this->wrapperSection->getDatabaseTableName()) . '.' . $this->configure['name'];
        }

        if ($this->wrapperSection->isArchive()) {
            // if archive disabled (default false)
            $fluent->where([$where => null]);
        } else {
            if ($this->wrapperSection->getActionType() == WrapperSection::ACTION_LIST) {
                $fluent->where($where . ' IS NOT NULL');
            }
        }
    }
}
