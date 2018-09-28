<?php declare(strict_types=1);

namespace AdminElement\Elements;

use AdminElement\IConfigureSection;
use AdminElement\WrapperSection;
use Nette\Application\UI\Form;
use Nette\Forms\Container;


/**
 * Class ForeignFkWhereElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class ForeignFkWhereElement extends AbstractElement
{
    // define general constant
    const
        DESCRIPTION = 'FK WHERE (M:)',
        USAGE = [IConfigureSection::PRESENTER_FOREIGN],
        ACTION_TYPES = [WrapperSection::ACTION_LIST, WrapperSection::ACTION_ADD, WrapperSection::ACTION_EDIT];


    /**
     * Get form container admin.
     *
     * @param Container $form
     * @param string    $prefix
     */
    public function getFormContainerAdmin(Container $form, string $prefix)
    {
        parent::getFormContainerAdmin($form, $prefix);  // first position

        // remove default order
        unset($form['orderdefault']);

        $translator = $form->getForm()->getTranslator();

        $fkItems = $this->wrapperSection->getListDatabaseFk($this->wrapperSection->getDatabaseTableName());
        $form->addSelect('foreign', $translator->translate($prefix . 'foreign'))
            ->setRequired($prefix . 'foreign-required')
            ->setPrompt($translator->translate($prefix . 'foreign-prompt'))
            ->setItems($fkItems)
            ->setTranslator(null);

        // select preview
        $form->addText('preview', $prefix . 'preview')
            ->setRequired($prefix . 'preview-required');
        // preview for getRenderRow
        $form->addText('fkcode', $prefix . 'fkcode')
            ->setOption('hint', $prefix . 'fkcode-hint');
        // group by fkId
        $form->addCheckbox('fkid', $prefix . 'fkid');
        // select first value
        $form->addCheckbox('fkidfirst', $prefix . 'fkidfirst');
        // set fkid for list in grid
        $form->addText('fkdefaultid', $prefix . 'fkdefaultid')
            ->setOption('hint', $prefix . 'fkdefaultid-hint');
        // set exclude render item (eg Flag picture)
        if (isset($this->configure['foreign']) && $this->configure['foreign'] && $this->configure['preview']) {
            $items = $this->wrapperSection->getDataByFk($this->configure['foreign'], $this->configure['preview']);
            $form->addMultiSelect('fkexclude', $translator->translate($prefix . 'fkexclude'))
                ->setItems($items)
                ->setTranslator(null);
        }

        $this->wrapperSection->setForeign($this, 'fkwhere');
    }


    /**
     * Get form container content.
     *
     * @param Form $form
     */
    public function getFormContainerContent(Form $form)
    {
        // hidden element
        $form->addHidden($this->idElement); // only hidden => show minimum for add+edit!

        parent::getFormContainerContent($form); // last position
    }


    /**
     * Get render row.
     *
     * @param $data
     * @return string
     */
    public function getRenderRow($data): string
    {
        if (isset($this->configure['fkcode']) && $this->configure['fkcode']) {
            $items = $this->wrapperSection->getDataByFk($this->configure['foreign'], $this->configure['fkcode']);
            $fkPk = $this->wrapperSection->getConfigureSectionValue('database')['fkpk'];
            $active = array_keys($this->wrapperSection->getMByIdN($data[$fkPk]));
            if (isset($this->configure['fkexclude']) && $this->configure['fkexclude']) {
                foreach ($this->configure['fkexclude'] as $id) {
                    unset($items[$id]); // remove exclude index from items
                }
                // remove exclude value from active
                $active = array_filter($active, function ($item) { return !in_array($item, $this->configure['fkexclude']); });
            }
            asort($active); // sort by values
            return serialize(['items' => $items, 'active' => $active]);
        }
        return '';
    }
}
