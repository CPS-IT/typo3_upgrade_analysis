<?php


namespace CPSIT\UpgradeAnalysis\Domain\Repository;


use TYPO3\CMS\Extbase\Persistence\Repository;

class AnalysisRepository extends Repository
{
    /**
     * @param $extKey
     * @return object
     */
    public function findByExtensionKey($extKey)
    {
        $query = $this->createQuery();
        return $query->matching(
            $query->equals('ext_key', $extKey)
        )->execute()->getFirst();
    }
}