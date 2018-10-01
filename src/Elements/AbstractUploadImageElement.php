<?php declare(strict_types=1);

namespace AdminElement\Elements;

use Nette\Application\UI\Form;
use Nette\Utils\Html;
use Thumbnail\Thumbnail;


/**
 * Class AbstractUploadImageElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
abstract class AbstractUploadImageElement extends UploadElement
{

    /**
     * Get form container content element.
     *
     * @param Form $form
     */
    protected function getFormContainerContentElement(Form $form)
    {
        $values = $this->wrapperSection->getDatabaseValues();
        // uploadimage element
        $form->addUploadImage($this->idElement, $this->getTranslateNameContent())
            ->setPath($this->getRelativePath())
            ->setImageSize(null, '200')
            ->setValue($values[$this->idElement . 'select'])
            ->setOption('image', true);
    }


    /**
     * Get form container content.
     *
     * @param Form $form
     * @throws \Exception
     */
    public function getFormContainerContent(Form $form)
    {
        parent::getFormContainerContent($form); // last position (exception for this usage)

        $select = $form[$this->idElement . 'select'];
        if ($select instanceof \Nette\Forms\Controls\SelectBox) {
            $select->setOption('image', true);  // enable image on select2
        }
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
        $item = $data[$this->idElement];
        if (file_exists($this->getAbsolutePath() . $item) && is_file($this->getAbsolutePath() . $item)) {
            $src = Thumbnail::getSrcPath($this->getRelativePath(), $item, null, '64');
            $html = Html::el('img', ['src' => $src]);
            return (string) $html;
        }
        return '';
    }
}
