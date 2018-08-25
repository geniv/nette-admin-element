<?php declare(strict_types=1);

namespace AdminElement\Elements;


/**
 * Class DateTimePickerElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class DateTimePickerElement extends AbstractDateTimePickerElement
{
    const
        DESCRIPTION = 'datetime picker element for date and time in 1:N table',
        DEFAULT_FORMAT = 'd.m.Y H:i',
        SYSTEM_FORMAT = 'Y-m-d H:i';
}
