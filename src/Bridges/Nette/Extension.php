<?php declare(strict_types=1);

namespace AdminElement\Bridges\Nette;

use AdminElement\AdminElement;
use AdminElement\Elements\CheckboxElement;
use AdminElement\Elements\DateElement;
use AdminElement\Elements\DatePickerElement;
use AdminElement\Elements\DateTimeElement;
use AdminElement\Elements\DateTimePickerElement;
use AdminElement\Elements\EditorElement;
use AdminElement\Elements\FkSelectElement;
use AdminElement\Elements\ForeignCheckboxElement;
use AdminElement\Elements\ForeignDateTimeElement;
use AdminElement\Elements\ForeignDateTimePickerElement;
use AdminElement\Elements\ForeignEditorElement;
use AdminElement\Elements\ForeignFkPkElement;
use AdminElement\Elements\ForeignFkWhereElement;
use AdminElement\Elements\ForeignHiddenElement;
use AdminElement\Elements\ForeignLabelElement;
use AdminElement\Elements\ForeignPositionElement;
use AdminElement\Elements\ForeignSelectElement;
use AdminElement\Elements\ForeignTextAreaElement;
use AdminElement\Elements\ForeignTextElement;
use AdminElement\Elements\ForeignUploadElement;
use AdminElement\Elements\ForeignUploadImageElement;
use AdminElement\Elements\HiddenElement;
use AdminElement\Elements\LabelElement;
use AdminElement\Elements\PasswordElement;
use AdminElement\Elements\PositionElement;
use AdminElement\Elements\RadioElement;
use AdminElement\Elements\SelectElement;
use AdminElement\Elements\TextAreaElement;
use AdminElement\Elements\TextElement;
use AdminElement\Elements\TimeElement;
use AdminElement\Elements\TimePickerElement;
use AdminElement\Elements\UploadElement;
use AdminElement\Elements\UploadImageElement;
use AdminElement\WrapperSection;
use Nette\DI\CompilerExtension;


/**
 * Class Extension
 *
 * @author  geniv
 * @package AdminElement\Bridges\Nette
 */
class Extension extends CompilerExtension
{
    /** @var array default values */
    private $defaults = [
        'elements' => [
            'label'                 => LabelElement::class,
            'hidden'                => HiddenElement::class,
            'text'                  => TextElement::class,
            'textarea'              => TextAreaElement::class,
            'editor'                => EditorElement::class,
            'password'              => PasswordElement::class,
            'date'                  => DateElement::class,
            'datepicker'            => DatePickerElement::class,
            'time'                  => TimeElement::class,
            'timepicker'            => TimePickerElement::class,
            'datetime'              => DateTimeElement::class,
            'datetimepicker'        => DateTimePickerElement::class,
            'checkbox'              => CheckboxElement::class,
            'position'              => PositionElement::class,
            'upload'                => UploadElement::class,
            'uploadimage'           => UploadImageElement::class,
            'radio'                 => RadioElement::class,
            'select'                => SelectElement::class,
            'fkselect'              => FkSelectElement::class,
            'foreignfkpk'           => ForeignFkPkElement::class,
            'foreignfkwhere'        => ForeignFkWhereElement::class,
            'foreignlabel'          => ForeignLabelElement::class,
            'foreignhidden'         => ForeignHiddenElement::class,
            'foreigntext'           => ForeignTextElement::class,
            'foreigntextarea'       => ForeignTextAreaElement::class,
            'foreigneditor'         => ForeignEditorElement::class,
            'foreignselect'         => ForeignSelectElement::class,
            'foreignupload'         => ForeignUploadElement::class,
            'foreignuploadimage'    => ForeignUploadImageElement::class,
            'foreigndatetime'       => ForeignDateTimeElement::class,
            'foreigndatetimepicker' => ForeignDateTimePickerElement::class,
            'foreigncheckbox'       => ForeignCheckboxElement::class,
            'foreignposition'       => ForeignPositionElement::class,
        ],
    ];


    /**
     * Load configuration.
     */
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();
        $config = $this->validateConfig($this->defaults);

        // load elements
        $elements = [];
        foreach ($config['elements'] as $name => $element) {
            $elements[$name] = $builder->addDefinition($this->prefix($name))
                ->setFactory($element)
                ->setAutowired(true);
        }

        $builder->addDefinition($this->prefix('default'))
            ->setFactory(AdminElement::class, [$elements])
            ->setAutowired(true);

        $builder->addDefinition($this->prefix('wrapper'))
            ->setFactory(WrapperSection::class)
            ->setAutowired(true);
    }
}
