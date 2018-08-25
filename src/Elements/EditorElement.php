<?php declare(strict_types=1);

namespace AdminElement\Elements;

use Admin\App\Presenters\FileSystemPresenter;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Utils\Callback;


/**
 * Class EditorElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class EditorElement extends AbstractTextElement
{
    const
        DESCRIPTION = 'WYSIWYG element for big text in 1:N table';


    /**
     * Get form container admin.
     *
     * @param Container $form
     * @param string    $prefix
     */
    public function getFormContainerAdmin(Container $form, string $prefix)
    {
        parent::getFormContainerAdmin($form, $prefix);  // first position

        // fileslist value
        $form->addCheckbox('fileslist', $prefix . 'fileslist');
    }


    /**
     * Get form container content.
     *
     * @param Form $form
     */
    public function getFormContainerContent(Form $form)
    {
        // textarea element
        $form->addTextArea($this->idElement, $this->getTranslateNameContent());

        // set fileslist
        if (isset($this->configure['fileslist'])) {
            $form[$this->idElement]->setOption('fileslist', $this->configure['fileslist']);
            $webDir = $this->wrapperSection->getConfigureParameterByIndex('webDir');
            $path = $webDir . FileSystemPresenter::FILES_DIR;
            $form[$this->idElement]->setOption('list', FileSystemPresenter::getListFiles($path));
            $form[$this->idElement]->setOption('path', '../' . FileSystemPresenter::FILES_DIR);
            $form[$this->idElement]->setOption('isImage', Callback::closure(FileSystemPresenter::class . '::isImage'));
        }

        parent::getFormContainerContent($form); // last position
    }
}
