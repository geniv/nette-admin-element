<?php declare(strict_types=1);

namespace AdminElement\Elements;

use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Utils\Html;
use Thumbnail\Thumbnail;


/**
 * Class AbstractFkSelectElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
abstract class AbstractFkSelectElement extends AbstractSelectElement
{

    /**
     * Get absolute path.
     *
     * @internal
     * @return string
     */
    private function getAbsolutePath(): string
    {
        $result = '';
        if (isset($this->configure['path'])) {
            // get absolute path
            if (is_dir($this->configure['path'] . '/')) {
                $result = $this->configure['path'] . '/';
            } else {
                $webDir = $this->wrapperSection->getConfigureParameterByIndex('webDir');
                $result = $webDir . $this->configure['path'];
            }
        }
        return $result;
    }


    /**
     * Get relative path.
     *
     * @internal
     * @return string
     */
    private function getRelativePath(): string
    {
        $result = '';
        if (isset($this->configure['path'])) {
            // get relative path
            if (is_dir($this->configure['path'] . '/')) {
                $webDir = $this->wrapperSection->getConfigureParameterByIndex('webDir');
                $result = '../' . substr($this->configure['path'] . '/', strlen($webDir));
            } else {
                $result = '../' . $this->configure['path'];
            }
        }
        return $result;
    }


    /**
     * Get items.
     *
     * @param array $configure
     * @return array
     */
    public function getSelectItems(array $configure): array
    {
        $result = [];
        if (isset($configure['fkextra'])) {
            // M:N with extra relationship :M
            $result = $this->wrapperSection->getDataByFk($configure['fkextra'], $configure['preview'], false);
        } else {
            // load data by fk and preview string
            if (isset($configure['fk'])) {
                $result = $this->wrapperSection->getDataByFk($configure['fk'], $configure['preview']);
            }
        }
        $result = array_map('strip_tags', $result);

        if (isset($configure['image']) && $configure['image']) {
            // generate thumbnail files
            $relativePath = $this->getRelativePath();
            $result = (array_map(function ($baseName) use ($relativePath) {
                return Html::el()->setText($baseName)->data('thumb', Thumbnail::getSrcPath($relativePath, $baseName, null, '64'));
            }, $result));
        }
        return $result;
    }


    /**
     * Get form container admin.
     *
     * @param Container $form
     * @param string    $prefix
     */
    public function getFormContainerAdmin(Container $form, string $prefix)
    {
        parent::getFormContainerAdmin($form, $prefix);  // first position

        $translator = $form->getForm()->getTranslator();

        // enable image select
        $form->addCheckbox('image', $prefix . 'image');
        if (isset($this->configure['image']) && $this->configure['image']) {
            // paths in configure
            $form->addSelect('path', $translator->translate($prefix . 'path'))
                ->setPrompt($translator->translate($prefix . 'path-prompt'))
                ->setItems($this->wrapperSection->getListPathParameters())
                ->setTranslator(null);
        }
    }


    /**
     * Get form container content.
     *
     * @param Form $form
     */
    public function getFormContainerContent(Form $form)
    {
        $items = $this->getSelectItems($this->configure);

        if ($this->wrapperSection->getSubSectionId()) {
            $subElement = $this->wrapperSection->getSubElementName();
            // select one select element
            if ($subElement == $this->idElement) {
                $this->configure['defaultvalue'] = $this->wrapperSection->getSubSectionId();
            }
        }

        // select element
        $form->addSelect($this->idElement, $this->getTranslateNameContent())
            ->setItems($items)
            ->setTranslator(null);

        // enable image to select
        if (isset($this->configure['image']) && $this->configure['image']) {
            $form[$this->idElement]->setOption('image', true);
        }

        // set prompt
        if (isset($this->configure['prompt']) && $this->configure['prompt']) {
            $form[$this->idElement]->setPrompt($this->configure['prompt']);
        }
        parent::getFormContainerContent($form); // last position
    }


    /**
     * Get render row.
     *
     * @param $data
     * @return string
     * @throws \Exception
     */
    public function getRenderRow($data): string
    {
        $configure = $this->configure;
        $configure['image'] = false;
        $items = $this->getSelectItems($configure);

        if (isset($this->configure['image']) && $this->configure['image']) {
            $item = $items[$data[$this->idElement]];
            if (file_exists($this->getAbsolutePath() . $item) && is_file($this->getAbsolutePath() . $item)) {
                // via code: \AdminElement\Elements\AbstractUploadImageElement::getRenderRow
                $src = Thumbnail::getSrcPath($this->getRelativePath(), $item, null, '64');
                $html = Html::el('img', ['src' => $src]);
                return (string) $html;
            }
        }
        return $items[$data[$this->idElement]] ?? $this->configure['defaultvalue'];
    }
}
