<?php

namespace CPSIT\UpgradeAnalysis\Domain\Model;

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


}