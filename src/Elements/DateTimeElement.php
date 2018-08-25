<?php declare(strict_types=1);

namespace AdminElement\Elements;


/**
 * Class DateTimeElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class DateTimeElement extends AbstractDateTimeElement
{
    const
        DESCRIPTION = 'datetime element for date and time in 1:N table',
        DEFAULT_FORMAT = 'd.m.Y H:i',
        SYSTEM_FORMAT = 'Y-m-d H:i';
}
