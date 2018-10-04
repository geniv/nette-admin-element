<?php declare(strict_types=1);

namespace AdminElement\Elements;

use Dibi\Fluent;
use Nette\Application\UI\Form;
use Nette\Forms\Container;


/**
 * Interface IElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
interface IElement
{

    /**
     * Get translate name content.
     *
     * @return string
     */
    public function getTranslateNameContent(): string;


    /**
     * Get form container admin.
     *
     * @param Container $form
     * @param string    $prefix
     */
    public function getFormContainerAdmin(Container $form, string $prefix);


    /**
     * Get form container content.
     *
     * @param Form $form
     */
    public function getFormContainerContent(Form $form);


    /**
     * Get render row.
     *
     * @param $data
     * @return string
     */
    public function getRenderRow($data): string;


    /**
     * Get source.
     *
     * @param Fluent $fluent
     */
    public function getSource(Fluent $fluent,bool $rawSource=false);
}
