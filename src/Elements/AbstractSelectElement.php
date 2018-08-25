<?php declare(strict_types=1);

namespace AdminElement\Elements;


/**
 * Class AbstractFkSelectElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
abstract class AbstractSelectElement extends AbstractElement
{

    /**
     * Get select items.
     *
     * @param array $configure
     * @return array
     */
    abstract public function getSelectItems(array $configure): array;


//    /**
//     * Get form container admin.
//     *
//     * @param Container $form
//     * @param string    $prefix
//     */
//    public function getFormContainerAdmin(Container $form, string $prefix)
//    {
//        parent::getFormContainerAdmin($form, $prefix);  // first position
//
//        $form->addText('truncate', $prefix . 'truncate')
//            ->setDefaultValue(70);
//    }
}
