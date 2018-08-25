<?php declare(strict_types=1);

namespace AdminElement\Elements;


/**
 * Class DateElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class DateElement extends AbstractDateTimeElement
{
    const
        DESCRIPTION = 'date element for date in 1:N table',
        DEFAULT_FORMAT = 'd.m.Y',
        SYSTEM_FORMAT = 'Y-m-d';
}
