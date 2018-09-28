<?php declare(strict_types=1);

namespace AdminElement\Elements;

use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Forms\Container;
use Nette\Http\FileUpload;
use Nette\Utils\Finder;
use Nette\Utils\Html;
use Thumbnail\Thumbnail;


/**
 * Class UploadElement
 *
 * @author  geniv
 * @package AdminElement\Elements
 */
class UploadElement extends AbstractElement
{
    const
        DESCRIPTION = 'upload element for 1:N table';

    /** @var string */
    private $deleteFile;
    /** @var Cache */
    protected $cache;


    /**
     * UploadElement constructor.
     *
     * @param IStorage $storage
     */
    public function __construct(IStorage $storage)
    {
        $this->cache = new Cache($storage, 'UploadElement');
    }


    /**
     * Get form container admin.
     *
     * @param Container $form
     * @param string    $prefix
     */
    public function getFormContainerAdmin(Container $form, string $prefix)
    {
        parent::getFormContainerAdmin($form, $prefix);   // first position

        // remove default order
        unset($form['defaultvalue']);

        $translator = $form->getForm()->getTranslator();

        // paths in configure
        $form->addSelect('path', $translator->translate($prefix . 'path'))
            ->setPrompt($translator->translate($prefix . 'path-prompt'))
            ->setItems($this->wrapperSection->getListPathParameters())
            ->setTranslator(null);

        // share files
        $form->addCheckbox('share', $prefix . 'share');
        // share filter to same file
        $form->addCheckbox('sharefilter', $prefix . 'sharefilter');
        // text on button
        $form->addText('textbutton', $prefix . 'textbutton')
            ->setDefaultValue('Vložit soubor');
    }


    /**
     * Get absolute path.
     *
     * @internal
     * @return string
     */
    protected function getAbsolutePath(): string
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
    protected function getRelativePath(): string
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
     * Get list files.
     *
     * @internal
     * @return array
     */
    private function getListFiles(): array
    {
        $isImage = ($this instanceof \AdminElement\Elements\AbstractUploadImageElement);

        $cacheName = 'getListFiles' . $this->idElement . $isImage;
        $result = $this->cache->load($cacheName);
        if ($result === null) {
            if ($this->getAbsolutePath()) {
                $finder = Finder::findFiles($this->configure['sharefilter'] ?? false ? '*-' . $this->idElement . '-*' : '*');
                $files = $finder->in($this->getAbsolutePath());
                $internalItems = array_map('basename', iterator_to_array($files));

                if ($isImage) {
                    // generate thumbnail files for only *UploadImageElement
                    $relativePath = $this->getRelativePath();
                    $images = (array_map(function ($baseName) use ($relativePath) {
                        return Html::el()->setText($baseName)->data('thumb', Thumbnail::getSrcPath($relativePath, $baseName, null, '64'));
                    }, $internalItems));
                    // combine arrays
                    $result = array_combine($internalItems, $images);
                } else {
                    // combine arrays
                    $result = array_combine($internalItems, $internalItems);
                }
                try {
                    $this->cache->save($cacheName, $result, [Cache::TAGS => 'upload']);
                } catch (\Throwable $e) {
                }
            }
        }
        return $result ?? [];
    }


    /**
     * Get form container content element.
     *
     * @internal
     * @param Form $form
     */
    protected function getFormContainerContentElement(Form $form)
    {
        // upload element
        $form->addUploadFile($this->idElement, $this->getTranslateNameContent())
            ->setPath($this->getRelativePath(), $this->getAbsolutePath())
            ->setTarget('_blank');
    }


    /**
     * Get form container content.
     *
     * @param Form $form
     */
    public function getFormContainerContent(Form $form)
    {
        $translator = $form->getForm()->getTranslator();

        // set begin group
        $form->addGroup($this->idElement);

        // internal element form
        $this->getFormContainerContentElement($form);

        // set textbutton
        if (isset($this->configure['textbutton'])) {
            $form[$this->idElement]->setOption('textbutton', $this->configure['textbutton']);
        }

        if (isset($this->configure['share']) && $this->configure['share']) {
            // select share files
            $items = $this->getListFiles();
            $form->addSelect($this->idElement . 'select', $translator->translate('upload-element-select'))
                ->setPrompt($translator->translate('upload-element-select-prompt'))
                ->setItems($items)
                ->setTranslator(null);
        } else {
            // like select
            $form->addHidden($this->idElement . 'select');
        }
        // nullable value
        $form->addCheckbox($this->idElement . 'reset', 'upload-element-reset');
        // delete value
        $form->addCheckbox($this->idElement . 'delete', 'upload-element-delete');

        // set end group
        $form->addGroup();

        if (isset($this->configure['required']) && $this->configure['required']) {
            // set addConditionOn to require
            $form[$this->idElement]->addConditionOn($form[$this->idElement . 'select'], Form::BLANK)
                ->setRequired($this->configure['required']);
            $this->configure['required'] = null;
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
        $item = $data[$this->idElement];
        if (file_exists($this->getAbsolutePath() . $item) && is_file($this->getAbsolutePath() . $item)) {
            $html = Html::el('a');
            $html->href = $this->getRelativePath() . $item;
            $html->target = '_blank';
            $html->setText($item);  //TODO truncate na nazev
            return (string) $html;
        }
        return '';
    }


    /**
     * Pre process insert values.
     *
     * @param array $values
     * @return mixed|null|string
     */
    public function preProcessInsertValues(array $values)
    {
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
        $file = $values[$this->idElement] ?? null;
        if ($file && ($file instanceof FileUpload) && $file->isOk()) {
//            $sanitizedName = date('Y-m-d-H-i-s') . '-' . $this->idElement . '-' . $file->getSanitizedName();
            $pathInfo = pathinfo($file->getSanitizedName());
            $sanitizedName = $pathInfo['filename'] . '-' . $this->idElement . '-' . date('Y-m-d-H-i-s') . (isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '');

            $file->move($this->getAbsolutePath() . $sanitizedName);

            // invalidate cache
            $this->cache->clean([Cache::TAGS => 'upload']);

            $values[$this->idElement] = $sanitizedName;
        } else {
            if ($file && ($file instanceof FileUpload) && $file->getError()) {
//                dump($file->getError());
//                $phpFileUploadErrors = array(
//                    0 => 'There is no error, the file uploaded with success',
//                    1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
//                    2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
//                    3 => 'The uploaded file was only partially uploaded',
//                    4 => 'No file was uploaded',
//                    6 => 'Missing a temporary folder',
//                    7 => 'Failed to write file to disk.',
//                    8 => 'A PHP extension stopped the file upload.',
//                );
//                dump($phpFileUploadErrors[$file->getError()]);
//                throw new \Exception($file->getError());
            }

            // reset (remove) file
            if ((isset($values[$this->idElement . 'reset']) && $values[$this->idElement . 'reset']) ||
                isset($values[$this->idElement . 'delete']) && $values[$this->idElement . 'delete']) {
                if ($values[$this->idElement . 'delete']) {
                    $this->deleteFile = $values[$this->idElement . 'select']; // store selected file for delete
                }
                $values[$this->idElement . 'select'] = null;
                $values[$this->idElement] = null;
            }

            // set file to select/hidden
            if (isset($values[$this->idElement . 'select']) && $values[$this->idElement . 'select']) {
                $values[$this->idElement] = $values[$this->idElement . 'select'];
            }

            // nullable empty file
            if ((!$file || !(($file instanceof FileUpload) && $file->hasFile())) && !$values[$this->idElement . 'select']) {
                $values[$this->idElement] = null;
            }
        }
        return $values[$this->idElement];
    }


    /**
     * Remove file.
     *
     * @param string $file
     */
    private function removeFile(string $file)
    {
        if (file_exists($file)) {
            @unlink($file);
        }

        // invalidate cache
        $this->cache->clean([Cache::TAGS => 'upload']);
    }


    /**
     * Post process success update.
     *
     * @param array $values
     */
    public function postProcessSuccessUpdate(array $values)
    {
        // remove file selected like "reset"
        if ($this->deleteFile) {
            $this->removeFile($this->getAbsolutePath() . $this->deleteFile);
        }
    }


    /**
     * Post process success delete.
     *
     * @param int $id
     */
    public function postProcessSuccessDelete(int $id)
    {
        if (isset($this->configure['share']) && !$this->configure['share']) {
            $values = $this->wrapperSection->getConfigureSectionValue('values');
            $this->removeFile($this->getAbsolutePath() . $values);
        }
    }


    /**
     * Set defaults.
     *
     * @param array $values
     * @return array|mixed|null
     */
    public function setDefaults(array $values)
    {
        if (isset($this->configure['share']) && $this->configure['share']) {
            $items = $this->getListFiles();

            // check exist value in array
            if (in_array($values[$this->idElement], $items)) {
                $values[$this->idElement . 'select'] = $values[$this->idElement];
            } else {
                $values[$this->idElement . 'select'] = null;
            }
        } else {
            $values[$this->idElement . 'select'] = $values[$this->idElement];
        }
        return $values;
    }


    /**
     * Pre process ignore values.
     *
     * @return array
     */
    public function preProcessIgnoreValues(): array
    {
        return [$this->idElement . 'reset', $this->idElement . 'delete', $this->idElement . 'select'];
    }
}
