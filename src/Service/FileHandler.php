<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\Directory\SystemDirectory;
use App\Exception\Filesystem\PathNotReadableException;
use App\Exception\Filesystem\PathNotWritableException;
use App\Model\File\SourceFile;
use App\Model\File\TargetFile;
use Exception;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\StorageAttributes;

class FileHandler
{
    public function __construct(
        private readonly Filesystem $filesystem,
    ) {
    }

    /**
     * Checks if the directories exist and their permissions
     *
     * @param string $directory
     *
     * @return bool
     * @throws Exception
     * @throws FilesystemException
     */
    public function isDirectoryReadable(string $directory): bool
    {
        if (!$this->filesystem->directoryExists($directory) && !is_readable($directory)) {
            throw new PathNotReadableException($directory, code: 1679989741039);
        }

        return true;
    }

    /**
     * Checks if the directories exist and their permissions
     *
     * @param string $directory
     *
     * @return bool
     * @throws Exception
     */
    public function isDirectoryWriteable(string $directory): bool
    {
        if (!is_dir($directory) && !is_writable($directory)) {
            throw new PathNotWritableException($directory, code: 1679989793176);
        }

        return true;
    }

    /**
     * Returns all files of the source directory
     *
     * @return DirectoryListing
     * @throws FilesystemException
     */
    public function getPdfFilesFromSource(): DirectoryListing
    {
        return $this->filesystem->listContents(SystemDirectory::SOURCE->path(), true)
                                ->filter(
                                    fn (StorageAttributes $i) => $i->isFile() && basename($i->path()) !== '.gitkeep'
                                )
                                ->filter(
                                    fn (StorageAttributes $i) => (string) pathinfo($i->path(), PATHINFO_EXTENSION)
                                                                === 'pdf'
                                )
                                ->filter(
                                    fn (StorageAttributes $i) => $this->filesystem->mimeType($i->path())
                                                                === 'application/pdf'
                                )
        ;
    }

    /**
     * Copies the file to the destination directory and renames it
     *
     * @param SourceFile $sourceFile
     * @param TargetFile $targetFile
     *
     * @return bool
     * @throws FilesystemException
     */
    public function copySourceFileToTarget(SourceFile $sourceFile, TargetFile $targetFile): bool
    {
        if (!$this->filesystem->directoryExists(SystemDirectory::TARGET->dirname())) {
            $this->filesystem->createDirectory(SystemDirectory::TARGET->dirname());
        }

        $targetFilename = $targetFile->filename;

        if (
            $this->filesystem->fileExists(
                SystemDirectory::TARGET->dirname() . "/" . $targetFile->path . "/" . $targetFilename . ".pdf"
            )
        ) {
            $i = 1;

            $newFilename = $targetFilename;
            while ($this->filesystem->fileExists(
                SystemDirectory::TARGET->dirname() . "/" . $targetFile->path . "/" . $newFilename . ".pdf"
            )) {
                $newFilename = $targetFilename . "-" . $i++;
            }

            $targetFilename = $newFilename;
        }

        $this->filesystem->copy(
            $sourceFile->path,
            SystemDirectory::TARGET->dirname() . "/" . $targetFile->path . "/" . $targetFilename . ".pdf"
        );

        return true;
    }

    /**
     * Cleans the target directory
     *
     * @return void
     * @throws FilesystemException
     */
    public function clearTargetDirectory(): void
    {
        $directories = $this->filesystem->listContents(SystemDirectory::TARGET->path())
                                        ->filter(
                                            fn (StorageAttributes $i) => $i->isDir()
                                        )
        ;

        /** @var DirectoryAttributes $directory */
        foreach ($directories as $directory) {
            $this->filesystem->deleteDirectory($directory->path());
        }

        $files = $this->filesystem->listContents(SystemDirectory::TARGET->path())
                                  ->filter(
                                      fn (StorageAttributes $i) => $i->isFile() && basename($i->path()) !== '.gitkeep'
                                  )
        ;

        /** @var FileAttributes $file */
        foreach ($files as $file) {
            $this->filesystem->delete($file->path());
        }
    }

    public function buildSourceFile(string $path): SourceFile
    {
        return new SourceFile(
            $path,
            "NAME"
        );
    }
}
