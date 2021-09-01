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

namespace CPSIT\UpgradeAnalysis\Service;

use CPSIT\UpgradeAnalysis\Console\ScanExtensions7;
use CPSIT\UpgradeAnalysis\Utility\Utility;
use Symfony\Component\Console\Application;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExtensionScanService
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
     * @var Application
     */
    protected $app;

    /**
     * Typo3ExtensionScanService constructor.
     * @param string $reportFileName
     * @param string $reportFileFormat
     */
    public function __construct(string $reportFileName = 'typo3scan', $reportFileFormat = 'json')
    {
        $this->reportFileName = $reportFileName;
        $this->reportFileFormat = $reportFileFormat;
    }

    /**
     * Scan TYPO3 Extension
     *
     * @param string $directoryToScan Directory to be scanned
     * @param string $outputPath Directory to store the scan results
     * @param string $target TYPO3 target version
     * @return string
     * @throws \Exception
     */
    public function scanDirectory(string $directoryToScan, string $outputPath, string $target): string
    {
        $startTime = microtime(true);

        $outputPath = $outputPath . $this->reportFileName . '.';
        $scanResults = exec("php /var/www/html/app/vendor/michielroos/typo3scan/typo3scan.phar scan --target " . $target . " -r " . $outputPath . "html " . $directoryToScan . " -f html");

        if (version_compare(TYPO3_branch, '7.6', '=')) {
            //todo symfony
            //$scanResults = exec("php /var/www/html/app/vendor/michielroos/typo3scan/typo3scan.phar scan --target " . $target . " -r " . $outputPath . "json " . $directoryToScan . " -f json");
        } else {
            $scanResults = $this->doTypo3Scan($directoryToScan, $target);
            $scanResults = ['executionTime' => microtime(true) - $startTime] + $scanResults;
            $scanResults = Utility::convertObjectToJson($scanResults);
            Utility::writeFile($outputPath . 'json', $scanResults);
        }

        return $scanResults;
    }

    /**
     * Scan TYPO3 Extension directory
     *
     * @param string $directoryToScan
     * @param string $target
     * @return array
     */
    public function doTypo3Scan(string $directoryToScan, string $target): array
    {
        $scanner = new ScannerService($target);

        return $scanner->scan($directoryToScan);
    }
}
