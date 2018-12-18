<?php declare(strict_types=1);

namespace AdminElement;

/**
 * Interface IConfigureGroup
 *
 * @author  geniv
 * @package AdminElement
 */
interface IConfigureGroup
{

    /**
     * Get list group.
     *
     * @param bool $hideInternal
     * @return array
     */
    public function getListGroup(bool $hideInternal = false): array;


    /**
     * Save group.
     *
     * @param array $values
     * @return int
     */
    public function saveGroup(array $values): int;


    /**
     * Save group sortable.
     *
     * @param array $values
     * @return int
     */
    public function saveGroupSortable(array $values): int;
}
