<?php declare(strict_types=1);

namespace AdminElement\Elements;

use AdminElement\WrapperSection;


/**
 * Class ArchiveElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class ArchiveElement extends HiddenElement
{
    const
        DESCRIPTION = 'archive/deleted element for archive row in 1:N table',
        ACTION_TYPES = [WrapperSection::ACTION_EDIT];


    /**
     * Pre process update values.
     *
     * @param array $values
     * @return string|null
     */
    public function preProcessUpdateValues(array $values)
    {
        dump($values, 'coze?!');

        //TODO dodelat vykonovou logiku!!!
        return parent::preProcessUpdateValues($values); // TODO: Change the autogenerated stub
    }
}
