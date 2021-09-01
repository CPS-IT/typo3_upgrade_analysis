<?php
/**
 * This file is part of the TYPO3 upgrade analysis tool project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 *
 */

namespace CPSIT\UpgradeAnalysis\DataProvider;

use Symfony\Component\Finder\Finder;

class DirectoryDataProvider implements ScanDirectoryDataProviderInterface
{
    /**
     * Directory depth
     *
     * @see Finder::depth()
     * @var string
     */
    protected $depth = '< 1';

    /**
     * @var Finder
     */
    protected $finder;


    /**
     * Absolute path to be scanned
     *
     * @var string
     */
    protected $pathToScan;

    /**
     * DirectoryDataProvider constructor.
     * @param string|string[] $pathToScan Absolute path to the directories to be scanned
     * @param Finder|null $finder
     */
    public function __construct($pathToScan, Finder $finder = null)
    {
        $this->finder = $finder ? $finder : new Finder();

        $this->pathToScan = $pathToScan;
    }

    /**
     * Get directory and subdirectories from path
     *
     * @return iterable Finder
     */
    public function getDirectoryList(): iterable
    {
        $directoryList = [];
        try {
            $directoryList = $this->finder->in($this->pathToScan)->depth($this->depth)->directories();
        } catch (\Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }

        return $directoryList;
    }

    /**
     * @param string $pathToScan
     */
    public function setPathToScan(string $pathToScan): void
    {
        $this->pathToScan = $pathToScan;
    }

    /**
     * Directory depth to scan
     *
     * @return string
     */
    public function getDepth(): string
    {
        return $this->depth;
    }

    /**
     * See
     *
     * @param string $depth
     * @see Finder->depth
     */
    public function setDepth(string $depth): void
    {
        $this->depth = $depth;
    }
}
