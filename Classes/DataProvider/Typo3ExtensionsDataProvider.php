<?php
declare(strict_types=1);

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

use Exception;

class Typo3ExtensionsDataProvider
{

    /**
     * Root path to TYPO3 System
     *
     * @var string
     */
    protected $typo3RootPath;

    /**
     * Path to TYPO3 extensions directory
     *
     * @var string
     */
    protected $extensionPath;

    /**
     * Path to TYPO3 sys extensions directory
     *
     * @var string
     */
    protected $sysExtensionPath;

    /**
     * Path to TYPO3 extensions directory
     *
     * @var string
     */
    protected $packageStatesPath;

    /**
     * Extension directory paths array
     *
     * @var iterable
     */
    protected $extensionDirectoryPaths;

    /**
     * Sys extension directory paths array
     *
     * @var iterable
     */
    protected $sysExtensionDirectoryPaths;


    /**
     * Typo3ExtensionsDataProvider constructor.
     *
     * @param string $typo3RootPath
     * @param string $extRelativePath Relative to $typo3RootPath default typo3conf/ext/
     * @param string $sysExtRelativePath Relative to $typo3RootPath default typo3/sysext/
     * @param string $packageStatesPath Relative to $typo3RootPath default typo3conf/PackageStates.php
     * @throws Exception
     */
    public function __construct(
        string $typo3RootPath,
        $extRelativePath = 'typo3conf/ext/',
        $sysExtRelativePath = 'typo3/sysext/',
        $packageStatesPath = 'typo3conf/PackageStates.php'
    ) {
        if (!empty($typo3RootPath) && is_dir($typo3RootPath)) {
            $this->typo3RootPath = self::sanitizeTrailingSeparator($typo3RootPath);
        } else {
            throw new Exception('Path to TYPO3 root does not exists or is empty', 1573636435);
        }

        $this->setExtensionPath($extRelativePath);
        $this->setSysExtensionPath($sysExtRelativePath);
        $this->setPackageStatesPath($packageStatesPath);
    }

    /**
     * Get TYPO3 extension directory paths
     *
     * @return iterable Finder
     */
    public function getAllExtensionDirectoryPaths(): iterable
    {
        $extRelativePath = [
            $this->getExtensionPath(),
            $this->getSysExtensionPath()
        ];

        $directoryDataProvider = new DirectoryDataProvider($extRelativePath);
        return $directoryDataProvider->getDirectoryList();
    }

    /**
     * Get TYPO3 extension directory paths
     *
     * @return iterable Finder
     */
    public function getExtensionDirectoryPaths(): iterable
    {
        $directoryDataProvider = new DirectoryDataProvider($this->getExtensionPath());
        return $directoryDataProvider->getDirectoryList();
    }

    /**
     * Get TYPO3 sys extension directory paths
     *
     * @return iterable Finder
     */
    public function getSysExtensionDirectoryPaths(): iterable
    {
        $directoryDataProvider = new DirectoryDataProvider($this->getSysExtensionPath());
        return $directoryDataProvider->getDirectoryList();
    }

    /**
     * @param string $extensionPath
     */
    public function setExtensionPath(string $extensionPath): void
    {
        $this->extensionPath = self::sanitizeTrailingSeparator($this->typo3RootPath . $extensionPath);
    }

    /**
     * Absolute path to TYPO3 sys extensions
     *
     * @param string $sysExtensionRelativePath
     */
    public function setSysExtensionPath(string $sysExtensionRelativePath): void
    {
        $this->sysExtensionPath = self::sanitizeTrailingSeparator($this->typo3RootPath . $sysExtensionRelativePath);
    }

    /**
     * @return string
     */
    public function getSysExtensionPath(): string
    {
        return $this->sysExtensionPath;
    }

    /**
     * @return string
     */
    public function getExtensionPath(): string
    {
        return $this->extensionPath;
    }

    /**
     * @return string
     */
    public function getPackageStatesPath(): string
    {
        return $this->packageStatesPath;
    }

    /**
     * @param string $packageStatesPath
     */
    public function setPackageStatesPath(string $packageStatesPath): void
    {
        $this->packageStatesPath = $this->typo3RootPath . $packageStatesPath;
    }

    /**
     * Sanitizes a trailing separator.
     * (e.g. 'some/path' => 'some/path')
     *
     * @param string $path The path to be sanitized
     * @param string $separator The separator to be used
     * @return string
     */
    public static function sanitizeTrailingSeparator($path, $separator = '/'): string
    {
        return rtrim(trim($path), $separator) . $separator;
    }
}
