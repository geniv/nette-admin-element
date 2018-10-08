<?php declare(strict_types=1);

namespace AdminElement;

use AdminElement\Elements\AbstractElement;
use AdminElement\Elements\ArchiveElement;
use AdminElement\Elements\ForeignFkPkElement;
use AdminElement\Elements\ForeignFkWhereElement;
use AdminElement\Elements\PositionElement;
use dibi;
use Dibi\Connection;
use Dibi\Exception;
use Dibi\Fluent;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\DI\Container;
use Nette\Security\User;
use Nette\SmartObject;
use Nette\Utils\Finder;
use Tracy\Debugger;
use Tracy\ILogger;


/**
 * Class WrapperSection
 * Logic: each element has only one physical one instance and configure is set to each instance separately
 *
 * @author  geniv
 * @package AdminElement
 */
class WrapperSection
{
    use SmartObject;

    // action type
    const
        ACTION_LIST = 'list',
        ACTION_ADD = 'add',
        ACTION_EDIT = 'edit',
        ACTION_DELETE = 'delete',
        ACTION_ARCHIVE = 'archive',
        ACTION_DETAIL = 'detail',
        ACTION_EMPTY = 'empty', //FIXME vyhodit tento typ!!
        ACTION_SORTABLE = 'sortable';
    // list all action types (actiontype)
    const
        ACTION_TYPES = [self::ACTION_LIST, self::ACTION_ADD, self::ACTION_EDIT, self::ACTION_DETAIL, self::ACTION_ARCHIVE], // all types
        ACTION_TYPES_ELEMENT = [self::ACTION_LIST, self::ACTION_ADD, self::ACTION_EDIT, self::ACTION_DETAIL];   // select types
    // default order types
    const
        DEFAULT_ORDER_TYPES = [null => 'NULL', 'asc' => 'ASC', 'desc' => 'DESC',];

    /** @var IConfigureSection */
    private $configureSection;
    /** @var AdminElement */
    private $adminElement;
    /** @var Connection */
    private $connection;
    /** @var bool */
    private $configureReady = false;
    /** @var array */
    private $configureMain, $configureDatabase, $configureItems, $configureElements, $configureParameters;
    /** @var string */
    private $actionType;
    /** @var string */
    private $databaseTablePrefix, $databaseTable, $databaseTableAs, $databaseTablePk, $databaseTablePkIndex, $databaseTableFkPk, $databaseTableFkWhere;
    /** @var int */
    private $databaseLimit = 0;
    /** @var bool */
    private $databaseTestSql = false;
    /** @var array */
    private $databaseOrderDefault = [], $databaseTableListFk = [], $databaseValues = [];
    /** @var Fluent */
    private static $staticSource;
    /** @var Cache */
    private $cache;
    /** @var array */
    private $cacheNames = [];
    /** @var int */
    private $fkId;
    /** @var string */
    private $sectionId, $sectionName, $subSectionId, $subElementName, $subElementConfig;
    /** @var array */
    private $listSection = [];
    /** @var bool */
    private $archive = false;
    /** @var User */
    private $user;


    /**
     * WrapperSection constructor.
     *
     * @param IConfigureSection $configureSection
     * @param AdminElement      $adminElement
     * @param Container         $container
     * @param Connection        $connection
     * @param IStorage          $storage
     */
    public function __construct(IConfigureSection $configureSection, AdminElement $adminElement, Container $container, Connection $connection, IStorage $storage, User $user)
    {
        $this->configureSection = $configureSection;            // load configure section
        $this->adminElement = $adminElement;                    // load admin element
        $this->connection = $connection;                        // load database connection
        $this->configureParameters = $container->parameters;    // load system configure
        $this->user = $user;                                    // load system user

        // init cache
        $this->cache = new Cache($storage, 'WrapperSection');
    }


    /**
     * Get configure parameters.
     *
     * @return array
     */
    public function getConfigureParameters(): array
    {
        // get system neon configure
        return $this->configureParameters;
    }


    /**
     * Get configure parameter by index.
     *
     * @param string $index
     * @return mixed|null
     */
    public function getConfigureParameterByIndex(string $index)
    {
        return $this->configureParameters[$index] ?? null;
    }


    /**
     * Get list path parameters.
     *
     * @return array
     */
    public function getListPathParameters(): array
    {
        $result = [];
        $parameters = $this->getConfigureParameters();
        $webDir = $this->getConfigureParameterByIndex('webDir');
        foreach ($parameters as $keyItem => $item) {
            if (is_array($item)) {
                foreach ($item as $keyItm => $itm) {
                    if (is_string($itm) && is_dir($webDir . $itm)) {
                        $result[$keyItem . ' - ' . $keyItm][$itm] = realpath($webDir . $itm);
                    }
                }
            } else {
                if (is_string($item) && is_dir($item)) {
                    $result[$keyItem][$item] = realpath($item);
                }
            }
        }
        return $result;
    }


    /**
     * Get sub section by element.
     *
     * @internal
     * @param array $configure
     * @return array
     */
    private function getSubSectionByElement(array $configure): array
    {
        if (isset($configure['items'][$configure['subelement']])) {
            $item = $configure['items'][$configure['subelement']];
        } else {
            $item = $this->getItem($configure['subelement']);
        }

        $result = [];
        if ($item) {
            $instance = $this->adminElement->getElement($item['type']);
            $instance->setWrapperSection($this);

            $data = $instance->getSelectItems($item);
            foreach ($data as $idValue => $value) {
                $result[$idValue] = [
                    'id'   => $idValue,
                    'name' => $value,
                ];
            }
        }
        return $result;
    }


    /**
     * Get sub section.
     *
     * @param string $idSection
     * @return array
     */
    public function getSubSection(string $idSection = null): array
    {
        $cacheName = 'getSubSection' . $idSection;
        $result = $this->cache->load($cacheName);
        if ($result === null) {
            // loading for submenu
            if (isset($idSection)) {
                $list = $this->configureSection->getListSection();
                foreach ($list as $item) {
                    if (isset($item['subelement'])) {
                        $result[$item['id']]['subsection'] = $this->getSubSectionByElement($item);
                    }
                }
            }
            try {
                $this->cache->save($cacheName, $result, [Cache::TAGS => 'grid']);
            } catch (\Throwable $e) {
            }
        }
        return $result ?? [];
    }


    /**
     * Set section id.
     *
     * @param string $sectionId
     */
    public function setSectionId(string $sectionId)
    {
        $this->sectionId = $sectionId;
    }


    /**
     * Get list menu item.
     *
     * @param string $idGroup
     * @return array
     */
    public function getListMenuItem(string $idGroup): array
    {
        $subSection = $this->getSubSection($this->sectionId ?? null);

        $listSectionByGroup = $this->configureSection->getListSectionByGroup($idGroup);
        // merge and array intersect by key with original menu and subsection part
        $list = array_merge_recursive($listSectionByGroup, array_intersect_key($subSection, $listSectionByGroup));
        // filter empty group
        return array_filter($list, function ($item) {
            return $this->user->isAllowed($item['id'], 'default');
        });
    }


    /**
     * Get menu item presenter.
     *
     * @param array $item
     * @param bool  $idSubSection
     * @return string
     */
    public function getMenuItemPresenter(array $item, bool $idSubSection = false): string
    {
        if (!$this->listSection) {
            $this->listSection = $this->configureSection->getListSection();
        }

        $type = $item['type'];  // load default type
        if ($idSubSection && isset($item['subelementconfig'])) {
            // load sub-element-config type
            $type = $this->listSection[$item['subelementconfig']]['type'];
        }
        return IConfigureSection::PRESENTER[$type];
    }


    /**
     * Get section name.
     *
     * @return string
     */
    public function getSectionName(): string
    {
        return $this->sectionName ?? '';
    }


    /**
     * Set section name.
     *
     * @param string|null $sectionName
     */
    public function setSectionName(string $sectionName = null)
    {
        $this->sectionName = $sectionName;
    }


    /**
     * Get subsection name.
     *
     * @param string|null $idSubSection
     * @return string
     */
    public function getSubsectionName(string $idSubSection = null): string
    {
        $items = $this->getSubSectionByElement(['subelement' => $this->getSubElementName()]);
        return $items[$idSubSection]['name'] ?? '';
    }


    /**
     * Get admin elements.
     *
     * @param string|null $usage
     * @return array
     */
    public function getAdminElements(string $usage = null): array
    {
        return $this->adminElement->getElements($usage);
    }


//TODO prenaset nejak razeni - pokud se odering na strance 1 seradi tak aby drzel sort na dalsi stranky paginatoru 2,3...?? treba pres session?? http://localhost/NetteWeb/admin/content-foreign/?page=3&idSection=5b20edd043afe
//TODO konfigurator komponenta by mohla umet group/list kde se bude pouzivat jako overlay a bude mit obsah jako sablonu jednoho radku, a v nastaveni komponenty v latte definovane obsahy, kazdy soupec bude mit take mozost enabled pro povolovani ci zakazovani v ramci jazyka!

//TODO do table a foreign pridat tlacitko duplikace!

//TODO admin: cisty export dat (do CSV) podle aktualniho vypisu
//TODO grid: export: csv, pdf, xml...a moznost dalsich - ovladat pres typ zobrazeni co se ma exportovat a co ne!!!

//TODO zobrazovani elementu pro submenu/zobrazovani elementu pro hlavni sekci, zobrazovat pro: element=hodnota
//TODO element podle vybrane moznosti - napr: select moznost: admin: element=upload, guest=textarea, moderator=text

//TODO hledani skrz cely admin (jen skrz sekce admin/ nebo v kazde sekci samostatne)
//TODO element: href (na proklikyna produkt)

//TODO na nejake tabulkce otestovat vlastnicke zaznamy!
//TODO vyresit vykreslovani pro uzivatelsky vlastnene zaznamy!!! - via ACL

//TODO rozsirit prava na admini ... omezeni aby jen a ja a bracha mohli byt v administrace administrace - aby podadmini videli jen administraci samotnou

//TODO optimalizaci prekladu vyresit!!

//TODO udelat nejaky prehled aktualnich tabulek kde by bylo videt co ma ktera tabulka za sloupce, typy a vazby...
//TODO nacitat informace o tabulce (sloupci) a moznost jejich zacleneni do administace sekce, kontkretne: komentar, NULLABLE, mohutnost!

//FIXME do datagridu pridat select filtr na zobrazeni jednotlivych typu obsahu (select filt na urovni sortable)!!!!!

//TODO na HP by mobl prijit specialni vystup elementu typu nove objednavky/atd??

//FIXME system podmenu predelat!! filtrovani jako bylo tenkrat na konfiguratoru bude leda umet fitr na gridu!!!!!
//TODO grid: filtrovani on-off, hledani on-off <- session + multiple moznost v zakladu


//FIXME pri editaci foreign sekce a defaultni zvolenem jazyku se nezobrazi obsah i kdyz je nastavevym pro proklikani se zobrazi konektne

//TODO zobrazovani podle typu: zobrazit kdyz element X (select) bude mit tuto Y (text) honodu - eg.selektivni prepinani => insert: text, update: label

    /**
     * Init internal configure.
     *
     * @internal
     */
    private function initConfigure()
    {
        // load information schema key column usage
        $this->databaseTableListFk = $this->getInformationSchemaKeyColumnUsage();
    }


    /**
     * Load elements.
     *
     * @internal
     * @param array $items
     */
    public function loadElements(array $items)
    {
        // load visible elements
        $elements = [];
        foreach ($items as $key => $item) {
            $element = $this->adminElement->getElement($item['type']);
            if ($element) {
                $instance = clone $element; // clone instance
                if ($instance) {
                    $instance->setWrapperSection($this);    // set wrapper
                    $instance->setIdElement($key);          // set element id
                    $elements[$key] = $instance;
                }
            }
        }
        $this->configureElements = $elements;   // set elements to configure array
    }


    /**
     * Set items.
     *
     * @param array $items
     */
    public function setItems(array $items)
    {
        // init configure
        $this->initConfigure();

        // set items
        $this->configureItems = $items;

        // set elements to configure
        $this->loadElements($items);

        // tell configure is ready
        $this->configureReady = true;
    }


    /**
     * Set cache names.
     *
     * @param array $names
     */
    public function setCacheNames(array $names)
    {
        $this->cacheNames = $names;
    }


    /**
     * Get action type.
     *
     * @return string
     */
    public function getActionType(): string
    {
        return $this->actionType;
    }


    /**
     * Set action type.
     *
     * @param string $actionType
     */
    public function setActionType(string $actionType)
    {
        $this->actionType = $actionType;
    }


    /**
     * Set database.
     *
     * @param string $tableName
     * @param string $pk
     */
    public function setDatabase(string $tableName, string $pk)
    {
        // set table prefix
        $this->databaseTablePrefix = $this->configureParameters['tablePrefix'];
        // table name
        $this->databaseTable = $tableName;
        // table name for AS
        $this->databaseTableAs = $this->getDatabaseAliasName($this->getDatabaseTableName());
        // only name PK for use index in values
        $this->databaseTablePkIndex = $pk;
        // sql name for use in sql query with table AS
        $this->databaseTablePk = $this->databaseTableAs . '.' . $this->databaseTablePkIndex;
    }


    /**
     * Set database FkPk.
     *
     * @param string $fkPk
     * @param string $fkWhere
     */
    public function setDatabaseFk(string $fkPk, string $fkWhere)
    {
        $this->databaseTableFkPk = $fkPk;
        $this->databaseTableFkWhere = $fkWhere;
    }


    /**
     * Get database table FkPk.
     *
     * @return string
     */
    public function getDatabaseTableFkPk(): string
    {
        return $this->databaseTableFkPk;
    }


    /**
     * Get database table FkWhere.
     *
     * @return string
     */
    public function getDatabaseTableFkWhere(): string
    {
        return $this->databaseTableFkWhere;
    }


    /**
     * Set database limit.
     *
     * @param int $limit
     */
    public function setDatabaseLimit(int $limit)
    {
        $this->databaseLimit = $limit;
    }


    /**
     * Set database test sql.
     *
     * @param bool $state
     */
    public function setDatabaseTestSql(bool $state)
    {
        $this->databaseTestSql = $state;
    }


    /**
     * Get by id.
     *
     * @param string $idSection
     * @param string $actionType
     * @return array
     * @throws Exception
     */
    public function getById(string $idSection, string $actionType): array
    {
        $configureSectionArray = $this->configureSection->getSectionById($idSection);
        if (!$configureSectionArray) {
            throw new Exception('Section "' . $idSection . '" does not exist!');
        }

        // set idSection
        $this->setSectionId($idSection);

        // set action type
        $this->setActionType($actionType);

        /*
         * internal set
         */

        if (isset($configureSectionArray['cache'])) {
            // if define cache, explode by ";"
            $this->setCacheNames(explode(';', $configureSectionArray['cache']));
        }

        // set database
        if (isset($configureSectionArray['database'])) {
            // if define database for add configuration section mode
            $this->setDatabase($configureSectionArray['database']['table'], $configureSectionArray['database']['pk']);

            //set fkpk + fkwhere
            if (isset($configureSectionArray['database']['fkpk']) && isset($configureSectionArray['database']['fkwhere'])) {
                $this->setDatabaseFk($configureSectionArray['database']['fkpk'], $configureSectionArray['database']['fkwhere']);
            }

            // set limit
            if (isset($configureSectionArray['database']['limit'])) {
                $this->setDatabaseLimit((int) $configureSectionArray['database']['limit']);
            }

            // set testSql
            if (isset($configureSectionArray['database']['testsql'])) {
                $this->setDatabaseTestSql($configureSectionArray['database']['testsql']);
            }
        }

        // set section name
        $this->setSectionName($configureSectionArray['name']);

        // set sub-element
        $this->setSubElementName($configureSectionArray['subelement'] ?? null);
        $this->setSubElementConfig($configureSectionArray['subelementconfig'] ?? null);

//FIXME FK-WHERE + FKPK -> musi byt where->pk musi byt v tomto poradi!!!!

        // set items
        $this->setItems($configureSectionArray['items'] ?? []);

        return $configureSectionArray;
    }


    /**
     * Get database table prefix.
     *
     * @return string
     */
    public function getDatabaseTablePrefix(): string
    {
        return $this->configureParameters['tablePrefix'];
    }


    /**
     * Get database table name.
     *
     * @param bool $withPrefix
     * @return string
     */
    public function getDatabaseTableName(bool $withPrefix = true): string
    {
        if (!$this->databaseTable) {
            die('Database name is not set!');
        }
        return ($withPrefix ? $this->getDatabaseTablePrefix() : '') . $this->databaseTable;
    }


    /**
     * Get database list tables.
     *
     * @return array
     */
    public function getInformationSchemaTables(): array
    {
        // list table name by database name
        $result = $this->connection->select('t.table_schema, t.table_name, t.table_type, t.engine, t.version, ' .
            't.row_format, t.table_rows, t.auto_increment, t.create_time, t.update_time, t.check_time, t.table_collation, ' .
            't.table_comment')
            ->from('[INFORMATION_SCHEMA].[TABLES]')->as('t')
            ->where(['t.table_schema' => $this->connection->getConfig('database')]);    // load by table schema

        if ($this->isTestSQL()) {
            $this->processTestSQL($result);
        }
        return $result->fetchAssoc('table_name');
    }


//    public function getInformationSchemaTablesConstraints(string $table): array
//    {
//        // list database constraint name by table name
//        $result = $this->connection->select('*, tc.constraint_name, tc.constraint_type')
//            ->from('[INFORMATION_SCHEMA].[TABLE_CONSTRAINTS]')->as('tc')
//            ->where(['tc.table_name' => $table]);
//
//        if ($this->isTestSQL()) {
//            $this->processTestSQL($result);
//        }
//        return $result->fetchPairs('constraint_name', 'constraint_type');
//    }


//    private static $databaseListColumnsArray;
//    public function getInformationSchemaColumns(string $table): array
//    {
//        // list database columns by table name
//        if (!isset(self::$databaseListColumnsArray)) {
//            $result = $this->connection->select('c.column_name, c.ordinal_position, c.column_default, c.is_nullable, ' .
//                'c.data_type, c.character_maximum_length, c.numeric_precision, c.datetime_precision, c.character_set_name, ' .
//                'c.collation_name, c.column_type, c.privileges, c.column_comment')
//                ->from('[INFORMATION_SCHEMA].[COLUMNS]')->as('c')
//                ->where(['c.table_name' => $table]);
//
//            if ($this->isTestSQL()) {
//                $this->processTestSQL($result);
//            }
//            self::$databaseListColumnsArray = $result->fetchAssoc('column_name');
//        }
//        return self::$databaseListColumnsArray;
//    }


    /**
     * Get information schema key column usage.
     *
     * @param string|null $tableName
     * @return array
     */
    public function getInformationSchemaKeyColumnUsage(string $tableName = null): array
    {
        $cacheName = 'getInformationSchemaKeyColumnUsage' . $tableName;
        $result = $this->cache->load($cacheName);
        if ($result === null) {
            $result = $this->connection->select('kcu.constraint_name, ' .
                'kcu.table_schema, kcu.table_name, kcu.column_name, ' .
                'kcu.referenced_table_name, kcu.referenced_column_name, ' .
                'rc.update_rule, rc.delete_rule')
                ->from('[INFORMATION_SCHEMA].[KEY_COLUMN_USAGE]')->as('kcu')
                ->join('[INFORMATION_SCHEMA].[REFERENTIAL_CONSTRAINTS]')->as('rc')->on('rc.constraint_name=kcu.constraint_name')->and('rc.constraint_schema=kcu.constraint_schema')
                ->where('kcu.referenced_table_name IS NOT NULL')
                ->where(['kcu.table_schema' => $this->connection->getConfig('database')]);    // load by table schema

            if ($tableName) {   // load table name
                $result->where(['kcu.table_name' => $tableName]);
            }

            if ($this->isTestSQL()) {
                $this->processTestSQL($result);
            }

            $result = $result->fetchAssoc('constraint_name');
            try {
                $this->cache->save($cacheName, $result, [Cache::TAGS => 'fk']);
            } catch (\Throwable $e) {
            }
        }
        return $result ?? [];
    }


    /**
     * Get list database table fk.
     *
     * @param string|null $tableName
     * @return array
     */
    public function getListDatabaseFk(string $tableName = null): array
    {
        // name => load table, null => current table
        $list = $this->getInformationSchemaKeyColumnUsage($tableName);
        return array_map(function ($row) {
            return $row['table_name'] . '.' . $row['constraint_name'];
        }, $list);
    }


    /**
     * Is test SQL.
     *
     * @internal
     * @return bool
     */
    private function isTestSQL(): bool
    {
        return $this->databaseTestSql;
    }


    /**
     * Process test SQL.
     *
     * @internal
     * @param Fluent $fluent
     */
    private function processTestSQL(Fluent $fluent)
    {
        Debugger::log((string) $fluent, 'test-sql');
    }


    /**
     * Get database alias name.
     *
     * @internal
     * @param string $name
     * @return string
     */
    public function getDatabaseAliasName(string $name): string
    {
        $explode = array_slice(explode('_', $name), 1); // always remove prefix
        $letter = array_map(function ($row) {
            $len = strlen($row);
            return $row[0] . $row[1] . $row[$len - 1];  // first two letter + last one letter
        }, array_filter($explode));
        return implode($letter);
    }


    /**
     * Get database pk.
     *
     * @return string
     */
    public function getDatabasePk(): string
    {
        return $this->databaseTablePkIndex ?? '';
    }


    /**
     * Get database limit.
     *
     * @param int $default
     * @return int
     */
    public function getDatabaseLimit(int $default = 50): int
    {
        return (int) $this->databaseLimit ?: $default;
    }


    /**
     * Get database table pk.
     *
     * @internal
     * @return string
     */
    private function getDatabaseTablePk(): string
    {
        if ($this->getFkNameByType('fkpk')) {
            $fkPk = $this->databaseTableListFk[$this->getFkNameByType('fkpk')];
            return $this->getDatabaseAliasName($fkPk['referenced_table_name']) . '.' . $fkPk['referenced_column_name'];
        } else {
            return $this->databaseTableAs . '.' . $this->databaseTablePkIndex;
        }
    }


    /**
     * Get database order default.
     *
     * @param string $defaultDirection
     * @return array
     */
    public function getDatabaseOrderDefault(string $defaultDirection = 'asc'): array
    {
        return $this->databaseOrderDefault ?: [$this->getDatabaseTablePk() => $defaultDirection];
    }


    /**
     * Get element.
     *
     * @internal
     * @param string $idElement
     * @return AbstractElement
     */
    private function getInternalElement(string $idElement): AbstractElement
    {
        if (!isset($this->configureElements[$idElement])) {
            die('unknown id element: ' . $idElement);
        }
        return $this->configureElements[$idElement];
    }


    /**
     * Get elements.
     *
     * @return array
     */
    public function getElements(): array
    {
        $result = [];
        // list all elements
        foreach ($this->getItems() as $key => $item) {
            $result[$key] = $this->getInternalElement($key);
        }
        return $result;
    }


    /**
     * Get element.
     *
     * @param string $idElement
     * @return AbstractElement
     */
    public function getElement(string $idElement): AbstractElement
    {
        return $this->getInternalElement($idElement);
    }


    /**
     * Is foreign key correct.
     *
     * @return bool
     */
    public function canForeignKeyDelete(): bool
    {
        $fkPk = $this->getFkNameByType('fkpk');
        if ($fkPk) {
            // https://stackoverflow.com/questions/5809954/mysql-restrict-and-no-action
            // if no action then delate row is problem
            return $this->databaseTableListFk[$fkPk]['delete_rule'] != 'NO ACTION';
        }
        return true;
    }


    /**
     * get M by id N.
     *
     * @param int $id
     * @return array
     */
    public function getMByIdN(int $id): array
    {
        $result = [];
        $fkPkName = $this->getFkNameByType('fkpk');
        $fkWhereName = $this->getFkNameByType('fkwhere');
        if ($fkPkName && $fkWhereName) {
            $fkPk = $this->databaseTableListFk[$fkPkName];
            $fkWhere = $this->databaseTableListFk[$fkWhereName];

            $cacheName = 'getMByIdN' . $fkPkName . $fkWhereName . $id;
            $result = $this->cache->load($cacheName);
            if ($result === null) {
                $result = $this->connection->select([$fkPk['column_name'], $fkWhere['column_name']])
                    ->from($fkPk['table_name'])
                    ->where([$fkPk['column_name'] => $id])
                    ->fetchAssoc($fkWhere['column_name']);
                try {
                    $this->cache->save($cacheName, $result, [Cache::TAGS => 'fk']);
                } catch (\Throwable $e) {
                }
            }
        }
        return $result ?? [];
    }


    /**
     * Get data by id.
     *
     * @param $id
     * @return array
     */
    public function getDataById(int $id): array
    {
        $fkPkName = $this->getFkNameByType('fkpk');
        if ($fkPkName) {
            $fkPk = $this->databaseTableListFk[$fkPkName];
            $pk = $this->getDatabaseAliasName($fkPk['referenced_table_name']) . '.' . $fkPk['referenced_column_name'];
        } else {
            $pk = $this->databaseTablePk;
        }

        $result = $this->getSource()
            ->where([$pk => $id]);

        if ($this->isTestSQL()) {
            $this->processTestSQL($result);
        }
        return (array) ($result->fetch() ?: []);
    }


    /**
     * Get connection.
     *
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }


    /**
     * Get cache.
     *
     * @return Cache
     */
    public function getCache(): Cache
    {
        return $this->cache;
    }


    /**
     * Get source.
     *
     * @param bool $singleton
     * @param bool $rawSource
     * @return Fluent
     */
    public function getSource(bool $singleton = true, bool $rawSource = false): Fluent
    {
        if ($singleton) {
            // return static singleton
            if (isset(self::$staticSource)) {
                return self::$staticSource;
            }
        }

        // init fluent
        $result = $this->connection->select($this->getDatabaseTablePk())->from($this->getDatabaseTableName())->as($this->databaseTableAs);

        $orderDefault = [];
        $orderPosition = 1;
        foreach ($this->getItems() as $idItem => $item) {
            $element = $this->getElement($idItem);

            if (isset($item['foreign']) && $item['foreign']) {
                $foreign = $this->databaseTableListFk[$item['foreign']];

                if (!($element instanceof ForeignFkWhereElement || $element instanceof ForeignFkPkElement)) {
                    $result->select([$this->getDatabaseAliasName($foreign['referenced_table_name']) . '.' . $item['name'] => $idItem]);
                }
            } else {
                // add item to select, condition for insert new configure section
                if (isset($item['name'])) {
                    $result->select([$this->databaseTableAs . '.' . $item['name'] => $idItem]);
                }
            }

            // select by subSectionId
            if ($this->getSubElementName() && $this->getSubElementName() == $idItem && $this->subSectionId) {
                if (isset($item['fk'])) {
                    $fk = $this->databaseTableListFk[$item['fk']];
//                        dump($fk);
                    $result->where([$this->getDatabaseAliasName($fk['table_name']) . '.' . $fk['column_name'] => $this->subSectionId]);
                } else {
                    $result->where([$this->databaseTableAs . '.' . $item['name'] => $this->subSectionId]);
                }
            }

            // call getSource for each element
            $element->getSource($result, $rawSource);

            // collected default order
            if (isset($item['orderdefault']) && $item['orderdefault'] && isset($item['name'])) {
                $index = $this->databaseTableAs . '.' . $item['name'];
                if (isset($item['foreign']) && $item['foreign']) {
                    $foreign = $this->databaseTableListFk[$item['foreign']];
                    $index = $this->getDatabaseAliasName($foreign['referenced_table_name']) . '.' . $item['name'];
                }

                $orderDefault[$index] = [
                    'orderposition' => $item['orderposition'] ?? $orderPosition,
                    'orderdefault'  => $item['orderdefault'],
                ];
                $orderPosition++;
            }
        }

        if ($orderDefault) {
            // manual order by orderposition
            uasort($orderDefault, function ($a, $b) { return $a['orderposition'] > $b['orderposition']; });
            $this->databaseOrderDefault = array_map(function ($row) { return $row['orderdefault']; }, $orderDefault);
        }

        if ($this->isTestSQL()) {
            $this->processTestSQL($result);
        }

        if ($singleton) {
            // save result for static singleton
            self::$staticSource = $result;
        }
        return $result;
    }


    /**
     * Get idElement by fkType.
     *
     * @internal
     * @param string $fkType
     * @return string
     */
    private function getIdElementByFkType(string $fkType): string
    {
        $idElement = null;
        // switch fkType for correct idElement
        switch ($fkType) {
            case'fkpk':
                $idElement = $this->databaseTableFkPk;
                break;

            case 'fkwhere':
                $idElement = $this->databaseTableFkWhere;
                break;
        }
        return $idElement ?? '';
    }


    /**
     * Get FK name by type.
     *
     * @internal
     * @param string $fkType
     * @param string $index
     * @return mixed|null
     */
    private function getFkNameByType(string $fkType, string $index = 'foreign')
    {
        $idElement = $this->getIdElementByFkType($fkType);
        if ($idElement) {
            $item = $this->getItem($idElement);
            if (isset($item[$index])) {
                return $item[$index];
            }
        }
        return null;
    }


    /**
     * Get database table list fk.
     *
     * @internal
     * @return array
     */
    public function getDatabaseTableListFk(): array
    {
        return $this->databaseTableListFk;
    }


    /**
     * Get foreign items.
     *
     * @return array
     */
    public function getForeignItems(): array
    {
        $items = $this->getDataByFk((string) $this->getFkNameByType('fkwhere'), (string) $this->getFkNameByType('fkwhere', 'preview'));
        $fkExclude = $this->getFkNameByType('fkwhere', 'fkexclude');
        if ($fkExclude) {
            foreach ($fkExclude as $id) {
                unset($items[$id]); // remove exclude index from items
            }
        }
        return $items;
    }


    /**
     * Set foreign.
     *
     * @param AbstractElement $abstractElement
     * @param string          $type
     */
    public function setForeign(AbstractElement $abstractElement, string $type)
    {
        $configure = $abstractElement->getConfigure();
        if (isset($configure['foreign']) && $configure['foreign']) {
            if ($this->getIdElementByFkType($type) != $abstractElement->getIdElement()) {
                $this->configureSection->saveSectionPart($this->sectionId, IConfigureSection::FILE_SECTION_DATABASE_INDEX, [$type => $abstractElement->getIdElement()]);
            }
        }
    }


    /**
     * Set archive.
     *
     * @param bool $archive
     */
    public function setArchive(bool $archive)
    {
        $this->archive = $archive;
    }


    /**
     * Is archive.
     *
     * @return bool
     */
    public function isArchive(): bool
    {
        // archive switch for only list
        return ($this->actionType == self::ACTION_LIST ? (bool) $this->archive : false);
    }


    /**
     * Clean archive.
     */
    public function cleanArchive()
    {
        $this->archive = null;
    }


    /**
     * Is clean archive.
     *
     * @return bool
     */
    public function isCleanArchive(): bool
    {
        return is_null($this->archive);
    }


    /**
     * Get archive element.
     *
     * @return string
     */
    public function getArchiveElement(): string
    {
        // detection position element in configure
        $elements = array_filter($this->getElements(), function ($row) {
            return ($row instanceof ArchiveElement);
        });
        return implode(array_keys($elements));
    }


    /**
     * Is archive configure.
     *
     * @return bool
     */
    public function isArchiveConfigure(): bool
    {
        $element = $this->getArchiveElement();
        return $element != '';
    }


    /**
     * Get count archive.
     *
     * @return int
     */
    public function getCountArchive(): int
    {
        if ($this->isArchiveConfigure()) {
            $fluent = $this->getSource(false, true);    // get source
            $element = $this->getElement($this->getArchiveElement());   // get archive element
            $element->getArchiveSource($fluent, false);      // process manual source for archive
            return count($fluent);
        }
        return 0;
    }


    /**
     * Get sortable element.
     *
     * @return string
     */
    public function getSortableElement(): string
    {
        // detection position element in configure
        $elements = array_filter($this->getElements(), function ($row) {
            return ($row instanceof PositionElement);
        });
        return implode(array_keys($elements));
    }


    /**
     * Is sortable configure.
     *
     * @return bool
     */
    public function isSortableConfigure(): bool
    {
        $element = $this->getSortableElement();
        return $element != '';
    }


    /**
     * Get count sortable.
     *
     * @return int
     */
    public function getCountSortable(): int
    {
        if ($this->isSortableConfigure()) {
            return count($this->getSource());
        }
        return 0;
    }


    /**
     * Save sortable position.
     *
     * @param array $values
     * @return bool
     * @throws Exception
     */
    public function saveSortablePosition(array $values): bool
    {
        $fkPk = $this->getFkNameByType('fkpk');
        if ($fkPk) {
            $pk = $this->databaseTableListFk[$fkPk]['referenced_column_name'];
            $table = $this->databaseTableListFk[$fkPk]['referenced_table_name'];
        } else {
            $pk = $this->getDatabasePk();
            $table = $this->getDatabaseTableName();
        }

        $element = $this->getSortableElement();
        $item = $this->getItem($element);

        $result = 0;
        foreach ($values as $index => $value) {
            $result += $this->connection->update($table, [$item['name'] => $index + 1])
                ->where([$pk => $value])
                ->execute(\dibi::AFFECTED_ROWS);
        }

        // invalidate cache
        $this->cleanCache();

        return $result > 0;
    }


    /**
     * Get max value.
     *
     * @param string      $position
     * @param array       $value
     * @param string|null $groupByColumn
     * @return int
     */
    public function getMaxValue(string $position, array $value, string $groupByColumn = null): int
    {
        $fkPk = $this->getFkNameByType('fkpk');
        if ($fkPk) {
            $table = $this->databaseTableListFk[$fkPk]['referenced_table_name'];
        } else {
            $table = $this->getDatabaseTableName();
        }

        // get max value
        $result = $this->connection->select('MAX(' . $position . ')')
            ->from($table);

        if ($groupByColumn) {
            $item = $this->getItem($groupByColumn);
            $result->where([$item['name'] => $value[$groupByColumn]]);
        }
        return $result->fetchSingle() ?? 1;
    }


    /**
     * Is FkId select first value.
     *
     * @return bool
     */
    public function isFkIdSelectFirstValue(): bool
    {
        $item = $this->getItem($this->databaseTableFkWhere);
        return $item['fkidfirst'];
    }


    /**
     * Get FkId.
     *
     * @return int
     */
    public function getFkId()
    {
        return $this->fkId;
    }


    /**
     * Set FkId.
     *
     * @param int|null $fkId
     */
    public function setFkId(int $fkId = null)
    {
        $this->fkId = $fkId;
        $this->getSource(false);    // need regenerate fluent with new fkId!!
    }


    /**
     * Get sub section id.
     *
     * @return string
     */
    public function getSubSectionId()
    {
        return $this->subSectionId;
    }


    /**
     * Set sub section id.
     *
     * @param string $subSectionId
     * @throws Exception
     */
    public function setSubSectionId(string $subSectionId)
    {
        $this->subSectionId = $subSectionId;

        // if sub-element config is set
        if ($this->getSubElementConfig()) {
            $this->getById($this->getSubElementConfig(), $this->getActionType());
        }

        $this->getSource(false);    // need regenerate fluent with new subSectionId!!
    }


    /**
     * Get sub-element name.
     *
     * @return string
     */
    public function getSubElementName(): string
    {
        return $this->subElementName ?? '';
    }


    /**
     * Set sub-element name.
     *
     * @param string|null $subElementName
     */
    public function setSubElementName(string $subElementName = null)
    {
        $this->subElementName = $subElementName;
    }


    /**
     * Get sub-element config.
     *
     * @return string
     */
    public function getSubElementConfig(): string
    {
        return $this->subElementConfig ?? '';
    }


    /**
     * Set sub-element config.
     *
     * @param string|null $subElementConfig
     */
    public function setSubElementConfig(string $subElementConfig = null)
    {
        $this->subElementConfig = $subElementConfig;
    }


    /**
     * Get values by preview.
     *
     * @internal
     * @param string $preview
     * @return array
     */
    private function getValuesByPreview(string $preview): array
    {
        $result = [];
        if (preg_match_all('/(?<var>\@[\w\_\-]+\@)?/', $preview, $matches)) {
            $vars = array_filter($matches['var']);

            foreach ($vars as $var) {
                $name = substr($var, 1, -1);    // remove @ from variable for name
                $result[$name] = $var;
            }
        }
        return $result;
    }


    /**
     * Get data by fk.
     *
     * @param string $fk
     * @param string $preview
     * @param bool   $referenced
     * @return array
     */
    public function getDataByFk(string $fk, string $preview, bool $referenced = true): array
    {
        $cacheName = 'getDataByFk' . $fk . $preview . $referenced;
        $result = $this->cache->load($cacheName);
        if ($result === null) {
            $variable = $this->getValuesByPreview($preview);

            // check exist foreign key
            if (!isset($this->databaseTableListFk[$fk])) {
                Debugger::log('Foreign key "' . $fk . '" does not exist!', ILogger::EXCEPTION);
                return [];
            }

            $fkName = $this->databaseTableListFk[$fk];
            $pk = $fkName[$referenced ? 'referenced_column_name' : 'column_name'];
            $res = $this->connection->select($pk)
                ->select($variable)
                ->from($fkName[$referenced ? 'referenced_table_name' : 'table_name'])
                ->fetchAssoc($pk);

            $result = array_map(function ($row) use ($pk, $variable, $preview) {
                unset($row[$pk]);    // remove pk from row
                return str_replace($variable, (array) $row, $preview);
            }, $res);

            try {
                $this->cache->save($cacheName, $result, [Cache::TAGS => 'fk']);
            } catch (\Throwable $e) {
            }
        }
        return $result ?? [];
    }


    /**
     * Get item.
     *
     * @param string $idElement
     * @return array
     */
    public function getItem(string $idElement): array
    {
        return $this->configureItems[$idElement] ?? [];
    }


    /**
     * Get items.
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->configureItems ?? [];
    }


    /**
     * Get items by show.
     *
     * @param string $action
     * @return array
     */
    public function getItemsByShow(string $action): array
    {
        return array_filter($this->getItems(), function ($item) use ($action) {
            return in_array($action, $item['show']);
        });
    }


    /**
     * Get items formatted.
     *
     * @return array
     */
    public function getItemsFormatted(): array
    {
        return array_map(function ($item) {
            return $item['type'] . ' - ' . ($item['name'] ?? '---');
        }, $this->getItems());
    }


    /**
     * Get detail container content.
     *
     * @param int $id
     * @return array
     */
    public function getDetailContainerContent(int $id): array
    {
        // load data
        $data = $this->setDefaults($this->getDataById($id));

        $result = [];
        foreach ($this->getItemsByShow(self::ACTION_DETAIL) as $key => $item) {
            // load data and inset to array
            $item['render_row'] = $this->getInternalElement($key)->getRenderRow($data);
            $result[$key] = $item;
        }
        return $result;
    }


    /**
     * Get form renderer path.
     *
     * @param bool $section
     * @return string
     */
    public static function getFormRendererPath($section = false): string
    {
        if ($section) {
            return __DIR__ . '/FormSectionRenderer.latte';
        } else {
            return __DIR__ . '/FormRenderer.latte';
        }
    }


    /**
     * Get form container content.
     *
     * @param Form $form
     */
    public function getFormContainerContent(Form $form)
    {
        // generate list html elements for content
        foreach ($this->getItemsByShow($this->actionType) as $key => $item) {
            $this->getInternalElement($key)->getFormContainerContent($form, $item);
        }
    }


    /**
     * Clean cache.
     *
     * @internal
     */
    public function cleanCache()
    {
        // internal clean cache
        $this->cache->clean([Cache::TAGS => 'fk']);     // internal clean cache for FK / foreign
        $this->cache->clean([Cache::TAGS => 'grid']);   // internal clean cache for grid

        // user defined cache
        if ($this->cacheNames) {
            $tempWebDir = $this->configureParameters['tempWebDir'];
            foreach ($this->cacheNames as $cacheDir) {
                // prochazeni a fyzicke mazani cache souboru primo z tempu
                $finder = Finder::findFiles('*');
                $tempPath = $tempWebDir . $cacheDir;
                if (file_exists($tempPath)) {
                    $files = $finder->in($tempPath);
                    foreach ($files as $file) {
                        @unlink($file->getRealPath());
                    }
                }
            }
        }
    }


    /*
     * For abstract element.
     */


    /**
     * Set flag success insert.
     *
     * @internal
     * @param int $value
     * @return int
     */
    private function setFlagSuccessInsert(int $value): int
    {
        $result = 0;
        foreach ($this->getItemsByShow($this->actionType) as $key => $element) {
            $result = $this->getInternalElement($key)->setFlagSuccessInsert($value);
            if ($result) {
                break;
            }
        }
        return $result;
    }


    /**
     * Set flag success update.
     *
     * @internal
     * @param int $value
     * @return int
     */
    private function setFlagSuccessUpdate(int $value): int
    {
        $result = 0;
        foreach ($this->getItemsByShow($this->actionType) as $key => $element) {
            $result = $this->getInternalElement($key)->setFlagSuccessUpdate($value);
            if ($result) {
                break;
            }
        }
        return $result;
    }


    /**
     * Set flag success delete.
     *
     * @internal
     * @param int $value
     * @return int
     */
    private function setFlagSuccessDelete(int $value): int
    {
        $result = 0;
        // for delete action are all elements
        foreach ($this->getItems() as $key => $element) {
            $result = $this->getInternalElement($key)->setFlagSuccessDelete($value);
            if ($result) {
                break;
            }
        }
        return $result;
    }


    /**
     * Set defaults.
     *
     * @param array $values
     * @return array
     */
    public function setDefaults(array $values): array
    {
        foreach ($this->getItemsByShow($this->actionType) as $key => $element) {
            $val = $this->getInternalElement($key)->setDefaults($values);
            if (is_array($val)) {
                $values = array_merge($values, $val);
            } else {
                $values[$key] = $val;
            }
        }
        $this->databaseValues = $values;
        return $values;
    }


    /**
     * Get database values.
     *
     * @return array
     */
    public function getDatabaseValues(): array
    {
        return $this->databaseValues;
    }


    /**
     * Pre process ignore values.
     *
     * @internal
     * @param array $values
     * @return array
     */
    private function preProcessIgnoreValues(array $values): array
    {
        foreach ($this->getItemsByShow($this->actionType) as $key => $element) {
            // auto remove index in case omit=>true
            if ($element['omit'] ?? false) {
                unset($values[$key]);
            }

            $ignores = $this->getInternalElement($key)->preProcessIgnoreValues();
            if ($ignores) {  // if define ignore
                foreach ($ignores as $ignore) {
                    unset($values[$ignore]);
                }
            }
        }
        return $values;
    }


    /*
     * Insert.
     */


    /**
     * Pre process insert values.
     *
     * @internal
     * @param array $values
     * @return array
     */
    private function preProcessInsertValues(array $values): array
    {
        foreach ($this->getItemsByShow($this->actionType) as $key => $element) {
            $values[$key] = $this->getInternalElement($key)->preProcessInsertValues($values);
        }
        return $values;
    }


    /**
     * Post process success insert.
     *
     * @internal
     * @param array $values
     */
    private function postProcessSuccessInsert(array $values)
    {
        foreach ($this->getItemsByShow($this->actionType) as $key => $element) {
            $this->getInternalElement($key)->postProcessSuccessInsert($values);
        }
    }


    /**
     * Post process insert.
     *
     * @internal
     * @param array $values
     */
    private function postProcessInsert(array $values)
    {
        foreach ($this->getItemsByShow($this->actionType) as $key => $element) {
            $this->getInternalElement($key)->postProcessInsert($values);
        }
    }


    /*
     * Update.
     */


    /**
     * Pre process update values.
     *
     * @internal
     * @param array $values
     * @return array
     */
    private function preProcessUpdateValues(array $values): array
    {
        foreach ($this->getItemsByShow($this->actionType) as $key => $element) {
            $values[$key] = $this->getInternalElement($key)->preProcessUpdateValues($values);
        }
        return $values;
    }


    /**
     * Post process success update.
     *
     * @internal
     * @param array $values
     */
    private function postProcessSuccessUpdate(array $values)
    {
        foreach ($this->getItemsByShow($this->actionType) as $key => $element) {
            $this->getInternalElement($key)->postProcessSuccessUpdate($values);
        }
    }


    /**
     * Post process update.
     *
     * @internal
     * @param array $values
     */
    private function postProcessUpdate(array $values)
    {
        foreach ($this->getItemsByShow($this->actionType) as $key => $element) {
            $this->getInternalElement($key)->postProcessUpdate($values);
        }
    }


    /*
     * Delete.
     */


    /**
     * Pre process delete.
     *
     * @internal
     * @param int $id
     */
    private function preProcessDelete(int $id)
    {
        foreach ($this->getItems() as $key => $element) {
            $this->getInternalElement($key)->preProcessDelete($id);
        }
    }


    /**
     * Post process success delete.
     *
     * @internal
     * @param int $id
     */
    private function postProcessSuccessDelete(int $id)
    {
        foreach ($this->getItems() as $key => $element) {
            $this->getInternalElement($key)->postProcessSuccessDelete($id);
        }
    }


    /**
     * Post process delete.
     *
     * @internal
     * @param int $id
     */
    private function postProcessDelete(int $id)
    {
        foreach ($this->getItems() as $key => $element) {
            $this->getInternalElement($key)->postProcessDelete($id);
        }
    }


    /*
     * For content presenter.
     */


    /**
     * Get columns with value.
     *
     * @internal
     * @param array $values
     * @return array
     */
    private function getColumnsWithValue(array $values): array
    {
        $result = [];
        foreach ($this->getItemsByShow($this->actionType) as $key => $item) {
            // separate to fkname and other
            if (isset($item['foreign']) && $item['foreign']) {
                // item !preview && fkname - separate - fkselect from foreign
                if (!isset($result[$item['foreign']][$item['name']])) {
                    // set only to unique index -> if two elemnts has same name => set only first element!
                    $result[$item['foreign']][$item['name']] = $values[$key];
                }
            } else {
                // skip undefined index: omit=>true
                if (isset($item['omit']) && $item['omit']) {
                    continue;
                }
                $result[$item['name']] = $values[$key];
            }
        }
        return $result;
    }


    /**
     * On success insert.
     *
     * @param array $values
     * @return int
     * @throws Exception
     */
    public function onSuccessInsert(array $values): int
    {
        // preprocessed insert values
        $values = $this->preProcessInsertValues($values);

        // preprocessed ignore values
        $values = $this->preProcessIgnoreValues($values);

        // convert internal name to db column name
        $col = $this->getColumnsWithValue($values);

        $this->connection->begin();
        try {

            // first save :N
            if ($this->getFkNameByType('fkpk')) {
                $fkPk = $this->databaseTableListFk[$this->getFkNameByType('fkpk')];
                $separateValues = $col[$this->getFkNameByType('fkpk')];
                unset($col[$this->getFkNameByType('fkpk')]);

//                $id = $separateValues[$fkPk['column_name']];
                unset($separateValues[$fkPk['column_name']]);

                // separate value can be []
                $res = $this->connection->insert($fkPk['referenced_table_name'], $separateValues);
                if ($this->isTestSQL()) {
                    $this->processTestSQL($res);
                }

                $id = $res->execute(Dibi::IDENTIFIER);

                $col[$fkPk['column_name']] = $id;
            }

            // second save M:
            if ($this->getFkNameByType('fkwhere')) {
                $fkWhere = $this->databaseTableListFk[$this->getFkNameByType('fkwhere')];
                $separateValues = $col[$this->getFkNameByType('fkwhere')];
                unset($col[$this->getFkNameByType('fkwhere')]);

                $id = $separateValues[$fkWhere['column_name']];
                unset($separateValues[$fkWhere['column_name']]);

                if ($separateValues) {
                    $id = $this->connection->select($fkWhere['referenced_column_name'])
                        ->from($fkWhere['referenced_table_name'])
                        ->where($separateValues)
                        ->fetchSingle();

                    if (!$id) {
                        $res = $this->connection->insert($fkWhere['referenced_table_name'], $separateValues);
                        if ($this->isTestSQL()) {
                            $this->processTestSQL($res);
                        }

                        // set new id for M: relationship
                        $id = $res->execute(Dibi::IDENTIFIER);
                    }
                }

                if (!$id && $this->isFkIdSelectFirstValue()) {
                    // set id by fkId
                    $id = $this->fkId;
                }
                $col[$fkWhere['column_name']] = $id;
            }

            // main save
            $res = $this->connection->insert($this->getDatabaseTableName(), $col);
            if ($this->isTestSQL()) {
                $this->processTestSQL($res);
                $this->connection->rollback();
                die;
            }

            $result = $res->execute(Dibi::IDENTIFIER);

            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollback();
            Debugger::log($e, ILogger::EXCEPTION);
            throw $e;
        }

        // invalidate cache
        $this->cleanCache();

        // set flag success insert
        $result = $this->setFlagSuccessInsert($result);
        if ($result >= 0) {
            // postprocessing after success insert
            $this->postProcessSuccessInsert($values);
        }
        // total postprocessing
        $this->postProcessInsert($values);
        return $result;
    }


    /**
     * On success update.
     *
     * @param array $values
     * @return int
     * @throws Exception
     */
    public function onSuccessUpdate(array $values): int
    {
        // preprocessed insert values
        $values = $this->preProcessUpdateValues($values);

        // preprocessed ignore values
        $values = $this->preProcessIgnoreValues($values);

        $col = $this->getColumnsWithValue($values);

        $this->connection->begin();
        try {
            $result = 0;
            $fk = [];

            // first save :N
            if ($this->getFkNameByType('fkpk')) {
                $fkPk = $this->databaseTableListFk[$this->getFkNameByType('fkpk')];
                $separateValues = $col[$this->getFkNameByType('fkpk')];
                unset($col[$this->getFkNameByType('fkpk')]);

                $id = $separateValues[$fkPk['column_name']];
                unset($separateValues[$fkPk['column_name']]);

                if ($separateValues) {
                    // if separate value contain data
                    $res = $this->connection->update($fkPk['referenced_table_name'], $separateValues)
                        ->where([$fkPk['referenced_column_name'] => $id]);
                    if ($this->isTestSQL()) {
                        $this->processTestSQL($res);
                    }
                    $result += $res->execute(Dibi::AFFECTED_ROWS);
                }

                $fk[$fkPk['column_name']] = $id;
            }


            // second save M:
            if ($this->getFkNameByType('fkwhere')) {
                $fkWhere = $this->databaseTableListFk[$this->getFkNameByType('fkwhere')];
                $separateValues = $col[$this->getFkNameByType('fkwhere')];
                unset($col[$this->getFkNameByType('fkwhere')]);

                $id = $separateValues[$fkWhere['column_name']];
                unset($separateValues[$fkWhere['column_name']]);

                if ($separateValues) {
                    $res = $this->connection->update($fkWhere['referenced_table_name'], $separateValues)
                        ->where([$fkWhere['referenced_column_name'] => $id]);
                    if ($this->isTestSQL()) {
                        $this->processTestSQL($res);
                    }

                    $result += $res->execute(Dibi::AFFECTED_ROWS);
                }

                if (!$id && $this->isFkIdSelectFirstValue()) {
                    // set id by fkId
                    $id = $this->fkId;
                }
                $fk[$fkWhere['column_name']] = $id;
            }

            $id = $this->connection->select($this->databaseTablePkIndex)
                ->from($this->getDatabaseTableName())
                ->where($fk)
                ->fetchSingle();

            if (!$id) {
                // insert
                $res = $this->connection->insert($this->getDatabaseTableName(), array_merge($col, $fk));

            } else {
                // update
                $res = $this->connection->update($this->getDatabaseTableName(), array_merge($col, $fk));

                if ($fk) {
                    $res->where([$this->databaseTablePkIndex => $id]);
                } else {
                    $res->where([$this->databaseTablePkIndex => $values[$this->databaseTablePkIndex]]);
                }
            }

            if ($this->isTestSQL()) {
                $this->processTestSQL($res);
                $this->connection->rollback();
                die;
            }

            $result += $res->execute(Dibi::AFFECTED_ROWS);

            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollback();
            Debugger::log($e, ILogger::EXCEPTION);
            throw $e;
        }

        // invalidate cache
        $this->cleanCache();

        // set flag success update
        $result = $this->setFlagSuccessUpdate($result);
        if ($result >= 0) {
            // postprocessing after success update
            $this->postProcessSuccessUpdate($values);
        }
        // total postprocessing
        $this->postProcessUpdate($values);
        return $result;
    }


    /**
     * On success delete.
     *
     * @param int $id
     * @return int
     * @throws Exception
     */
    public function onSuccessDelete(int $id): int
    {
        // preprocessed insert values
        $this->preProcessDelete($id);

        $this->connection->begin();
        try {
            $res = null;
            $result = 0;
            if ($this->getFkNameByType('fkpk')) {
                $fkPk = $this->databaseTableListFk[$this->getFkNameByType('fkpk')];

                if ($fkPk['delete_rule'] == 'CASCADE') {
                    // correct delete by fkpk setting (:N) with CASCADE foreign key
                    $res = $this->connection->delete($fkPk['referenced_table_name'])
                        ->where([$fkPk['referenced_column_name'] => $id]);
                }

                if ($fkPk['delete_rule'] == 'NO ACTION') {
                    // fix invalid M:N relationship
                    $fk = $this->connection->select([$fkPk['referenced_column_name'], $fkPk['column_name']])
                        ->from($fkPk['table_name'])
                        ->where([$fkPk['column_name'] => $id])
                        ->fetch();

                    // first remove (M:N) item
                    $res = $this->connection->delete($fkPk['table_name'])
                        ->where([$fkPk['referenced_column_name'] => $fk[$fkPk['referenced_column_name']]]);

                    if ($this->isTestSQL()) {
                        $this->processTestSQL($res);
                    }

                    $result += $res->execute(Dibi::AFFECTED_ROWS);

                    // second remove (:N) item
                    $res = $this->connection->delete($fkPk['referenced_table_name'])
                        ->where([$fkPk['referenced_column_name'] => $id]);
                }
            } else {
                $res = $this->connection->delete($this->getDatabaseTableName())
                    ->where([$this->databaseTablePkIndex => $id]);
            }

            if ($res && $this->isTestSQL()) {
                $this->processTestSQL($res);
                $this->connection->rollback();
                die;
            }

            if ($res) {
                $result += $res->execute(Dibi::AFFECTED_ROWS);
            }

            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollback();
            Debugger::log($e, ILogger::EXCEPTION);
            throw $e;
        }

        // invalidate cache
        $this->cleanCache();

        // set flag success delete
        $result = $this->setFlagSuccessDelete($result);
        if ($result > 0) {
            // postprocessing after success delete
            $this->postProcessSuccessDelete($id);
        }
        // total postprocessing
        $this->postProcessDelete($id);
        return $result;
    }


    /**
     * Delete foreign section.
     *
     * @param int $id
     * @param int $idLocale
     * @return int
     * @throws Exception
     */
    public function deleteForeignSection(int $id, int $idLocale): int
    {
        $values = [];
        if ($this->getFkNameByType('fkpk')) {
            $fkPk = $this->databaseTableListFk[$this->getFkNameByType('fkpk')];
            $values[$fkPk['column_name']] = $id;
        }

        if ($this->getFkNameByType('fkwhere')) {
            $fkWhere = $this->databaseTableListFk[$this->getFkNameByType('fkwhere')];
            $values[$fkWhere['column_name']] = $idLocale;
        }

        if (count($values) == 2) {
            $result = $this->connection->delete($this->getDatabaseTableName())->where($values);
        } else {
            throw new Exception('FK PK or FK WHERE is not define!');
        }

        // invalidate cache
        $this->cleanCache();

        if ($this->isTestSQL()) {
            $this->processTestSQL($result);
            die;
        }
        return $result->execute();
    }
}
