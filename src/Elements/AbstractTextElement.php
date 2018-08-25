<?php declare(strict_types=1);

namespace AdminElement\Elements;

use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Utils\Strings;


/**
 * Class AbstractTextElement
 *
 * @author  geniv
 * @package AdminElement\Element
 */
class AbstractTextElement extends AbstractElement
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

        $form->addText('truncate', $prefix . 'truncate')
            ->setDefaultValue(70);
        $form->addText('placeholder', $prefix . 'placeholder');
        $form->addText('textformat', $prefix . 'textformat')
            ->setOption('hint', $prefix . 'textformat-hint');
        $form->addCheckbox('readonly', $prefix . 'readonly');
    }


    /**
     * Get form container content.
     *
     * @param Form $form
     */
    public function getFormContainerContent(Form $form)
    {
        if (isset($this->configure['placeholder']) && $this->configure['placeholder']) {
            $form[$this->idElement]->setAttribute('placeholder', $this->configure['placeholder']);
        }

        if (isset($this->configure['readonly']) && $this->configure['readonly']) {
            $form[$this->idElement]->setAttribute('readonly', $this->configure['readonly']);
        }
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
        $string = parent::getRenderRow($data);

        // remove html tags + &nbsp;
        $string = html_entity_decode(strip_tags($string));

        // truncate long text
        if (isset($this->configure['truncate']) && $this->configure['truncate']) {
            $string = Strings::truncate($string, $this->configure['truncate']);
        }

        // format output by sprintf
        if (isset($this->configure['textformat']) && $this->configure['textformat']) {
            $string = sprintf($this->configure['textformat'], $string);
        }
        return $string;
    }


    /**
     * Pre process insert values.
     *
     * @param array $values
     * @return mixed|null|string
     */
    public function preProcessInsertValues(array $values)
    {
        // if value is empty will be save NULL
        return $this->preProcessUpdateValues($values);
    }


    /**
     * Pre process update values.
     *
     * @param array $values
     * @return mixed|null|string
     */
    public function preProcessUpdateValues(array $values)
    {
        // if value is empty will be save NULL
        return (isset($values[$this->idElement]) && Strings::length($values[$this->idElement]) > 0 ? $values[$this->idElement] : null);
    }
}
