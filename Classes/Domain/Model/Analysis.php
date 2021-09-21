<?php

namespace CPSIT\Typo3UpgradeAnalysis\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Analysis extends AbstractEntity
{

    /**
     * @var int
     */
    protected $linesOfCode = 0;

    /**
     * @var string
     */
    protected $extKey;

    /**
     * @var int
     */
    protected $phpWarnings = 0;

    /**
     * @var int
     */
    protected $phpErrors = 0;

    /**
     * @var int
     */
    protected $extensionScanStrongBraking = 0;

    /**
     * @var int
     */
    protected $extensionScanWeakBraking = 0;

    /**
     * @var int
     */
    protected $extensionScanStrongDeprecated = 0;

    /**
     * @var int
     */
    protected $extensionScanWeakDeprecated = 0;

    /**
     * @var int
     */
    protected $category = 0;

    /**
     * @var bool
     */
    protected $compatibleVersion = false;

    /**
     * @var bool
     */
    protected $deactivated = false;

    /**
     * @return int
     */
    public function getLinesOfCode()
    {
        return $this->linesOfCode;
    }

    /**
     * @param int $linesOfCode
     */
    public function setLinesOfCode($linesOfCode)
    {
        $this->linesOfCode = $linesOfCode;
    }

    /**
     * @return string
     */
    public function getExtKey()
    {
        return $this->extKey;
    }

    /**
     * @param string $extKey
     */
    public function setExtKey($extKey)
    {
        $this->extKey = $extKey;
    }

    /**
     * @return int
     */
    public function getPhpWarnings()
    {
        return $this->phpWarnings;
    }

    /**
     * @param int $phpWarnings
     */
    public function setPhpWarnings($phpWarnings)
    {
        $this->phpWarnings = $phpWarnings;
    }

    /**
     * @return int
     */
    public function getPhpErrors()
    {
        return $this->phpErrors;
    }

    /**
     * @param int $phpErrors
     */
    public function setPhpErrors($phpErrors)
    {
        $this->phpErrors = $phpErrors;
    }

    /**
     * @return int
     */
    public function getExtensionScanStrongBraking()
    {
        return $this->extensionScanStrongBraking;
    }

    /**
     * @param int $extensionScanStrongBraking
     */
    public function setExtensionScanStrongBraking($extensionScanStrongBraking)
    {
        $this->extensionScanStrongBraking = $extensionScanStrongBraking;
    }

    /**
     * @return int
     */
    public function getExtensionScanWeakBraking()
    {
        return $this->extensionScanWeakBraking;
    }

    /**
     * @param int $extensionScanWeakBraking
     */
    public function setExtensionScanWeakBraking($extensionScanWeakBraking)
    {
        $this->extensionScanWeakBraking = $extensionScanWeakBraking;
    }

    /**
     * @return int
     */
    public function getExtensionScanStrongDeprecated()
    {
        return $this->extensionScanStrongDeprecated;
    }

    /**
     * @param int $extensionScanStrongDeprecated
     */
    public function setExtensionScanStrongDeprecated($extensionScanStrongDeprecated)
    {
        $this->extensionScanStrongDeprecated = $extensionScanStrongDeprecated;
    }

    /**
     * @return int
     */
    public function getExtensionScanWeakDeprecated()
    {
        return $this->extensionScanWeakDeprecated;
    }

    /**
     * @param int $extensionScanWeakDeprecated
     */
    public function setExtensionScanWeakDeprecated($extensionScanWeakDeprecated)
    {
        $this->extensionScanWeakDeprecated = $extensionScanWeakDeprecated;
    }

    /**
     * @return int
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param int $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return bool
     */
    public function isCompatibleVersion()
    {
        return $this->compatibleVersion;
    }

    /**
     * @param bool $compatibleVersion
     */
    public function setCompatibleVersion($compatibleVersion)
    {
        $this->compatibleVersion = $compatibleVersion;
    }

    /**
     * @return bool
     */
    public function isDeactivated()
    {
        return $this->deactivated;
    }

    /**
     * @param bool $deactivated
     */
    public function setDeactivated($deactivated)
    {
        $this->deactivated = $deactivated;
    }

}