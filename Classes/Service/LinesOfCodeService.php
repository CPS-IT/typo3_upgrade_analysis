<?php
/**
 *   This file is part of the TYPO3 upgrade analysis tool project.
 *
 *   It is free software; you can redistribute it and/or modify it under
 *   the terms of the GNU General Public License, either version 2
 *   of the License, or any later version.
 *
 *   For the full copyright and license information, please read the
 *   LICENSE.md file that was distributed with this source code.
 */

namespace CPSIT\Typo3UpgradeAnalysis\Service;

use MichielRoos\TYPO3Scan\Command\ScanCommand;
use SplFileObject;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;

class LinesOfCodeService
{
    /**
     * Report file name
     *
     * @var string
     */
    protected $reportFileName;

    /**
     * Report file format
     * @see ScanCommand
     *
     * @var string
     */
    protected $reportFileFormat;

    /**
     * Typo3ExtensionScanService constructor.
     * @param string $reportFileName
     * @param string $reportFileFormat
     */
    public function __construct(string $reportFileName = 'linesOfCode', $reportFileFormat = 'json')
    {
        $this->reportFileName = $reportFileName;
        $this->reportFileFormat = $reportFileFormat;
    }

    /**
     * Scan directory and count number of files in file mask
     *
     * @param string $directoryToScan Directory to be scanned
     * @param string $outputPath Directory to store the scan results
     * @param string $findFileMask find file mask. Default '*.php'
     * @return array
     */
    public function scanDirectory(string $directoryToScan, string $outputPath, bool $outputInFile, string $findFileMask = '*.php'): array
    {
        $linesOfCodeInFiles = [];
        $startTime = microtime(true);
        /** @var Finder finder */
        $finder = new Finder();
        $files = $finder->in($directoryToScan)->files()->name($findFileMask);

        $linesOfCodeInFiles['totals'] = 0;
        $linesOfCodeInFiles['executionTime'] = 0;

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $linesInfile = $this->countLinesInfile($file);
            $linesOfCodeInFiles['totals'] += $linesInfile['lines'];
            $linesOfCodeInFiles[] = $linesInfile;
        }

        if ($outputInFile) {
            $outputPath = $outputPath . $this->reportFileName . '.' . $this->reportFileFormat;

            $fileSystem = new Filesystem();
            $linesOfCodeInFiles['executionTime'] = microtime(true) - $startTime;
            $linesOfCodeInFilesJson = json_encode($linesOfCodeInFiles);
            $fileSystem->dumpFile($outputPath, $linesOfCodeInFilesJson);
        }

        return $linesOfCodeInFiles;
    }

    /**
     * Return lines of code in file
     * Empty lines will be ignored.
     *
     * @param SplFileInfo $file
     * @return array
     */
    public function countLinesInfile(SplFileInfo $file): array
    {
        /** @var  SplFileObject $splFile */
        $splFile = $file->openFile();
        $splFile->setFlags(SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $splFile->seek(PHP_INT_MAX);

        $linesOfCodeInFiles = [
            'lines' => $splFile->key(),
            'path' => $splFile->getRealPath(),
        ];

        unset($splFile);

        return $linesOfCodeInFiles;
    }
}
