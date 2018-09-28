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
}
