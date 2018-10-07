<?php declare(strict_types=1);

namespace AdminElement\Elements;

use Nette\Application\UI\Form;
use Nette\Forms\Container;


/**
 * Class AbstractDateTimeElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
abstract class AbstractDateTimeElement extends AbstractTextElement
{

    /**
     * Get form container admin.
     *
     * @param Container $form
     * @param string    $prefix
     */
    public function getFormContainerAdmin(Container $form, string $prefix)
    {
        parent::getFormContainerAdmin($form, $prefix);  // first position

        // remove elements
        unset($form['truncate'], $form['textformat'], $form['readonly'], $form['defaultvalue'], $form['placeholder']);

        $form->addText('format', $prefix . 'format')
            ->setDefaultValue(static::DEFAULT_FORMAT);

        if ($this instanceof AbstractDateTimePickerElement) {
            // picker element
        } else {
            // remove elements
            unset($form['required']);

            $form->addCheckbox('currentdatetime', $prefix . 'currentdatetime');
        }
    }


    /**
     * Get form container content element.
     *
     * @param Form $form
     */
    protected function getFormContainerContentElement(Form $form)
    {
        // label element
        $form->addLabel($this->idElement, $this->getTranslateNameContent());
    }


    /**
     * Get form container content.
     *
     * @param Form $form
     */
    public function getFormContainerContent(Form $form)
    {
        // internal element form
        $this->getFormContainerContentElement($form);

        parent::getFormContainerContent($form); // last position
    }


    /**
     * Set defaults.
     *
     * @param array $values
     * @return array|mixed|null
     */
    public function setDefaults(array $values)
    {
        if (isset($this->configure['format'])) {
            $value = $values[$this->idElement];
            if ($value instanceof \DateTime) {
                $values[$this->idElement] = $value->format(static::SYSTEM_FORMAT);
            }
        }
        return $values;
    }


    /**
     * Pre process insert values.
     *
     * @param array $values
     * @return null|string
     */
    public function preProcessInsertValues(array $values)
    {
        return $this->preProcessUpdateValues($values);
    }


    /**
     * Pre process update values.
     *
     * @param array $values
     * @return string|null
     */
    public function preProcessUpdateValues(array $values)
    {
        if (isset($this->configure['currentdatetime']) && $this->configure['currentdatetime']) {
            // current date format for save
            $values[$this->idElement] = date(static::SYSTEM_FORMAT);
        }
        return parent::preProcessUpdateValues($values);
    }


    /**
     * Get render row.
     *
     * @param $data
     * @return string
     */
    public function getRenderRow($data): string
    {
        $item = $data[$this->idElement];
        if (isset($this->configure['format'])) {
            if (is_string($item)) {
                // invalidate date/time format
                if (!strtotime($item)) {
                    return '';
                }
                // string convert to datetime
                $item = new \DateTime($item);
            }
            if ($item instanceof \DateTime) {
                // format output
                return $item->format($this->configure['format']);
            }
        }
        return parent::getRenderRow($data);
    }
}
