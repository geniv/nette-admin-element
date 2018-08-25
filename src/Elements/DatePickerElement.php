<?php declare(strict_types=1);

namespace AdminElement\Elements;


/**
 * Class DatePickerElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class DatePickerElement extends AbstractDateTimePickerElement
{
    const
        DESCRIPTION = 'date picker element for date in 1:N table',
        DEFAULT_FORMAT = 'd.m.Y',
        SYSTEM_FORMAT = 'Y-m-d';
}
