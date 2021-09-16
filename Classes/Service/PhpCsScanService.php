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

use CPSIT\Typo3UpgradeAnalysis\Utility\Utility;
use MichielRoos\TYPO3Scan\Command\ScanCommand;
use Symfony\Component\Process\PhpExecutableFinder;

class PhpCsScanService
{
    /**
     * path to phpcs in vendor directory
     */
    const PHPCS_PATH = '/var/www/html/app/vendor/bin/phpcs';

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
     * PHP Executable path
     * @var string
     */
    protected $phpBinaryPath;

    /**
     * Typo3ExtensionScanService constructor.
     * @param string $reportFileName
     * @param string $reportFileFormat
     */
    public function __construct(string $reportFileName = 'phpScanner', $reportFileFormat = 'json')
    {
        $this->reportFileName = $reportFileName;
        $this->reportFileFormat = $reportFileFormat;

        // PHP binary path
        $phpBinaryFinder = new PhpExecutableFinder();
        $this->phpBinaryPath = $phpBinaryFinder->find();
    }

    /**
     * Execute PhpCs scan
     *
     * @param string $directoryToScan Directory to be scanned
     * @param string $outputPath Directory to store the scan results
     * @param string $phpVersionToTest
     * @return string
     */
    public function scanDirectory(string $directoryToScan, string $outputPath, string $phpVersionToTest = '7.2'): string
    {
        $outputPath = $outputPath . $this->reportFileName . '.' . $this->reportFileFormat;

        //todo zu spezifisch
        $phpCsPath = self::PHPCS_PATH;

        $scanResults = exec("{$this->phpBinaryPath} {$phpCsPath} \"{$directoryToScan}\" --standard=PHPCompatibility --runtime-set testVersion {$phpVersionToTest} --report=json");

        //Utility::writeFile($outputPath, $scanResults);

        return $scanResults;
    }
}
