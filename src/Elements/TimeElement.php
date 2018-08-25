<?php declare(strict_types=1);

namespace AdminElement\Elements;


/**
 * Class TimeElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class TimeElement extends AbstractDateTimeElement
{
    const
        DESCRIPTION = 'time element for time in 1:N table',
        DEFAULT_FORMAT = 'H:i:s',
        SYSTEM_FORMAT = 'H:i';
}
