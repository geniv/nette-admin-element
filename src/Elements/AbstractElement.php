<?php declare(strict_types=1);

namespace AdminElement\Elements;

use AdminElement\IConfigureSection;
use Dibi\Fluent;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use AdminElement\WrapperSection;
use Nette\SmartObject;


/**
 * Class AbstractElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
abstract class AbstractElement implements IElement
{
    use SmartObject;

    // define general constant
    const
        DESCRIPTION = '',
        USAGE = [IConfigureSection::PRESENTER_TABLE, IConfigureSection::PRESENTER_FOREIGN, IConfigureSection::PRESENTER_TREE],
        ACTION_TYPES = WrapperSection::ACTION_TYPES_ELEMENT;

    /** @var WrapperSection */
    protected $wrapperSection;
    /** @var string */
    protected $idElement;
    /** @var array */
    protected $configure;


    /**
     * Set wrapper section.
     *
     * @param WrapperSection $wrapperSection
     * @param string         $idElement
     */
    public function __construct(WrapperSection $wrapperSection, string $idElement)
    {
        $this->wrapperSection = $wrapperSection;
        $this->idElement = $idElement;
        $this->configure = $wrapperSection->getItem($idElement);
    }


    /**
     * __toString.
     *
     * @return string
     */
    public function __toString(): string
    {
        $class = get_class($this);  // load class name
        $description = WrapperSection::getClassDescription($class);
        return WrapperSection::getClassName($class) . ($description ? ' - ' . $description : '---');
    }


    /**
     * Get id element.
     *
     * @return string
     */
    public final function getIdElement(): string
    {
        return $this->idElement;
    }


    /**
     * Get configure.
     *
     * @return array
     */
    public function getConfigure(): array
    {
        return $this->configure;
    }


    /**
     * Get translate name content.
     *
     * @return string
     */
    public function getTranslateNameContent(): string
    {
        return (($this->configure['alias'] ?? '') ?: 'content-element-' . $this->configure['type'] . '-' . $this->configure['name']);
    }


    /**
     * Get form container admin.
     *
     * @param Container $form
     * @param string    $prefix
     */
    public function getFormContainerAdmin(Container $form, string $prefix)
    {
        // Administration administration form.

        $translator = $form->getForm()->getTranslator();

        $form->addText('name', $prefix . 'name')
            ->setRequired($prefix . 'name-required');
        $form->addText('alias', $prefix . 'alias');
        $form->addText('defaultvalue', $prefix . 'defaultvalue');
        $form->addText('emptyvalue', $prefix . 'emptyvalue')
            ->setDefaultValue('---');

        $form->addText('required', $prefix . 'required');   // only require text
        $form->addCheckbox('omit', $prefix . 'omit');   // not set with post data
        $form->addCheckbox('ordering', $prefix . 'ordering');   // ordering in grid
        // order default
        $form->addRadioList('orderdefault', $translator->translate($prefix . 'orderdefault'))
            ->setItems(WrapperSection::DEFAULT_ORDER_TYPES)
            ->setTranslator(null);
        if (isset($this->configure['orderdefault']) && $this->configure['orderdefault']) { // if orderdefault is define
            $form->addText('orderposition', $prefix . 'orderposition');     // position order
        }
        $form->addText('hint', $prefix . 'hint');   // hint text in Renderer

        // show for grid
        $form->addCheckboxList('show', $translator->translate($prefix . 'show'))
            ->setItems(WrapperSection::ACTION_TYPES, false)
            ->setTranslator(null);
    }


    /**
     * Get form container content.
     *
     * @param Form $form
     */
    public function getFormContainerContent(Form $form)
    {
        // Administration content form.

        // set value omitted
        if (isset($this->configure['omit']) && $this->configure['omit']) {
            $form[$this->idElement]->setOmitted($this->configure['omit']);
        }

        // set empty value
        if ((isset($this->configure['defaultvalue']) && $this->configure['defaultvalue'])) {
            $emptyValue = $this->configure['defaultvalue'];

            if (!$form[$this->idElement]->getValue()) {
                // insert default Value to value
                $form[$this->idElement]->setValue($emptyValue);
            } else {
                // set DefaultValue to value
                $form[$this->idElement]->setDefaultValue($emptyValue);
            }
        }

        // set required text
        if (isset($this->configure['required']) && $this->configure['required']) {
            $form[$this->idElement]->setRequired($this->configure['required']);   // this method must by call like last
        }

        // set description help
        if (isset($this->configure['hint']) && $this->configure['hint']) {
            $form[$this->idElement]->setOption('hint', $this->configure['hint']);
        }
    }


    /**
     * Get render row.
     *
     * @param $data
     * @return string
     */
    public function getRenderRow($data): string
    {
        // Renderer (format) row of column for grid.
        return (string) $data[$this->idElement];
    }


    /**
     * Get source.
     *
     * @param Fluent $fluent
     * @param bool   $rawSource
     */
    public function getSource(Fluent $fluent, bool $rawSource = false) { }


    /*
     * From wrapper.
     */


    /**
     * Set flag success insert.
     *
     * @param int $value
     * @return int
     */
    public function setFlagSuccessInsert(int $value): int
    {
        return $value;
    }


    /**
     * Set flag success update.
     *
     * @param int $value
     * @return int
     */
    public function setFlagSuccessUpdate(int $value): int
    {
        return $value;
    }


    /**
     * Set flag success delete.
     *
     * @param int $value
     * @return int
     */
    public function setFlagSuccessDelete(int $value): int
    {
        return $value;
    }


    /**
     * Set defaults.
     *
     * @param array $values
     * @return mixed|null
     */
    public function setDefaults(array $values)
    {
        return (isset($values[$this->idElement]) ? $values[$this->idElement] : null);
    }


    /**
     * Pre process ignore values.
     * Define key for array which will be ignored.
     *
     * @return array
     */
    public function preProcessIgnoreValues(): array
    {
        return [];
    }


    /*
     * Insert.
     */


    /**
     * Pre process insert values.
     *
     * @param array $values
     * @return mixed|null
     */
    public function preProcessInsertValues(array $values)
    {
        return $values[$this->idElement] ?? null;
    }


    /**
     * Post process success insert.
     *
     * @param array $values
     */
    public function postProcessSuccessInsert(array $values) { }


    /**
     * Post process insert.
     *
     * @param array $values
     */
    public function postProcessInsert(array $values) { }


    /*
     * Update.
     */


    /**
     * Pre process update values.
     *
     * @param array $values
     * @return string|null
     */
    public function preProcessUpdateValues(array $values)
    {
        return $values[$this->idElement] ?? null;
    }


    /**
     * Post process success update.
     *
     * @param array $values
     */
    public function postProcessSuccessUpdate(array $values) { }


    /**
     * Post process update.
     *
     * @param array $values
     */
    public function postProcessUpdate(array $values) { }


    /*
     * Delete.
     */

    /**
     * Pre process delete.
     *
     * @param int $id
     */
    public function preProcessDelete(int $id) { }


    /**
     * Post process success delete.
     *
     * @param int $id
     */
    public function postProcessSuccessDelete(int $id) { }


    /**
     * Post process delete.
     *
     * @param int $id
     */
    public function postProcessDelete(int $id) { }
}
