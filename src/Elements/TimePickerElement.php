<?php declare(strict_types=1);

namespace AdminElement\Elements;


/**
 * Class TimePickerElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class TimePickerElement extends AbstractDateTimePickerElement
{
    const
        DESCRIPTION = 'time picker element for time in 1:N table',
        DEFAULT_FORMAT = 'H:i:s',
        SYSTEM_FORMAT = 'H:i';
}
