<?php declare(strict_types=1);

namespace AdminElement;

use AdminElement\Elements\IAbstractElement;
use Dibi\Connection;
use Dibi\IDataSource;
use Nette\Caching\Cache;


/**
 * Interface IWrapperSection
 *
 * @author  geniv
 * @package AdminElement
 */
interface IWrapperSection
{
    // action type
    const
        ACTION_LIST = 'list',
        ACTION_ADD = 'add',
        ACTION_EDIT = 'edit',
        ACTION_DETAIL = 'detail',
        ACTION_DELETE = 'delete',
        ACTION_ARCHIVE = 'archive',
        ACTION_EXPORT = 'export',
        ACTION_SORTABLE = 'sortable';
    // list all action types (actiontype)
    const
        ACTION_TYPES = [self::ACTION_LIST, self::ACTION_ADD, self::ACTION_EDIT, self::ACTION_DETAIL, self::ACTION_ARCHIVE, self::ACTION_EXPORT], // all types
        ACTION_TYPES_ELEMENT = [self::ACTION_LIST, self::ACTION_ADD, self::ACTION_EDIT, self::ACTION_DETAIL];   // select types
    // default order types
    const
        DEFAULT_ORDER_TYPES = [null => 'NULL', 'asc' => 'ASC', 'desc' => 'DESC',];


    /**
     * Get class name.
     *
     * @param string $class
     * @return string
     */
    public static function getClassName(string $class): string;


    /**
     * Get class description.
     *
     * @param string $class
     * @return string
     */
    public static function getClassDescription(string $class): string;


    /**
     * Get configure parameter by index.
     *
     * @param string $index
     * @return mixed|null
     */
    public function getConfigureParameterByIndex(string $index);


    /**
     * Get list path parameters.
     *
     * @return array
     */
    public function getListPathParameters(): array;


    /**
     * Get sub section.
     *
     * @param string $idSection
     * @return array
     */
    public function getSubSection(string $idSection = null): array;


    /**
     * Get section by sub element config.
     *
     * @param string $subElementConfig
     * @return array
     */
    public function getSectionBySubElementConfig(string $subElementConfig): array;


    /**
     * Get section id by sub element config.
     *
     * @param string $subElementConfig
     * @return string
     */
    public function getSectionIdBySubElementConfig(string $subElementConfig): string;


    /**
     * Get section id.
     *
     * @return string
     */
    public function getSectionId(): string;


    /**
     * Set section id.
     *
     * @param string $sectionId
     */
    public function setSectionId(string $sectionId);


    /**
     * Get list menu item.
     *
     * @param string $idGroup
     * @return array
     */
    public function getListMenuItem(string $idGroup): array;


    /**
     * Get menu item presenter.
     *
     * @param array $item
     * @param bool  $idSubSection
     * @return string
     */
    public function getMenuItemPresenter(array $item, bool $idSubSection = false): string;


    /**
     * Get section id menu item.
     *
     * @param array $item
     * @return string
     */
    public function getSectionIdMenuItem(array $item): string;


    /**
     * Get section name.
     *
     * @return string
     */
    public function getSectionName(): string;


    /**
     * Set section name.
     *
     * @param string|null $sectionName
     */
    public function setSectionName(string $sectionName = null);


    /**
     * Get subsection name.
     *
     * @param string|null $idSubSection
     * @return string
     */
    public function getSubsectionName(string $idSubSection = null): string;


    /**
     * Get admin elements.
     *
     * @param string|null $usage
     * @return array
     */
    public function getAdminElements(string $usage = null): array;


    /**
     * Set items.
     *
     * @param array $items
     */
    public function setItems(array $items);


    /**
     * Set cache names.
     *
     * @param array $names
     */
    public function setCacheNames(array $names);


    /**
     * Get action type.
     *
     * @return string
     */
    public function getActionType(): string;


    /**
     * Set action type.
     *
     * @param string $actionType
     */
    public function setActionType(string $actionType);


    /**
     * Set database.
     *
     * @param string $tableName
     * @param string $pk
     */
    public function setDatabase(string $tableName, string $pk);


    /**
     * Set database FkPk.
     *
     * @param string $fkPk
     * @param string $fkWhere
     */
    public function setDatabaseFk(string $fkPk, string $fkWhere);


    /**
     * Get database table FkPk.
     *
     * @return string
     */
    public function getDatabaseTableFkPk(): string;


    /**
     * Get database table FkWhere.
     *
     * @return string
     */
    public function getDatabaseTableFkWhere(): string;


    /**
     * Set database limit.
     *
     * @param int $limit
     */
    public function setDatabaseLimit(int $limit);


    /**
     * Set database test sql.
     *
     * @param bool $state
     */
    public function setDatabaseTestSql(bool $state);


    /**
     * Get by id.
     *
     * @param string $idSection
     * @param string $actionType
     * @return array
     */
    public function getById(string $idSection, string $actionType): array;


    /**
     * Get database table prefix.
     *
     * @return string
     */
    public function getDatabaseTablePrefix(): string;


    /**
     * Get database table name.
     *
     * @param bool $withPrefix
     * @return string
     */
    public function getDatabaseTableName(bool $withPrefix = true): string;


    /**
     * Get database list tables.
     *
     * @return array
     */
    public function getInformationSchemaTables(): array;


    /**
     * Get information schema key column usage.
     *
     * @param string|null $tableName
     * @return array
     */
    public function getInformationSchemaKeyColumnUsage(string $tableName = null): array;


    /**
     * Get list database table fk.
     *
     * @param string|null $tableName
     * @return array
     */
    public function getListDatabaseFk(string $tableName = null): array;


    /**
     * Get database alias name.
     *
     * @param string $name
     * @return string
     */
    public function getDatabaseAliasName(string $name): string;


    /**
     * Get database pk.
     *
     * @return string
     */
    public function getDatabasePk(): string;


    /**
     * Get database limit.
     *
     * @param int $default
     * @return int
     */
    public function getDatabaseLimit(int $default = 50): int;


    /**
     * Get database order default.
     *
     * @param string $defaultDirection
     * @return array
     */
    public function getDatabaseOrderDefault(string $defaultDirection = 'asc'): array;


    /**
     * Get elements.
     *
     * @return array
     */
    public function getElements(): array;


    /**
     * Get element.
     *
     * @param string $idElement
     * @return IAbstractElement
     */
    public function getElement(string $idElement): IAbstractElement;


    /**
     * Is foreign key correct.
     *
     * @return bool
     */
    public function canForeignKeyDelete(): bool;


    /**
     * get M by id N.
     *
     * @param int $id
     * @return array
     */
    public function getMByIdN(int $id): array;


    /**
     * Get data by id.
     *
     * @param $id
     * @return array
     */
    public function getDataById(int $id): array;


    /**
     * Get connection.
     *
     * @return Connection
     */
    public function getConnection(): Connection;


    /**
     * Get cache.
     *
     * @return Cache
     */
    public function getCache(): Cache;


    /**
     * Get source.
     *
     * @param bool $singleton
     * @param bool $rawSource
     * @return IDataSource
     */
    public function getSource(bool $singleton = true, bool $rawSource = false): IDataSource;


    /**
     * Get foreign items.
     *
     * @return array
     */
    public function getForeignItems(): array;


    /**
     * Set foreign.
     *
     * @param IAbstractElement $abstractElement
     * @param string           $type
     */
    public function setForeign(IAbstractElement $abstractElement, string $type);


    /**
     * Set archive.
     *
     * @param bool $archive
     */
    public function setArchive(bool $archive);


    /**
     * Is archive.
     *
     * @return bool
     */
    public function isArchive(): bool;


    /**
     * Clean archive.
     */
    public function cleanArchive();


    /**
     * Is clean archive.
     *
     * @return bool
     */
    public function isCleanArchive(): bool;


    /**
     * Get archive element.
     *
     * @return string
     */
    public function getArchiveElement(): string;


    /**
     * Is archive configure.
     *
     * @return bool
     */
    public function isArchiveConfigure(): bool;


    /**
     * Get count archive.
     *
     * @return int
     */
    public function getCountArchive(): int;


    /**
     * Get sortable element.
     *
     * @return string
     */
    public function getSortableElement(): string;


    /**
     * Is sortable configure.
     *
     * @return bool
     */
    public function isSortableConfigure(): bool;


    /**
     * Get count sortable.
     *
     * @return int
     */
    public function getCountSortable(): int;


    /**
     * Save sortable position.
     *
     * @param array $values
     * @return bool
     */
    public function saveSortablePosition(array $values): bool;


    /**
     * Get max value.
     *
     * @param string      $position
     * @param array       $value
     * @param string|null $groupByColumn
     * @return int
     */
    public function getMaxValue(string $position, array $value, string $groupByColumn = null): int;


    /**
     * Is FkId select first value.
     *
     * @return bool
     */
    public function isFkIdSelectFirstValue(): bool;


    /**
     * Get FkId.
     *
     * @return int
     */
    public function getFkId(): int;


    /**
     * Set FkId.
     *
     * @param int|null $fkId
     */
    public function setFkId(int $fkId = null);


    /**
     * Get sub section id.
     *
     * @return string
     */
    public function getSubSectionId(): string;


    /**
     * Set sub section id.
     *
     * @param string $subSectionId
     */
    public function setSubSectionId(string $subSectionId);
}
