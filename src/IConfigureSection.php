<?php declare(strict_types=1);

namespace AdminElement;


/**
 * Interface IConfigureSection
 *
 * @author  geniv
 * @package AdminElement
 */
interface IConfigureSection
{
    // type presenter
    const
        PRESENTER_TABLE = 'table',
        PRESENTER_FOREIGN = 'foreign',
        PRESENTER_TREE = 'tree',
        PRESENTER = [
        self::PRESENTER_TABLE   => 'ContentTable',    // 1:N
        self::PRESENTER_FOREIGN => 'ContentForeign',  // M:N
        //        self::PRESENTER_TREE    => 'ContentTree',     // tree     //TODO TREE jeste nefunguje!
    ];

    // block name for neon
    const
        FILE_SECTION_DATABASE_INDEX = 'database',
        FILE_SECTION_ITEMS_INDEX = 'items';

    // block name for url
    const
        BLOCK_MAIN = 'main',
//        BLOCK_LINK = 'link',
        BLOCK_DATABASE = 'database';


    /**
     * Get list section.
     *
     * @return array
     */
    public function getListSection(): array;


    /**
     * Get section by id.
     *
     * @param string $id
     * @return array
     */
    public function getSectionById(string $id): array;


    /**
     * Get list section by group.
     *
     * @param string $idGroup
     * @return array
     */
    public function getListSectionByGroup(string $idGroup): array;


    /**
     * Save section part.
     *
     * @param string      $id
     * @param string|null $part
     * @param array       $values
     * @return string
     */
    public function saveSectionPart(string $id, string $part = null, array $values): string;


    /**
     * Delete section part.
     *
     * @param string $id
     * @param string $part
     * @param string $idPart
     * @return string
     */
    public function deleteSectionPart(string $id, string $part, string $idPart): string;


    /**
     * Delete section.
     *
     * @param string $id
     * @return string
     */
    public function deleteSection(string $id): string;


    /**
     * Save section sortable.
     *
     * @param string|null $id
     * @param array       $values
     * @return int
     */
    public function saveSectionSortable(string $id = null, array $values): int;
}
