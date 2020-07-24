<?php


namespace bfinlay\SpreadsheetSeeder;

/**
 * Consider enhancing with FlySystem integration
 */
class FileIterator extends \AppendIterator
{
    private $count;

    /**
     * @var SpreadsheetSeederSettings
     */
    private $settings;

    /**
     * FileIterator constructor.
     * @param SpreadsheetSeederSettings $settings
     */
    public function __construct()
    {
        parent::__construct();
        $this->settings = resolve(SpreadsheetSeederSettings::class);

        $flags = \FilesystemIterator::KEY_AS_FILENAME;

        $globs = $this->settings->file;
        if (! is_array($globs)) {
            $globs = [$globs];
        }

        foreach ($globs as $glob) {
            if (is_dir($glob)) {
                $glob = dirname($glob) . "/*." . $this->settings->extension;
            }

            $it = new \GlobIterator(base_path() . $glob, $flags);
            $this->append($it);
            $this->count += $it->count();
        }
    }

    public function current()
    {
        $sourceFile = new SourceFile(parent::current(), $this->settings);
        return $sourceFile;
    }

    public function next() {
        parent::next();
        if ( ! $this->valid() ) return;
        $sourceFile = new SourceFile(parent::current(), $this->settings);
        if ($sourceFile->shouldSkip()) $this->next();
    }

    public function count() {
        return $this->count;
    }
}
