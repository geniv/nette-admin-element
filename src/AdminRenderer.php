<?php declare(strict_types=1);

namespace AdminElement;

use Nette\Forms\IControl;
use Nette\Forms\Rendering\DefaultFormRenderer;
use Nette\Utils\Html;


/**
 * Class AdminRenderer
 *
 * @deprecated
 * @author  geniv
 * @package AdminElement
 */
class AdminRenderer extends DefaultFormRenderer
{
    /** @var WrapperSection */
    private $wrapperSection;


    /**
     * AdminRenderer constructor.
     *
     * @param WrapperSection $wrapperSection
     */
    public function __construct(WrapperSection $wrapperSection)
    {
        $this->wrapperSection = $wrapperSection;
    }


    /**
     * Render pair.
     *
     * @param IControl $control
     * @return string
     */
    public function renderPair(IControl $control)
    {
        $item = $this->wrapperSection->getItem($control->getName());

        $pair = $this->getWrapper('pair container');
        $pair->addHtml($this->renderLabel($control));

        $cont = $this->renderControl($control);
//        if ($control instanceof Checkbox) {}
        $pair->addHtml($cont);

        // hint napoveda elementu se vklada pred description
        if (isset($item['hint'])) {
            $pair->addHtml(Html::el('span', ['class' => 'help-block'])->setHtml($item['hint']));
        }

        // vkladani description
//        if ($control->getOption('description')) {
//            $pair->addHtml($control->getOption('description'));  // nesmi se zapominat na description
//        }

        $pair->class($this->getValue($control->isRequired() ? 'pair .required' : 'pair .optional'), TRUE);
        $pair->class($control->hasErrors() ? $this->getValue('pair .error') : NULL, TRUE);
        $pair->class($control->getOption('class'), TRUE);
//        $pair->class('form-group-' . $groupName, true);  // vlozeni tridy podle typu elementu
        if (++$this->counter % 2) {
            $pair->class($this->getValue('pair .odd'), TRUE);
        }
        $pair->id = $control->getOption('id');
        return $pair->render(0);
    }
}
