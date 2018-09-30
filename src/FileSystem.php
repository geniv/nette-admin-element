<?php declare(strict_types=1);

namespace AdminElement;

use Nette\StaticClass;
use Nette\Utils\Finder;
use SplFileInfo;


/**
 * Class FileSystem
 *
 * @author  geniv
 * @package AdminElement
 */
class FileSystem
{
    use StaticClass;

    // path for FileSystemPresenter and EditorElement
    const
        FILES_DIR = 'www/files/file/',
        FILES_IMAGE = ['image/gif', 'image/png', 'image/jpeg'];


    /**
     * Is image.
     *
     * @param $file
     * @return bool
     */
    public static function isImage(SplFileInfo $file): bool
    {
        // check image system by: https://api.nette.org/2.4/source-Http.FileUpload.php.html#175-182
        $type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file->getRealPath());
        return in_array($type, self::FILES_IMAGE, true);
    }


    /**
     * Get list files.
     *
     * @param $path
     * @return array
     */
    public static function getListFiles($path): array
    {
        $finder = Finder::findFiles('*');
        $list = iterator_to_array($finder->in($path));

        // user sort method
        usort($list, function ($a, $b) {
            return $a->getMTime() > $b->getMTime();
        });
        return $list;
    }
}
