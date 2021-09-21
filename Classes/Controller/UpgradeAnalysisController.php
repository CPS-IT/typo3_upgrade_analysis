<?php
namespace CPSIT\Typo3UpgradeAnalysis\Controller;

/**
 *
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */

use CPSIT\Typo3UpgradeAnalysis\DataProvider\Typo3ExtensionsDataProvider;
use CPSIT\Typo3UpgradeAnalysis\Domain\Model\Analysis;
use CPSIT\Typo3UpgradeAnalysis\Domain\Repository\AnalysisRepository;
use CPSIT\Typo3UpgradeAnalysis\Service\ExtensionScanService;
use CPSIT\Typo3UpgradeAnalysis\Service\LinesOfCodeService;
use CPSIT\Typo3UpgradeAnalysis\Service\PhpCsScanService;
use NITSAN\NsExtCompatibility\Controller\nsextcompatibilityController;
use CPSIT\Typo3UpgradeAnalysis\Service\TerApiService;
use NITSAN\NsExtCompatibility\Utility\Extension;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use TYPO3\CMS\Backend\Toolbar\Enumeration\InformationStatus;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extensionmanager\Utility\ListUtility;

/**
 * Backend Controller
 */
class UpgradeAnalysisController extends nsextcompatibilityController
{
    /**
     * @var TerApiService
     */
    protected $terApiService;

    /**
     * @var PhpCsScanService
     */
    protected $phpScanService;

    /**
     * @var ExtensionScanService
     */
    protected $typo3ExtensionScanService;

    /**
     * @var LinesOfCodeService
     */
    protected $linesOfCodeService;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var AnalysisRepository
     */
    protected $analysisRepository;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManger;

    /**
     *
     */
    public function listAction()
    {
        parent::listAction();

        $arguments = $this->request->getArguments();

        $this->terApiService = GeneralUtility::makeInstance(TerApiService::class);
        $this->phpScanService = GeneralUtility::makeInstance(PhpCsScanService::class);
        $this->typo3ExtensionScanService = GeneralUtility::makeInstance(ExtensionScanService::class, 'typo3scan', 'html');
        $this->linesOfCodeService = GeneralUtility::makeInstance(LinesOfCodeService::class);
        $this->fileSystem = GeneralUtility::makeInstance(Filesystem::class);
        $this->analysisRepository = $this->objectManager->get(AnalysisRepository::class);
        $this->persistenceManger = $this->objectManager->get(PersistenceManager::class);


        $targetVersion = $this->request->getArguments()['targetVersion'];
        if ($targetVersion != null) {
            //$targetVersion has format <major version>.x
            preg_match('/\d*/', $targetVersion, $m);
            $this->scanExtensions($m[0]);

            $this->doEffortCalculation();
        }

        //system information
        $systemInfo = [];
        $systemInfo['userStatistic'] = $this->getUserStatistic();
        $systemInfo['beGroups'] = $this->getBeGroups();
        $systemInfo['cTypes'] = $this->getCTypes();
        $systemInfo['menuTypes'] = $this->getMenuTypes();
        $systemInfo['listTypes'] = $this->getListTypes();
        $systemInfo['serverInfo'] = $this->getServerInfos();
        $systemInfo['domains'] = $this->getDomains();
        $this->view->assign('systemInfo', $systemInfo);
    }

    public function detailAction()
    {
        parent::detailAction();
        $extKey = $this->request->getArguments()['extKey'];

        $allExtensions = $this->objectManager->get(ListUtility::class)->getAvailableAndInstalledExtensionsWithAdditionalInformation();
        foreach ($allExtensions as $extensionKey => $ext) {
            //Filter all local extension
            if (strtolower($ext['type']) == 'local') {
                $storage = ResourceFactory::getInstance()->getDefaultStorage();
                $fileObject = $storage->getFile( 'scan/' . $extensionKey . '/typo3scan.html');
                if ($fileObject !== null) {
                    $uid = $fileObject->getUid();
                    $url = $fileObject->getPublicUrl();
                }
            }
        }

        /** @var Analysis $analysis */
        $analysis = $this->analysisRepository->getAnalysisByExtensionKey($extKey);

        $extensionInfo = [];
        $extensionInfo['linesOfCode'] = $analysis->getLinesOfCode();
        $this->view->assign('additionalExtensionInformation', $extensionInfo);
    }

    /**
     * @param $targetVersion
     * @param $newScanRequested
     */
    protected function scanExtensions($targetVersion, $newScanRequested = false){
        //todo nochmal parameter angucken
        $typo3Extensions = new Typo3ExtensionsDataProvider('/var/www/html/app/web');
        //$reportDirectoryBasePath = Typo3ExtensionsDataProvider::sanitizeTrailingSeparator('scaniii');
        $reportDirectoryBasePath = Typo3ExtensionsDataProvider::sanitizeTrailingSeparator('/var/www/html/app/web/fileadmin/scan');

        /** @var SplFileInfo $directory */
        foreach ($typo3Extensions->getAllExtensionDirectoryPaths() as $directory) {
            //do not scan extensions from core
            preg_match('/typo3conf/', $directory->getPathname(), $m);
            if (empty($m)) {
                continue;
            }

            $version = $this->checkExtensionVersion($extKey, $targetVersion);
            //todo set analysis['compatibleVersion'] = 1 if available

            //scan the extensions only if they have not been scanned yet or if it is explicitly requested
            $extKey = $directory->getFilename();
            $analysis = $this->analysisRepository->getAnalysisByExtensionKey($extKey);
            if ($analysis === null || $newScanRequested) {
                $pathToReportDirectory = $this->createReportDirectoryPathForExtension($directory, $reportDirectoryBasePath);
                $this->processDirectory($directory, $extKey, $pathToReportDirectory, $targetVersion);
                $this->getUpgradeCategoryForExtension($extKey);
            }
        }
    }

    /**
     * @param SplFileInfo $directory
     * @param string $reportDirectoryBasePath
     * @return string
     */
    public function createReportDirectoryPathForExtension($directory, $reportDirectoryBasePath)
    {
        $pathToReportDirectory = Typo3ExtensionsDataProvider::sanitizeTrailingSeparator(
            $reportDirectoryBasePath . $directory->getFilename()
        );
        // Create report directory if
        if (!$this->fileSystem->exists($pathToReportDirectory)) {
            $this->fileSystem->mkdir($pathToReportDirectory);
        }

        return $pathToReportDirectory;
    }

    /**
     * @param SplFileInfo $directory
     * @param $extKey
     * @param $pathToReportDirectory
     * @param $targetVersion
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function processDirectory(SplFileInfo $directory, $extKey, $pathToReportDirectory, $targetVersion)
    {
        // Execute PhpSc
        $phpScResults = $this->phpScanService->scanDirectory(
            $directory->getRealPath(),
            $pathToReportDirectory,
            $targetVersion
        );

        // Execute typo3 scan
        $typo3ScanResults = $this->typo3ExtensionScanService->scanDirectory(
            $directory->getRealPath(),
            $pathToReportDirectory,
            $targetVersion
        );

        // Count lines of php
        $linesOfCodeResults = $this->linesOfCodeService->scanDirectory(
            $directory->getRealPath(),
            $pathToReportDirectory,
            false
        );

        /** @var Analysis $analysis */
        $analysis = $this->analysisRepository->getAnalysisByExtensionKey($extKey);
        if ($analysis === null) {
            $analysis = GeneralUtility::makeInstance(Analysis::class);
            $analysis->setExtKey($extKey);
            $analysis->setLinesOfCode($linesOfCodeResults['totals']);
            $this->analysisRepository->add($analysis);
        } else {
            $analysis->setLinesOfCode($linesOfCodeResults['totals']);
            $this->analysisRepository->update($analysis);
        }
        $this->persistenceManger->persistAll();
    }

    /**
     * check if there is a compatible extension version for target version
     *
     * @param $extensionKey
     * @param $targetVersion
     */
    protected function checkExtensionVersion($extensionKey, $targetVersion)
    {
        $versions = $this->terApiService->getVersionsForExtensionFromTer($extensionKey);

    }

    /**
     * @param $extKey
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    protected function getUpgradeCategoryForExtension($extKey)
    {
        /** @var Analysis $analysis */
        $analysis = $this->analysisRepository->getAnalysisByExtensionKey($extKey);

        $analysis->setExtensionScanStrongBraking(11);
        $analysis->setExtensionScanStrongDeprecated(2);
        $analysis->setExtensionScanWeakDeprecated(3);
        $analysis->setPhpWarnings(1);

        //Extensions with category 0 (system extensions) are loaded directly from the database
        //for the entire calculation
        if ($analysis->isCompatibleVersion() == 1 || $analysis->isDeactivated()) {
            $category = 1;
        } elseif ($analysis->getPhpErrors() == 0 && $analysis->getExtensionScanStrongBraking() == 0
            && $analysis->getExtensionScanStrongDeprecated() == 0
            && $analysis->getExtensionScanWeakBraking() == 0) {
            $category = 2;
        } elseif ($analysis->getPhpErrors() == 0 && $analysis->getExtensionScanStrongBraking() == 0
            && $analysis->getExtensionScanStrongDeprecated() == 0) {
            $category = 3;
        } elseif ($analysis->getPhpErrors() == 0 && $analysis->getExtensionScanStrongBraking() == 0) {
            $category = 4;
        } else {
            $category = 5;
        }

        $analysis->setCategory($category);
        $this->analysisRepository->update($analysis);
        $this->persistenceManger->persistAll();

    }

    /**
     * @return float|int|mixed
     */
    protected function doEffortCalculation()
    {
        // $effort = ['category' => 'estimated time per extension (hours)']
        $effort = [
            0 => 0.25,
            1 => 2,
            2 => 4,
            3 => 8,
            4 => 20,
            5 => 64,
        ];

        $extensions = $this->analysisRepository->findAll();
        $estimatedTime = 0;
        foreach ($extensions as $extension) {
            $category = $extension->getCategory();
            if ($category == 0) {
                $estimatedTime += $effort[$category];
            } else {
                $estimatedTime += $effort[$category] * 2;
            }

        }

        return $estimatedTime * 1.4;
    }

    /**
     * @return array[]
     */
    protected function getUserStatistic()
    {
        $beUsers = $this->getBeUsers();
        $admins = $this->getAdmins();
        $userStatistic = [
            'beUsers' => [
                'all' => $beUsers[0]['count(uid)'],
                'active' => $beUsers[1]['count(uid)'],
                'inactive' => $beUsers[2]['count(uid)'] + $beUsers[3]['count(uid)'] + $beUsers[4]['count(uid)'],
            ],
            'admins' => [
                'all' => $admins[0]['count(uid)'],
                'active' => $admins[1]['count(uid)'],
                'inactive' => $admins[2]['count(uid)'] + $admins[3]['count(uid)'] + $admins[4]['count(uid)'],
            ]
        ];

        return $userStatistic;
    }

    /**
     * @return array
     */
    protected function getBeUsers()
    {
        $sql = "SELECT 'Benutzer (Gesamt)', count(uid), disable, deleted FROM be_users
UNION
SELECT '-davon aktiv', count(uid), disable, deleted FROM be_users WHERE disable = 0 AND deleted = 0
UNION
SELECT '-davon inaktiv', count(uid), disable, deleted FROM be_users WHERE disable = 1 AND deleted = 0
UNION
SELECT '-davon entfernt', count(uid), disable, deleted FROM be_users WHERE disable = 0 AND deleted = 1
UNION
SELECT '-davon inaktiv & entfernt', count(uid), disable, deleted FROM be_users WHERE disable = 1 AND deleted = 1
;";
        return $this->doSqlRequest($sql);
    }

    /**
     * @return array
     */
    protected function getAdmins()
    {
        $sql = "SELECT 'Benutzer (Admin)', count(uid), disable, admin, deleted FROM be_users WHERE admin = 1
UNION
SELECT '-davon aktiv', count(uid), disable, admin, deleted FROM be_users WHERE disable = 0 AND deleted = 0 AND admin = 1
UNION
SELECT '-davon inaktiv', count(uid), disable, admin, deleted FROM be_users WHERE disable = 1 AND deleted = 0 AND admin = 1
UNION
SELECT '-davon entfernt', count(uid), disable, admin, deleted FROM be_users WHERE disable = 0 AND deleted = 1 AND admin = 1
UNION
SELECT '-davon inaktiv & entfernt', count(uid), disable, admin, deleted FROM be_users WHERE disable = 1 AND deleted = 1 AND admin = 1
;
			";

        return $this->doSqlRequest($sql);
    }

    /**
     * @return array
     */
    protected function getBeGroups()
    {
        $sql = "SELECT 'Gruppen', count(uid), hidden, deleted FROM be_groups
UNION
SELECT '-davon aktiv', count(uid), hidden, deleted FROM be_groups WHERE deleted = 0 AND hidden = 0
UNION
SELECT '-davon versteckt', count(uid), hidden, deleted FROM be_groups WHERE deleted = 0 AND hidden = 1
UNION
SELECT '-davon entfernt', count(uid), hidden, deleted FROM be_groups WHERE deleted = 1 AND hidden = 0
UNION
SELECT '-davon versteckt & entfernt', count(uid), hidden, deleted FROM be_groups WHERE deleted = 1 AND hidden = 1
;
			";

        $beGroups = $this->doSqlRequest($sql);
        return [
            'all' => $beGroups[0]['count(uid)'],
            'active' => $beGroups[1]['count(uid)'],
            'inactive' => $beGroups[2]['count(uid)'] + $beGroups[3]['count(uid)'] + $beGroups[4]['count(uid)'],
        ];
    }

    /**
     * @return array
     */
    protected function getCTypes()
    {
        $sql = "SELECT ttc.CType, count(ttc.CType) as 'active', IFNULL(inactive, 0) as inactive, IFNULL(removed, 0) as removed, IFNULL(expired, 0) as expired FROM tt_content ttc
LEFT JOIN (
SELECT CType, count(CType) as 'inactive' FROM tt_content WHERE hidden = 1 GROUP BY CType
) as ttch ON ttc.CType = ttch.CType
LEFT JOIN (
SELECT CType, count(CType) as 'removed' FROM tt_content WHERE deleted = 1 GROUP BY CType
) as ttcd ON ttc.CType = ttcd.CType
LEFT JOIN (
SELECT CType, count(CType) as 'expired' FROM tt_content WHERE endtime < UNIX_TIMESTAMP() AND endtime != 0 GROUP BY CType
) as ttce ON ttc.CType = ttce.CType
 
GROUP BY ttc.CType ORDER BY count(*) DESC;
			";

        return $this->doSqlRequest($sql);
    }

    /**
     * @return array
     */
    protected function getMenuTypes()
    {
        $sql = "SELECT ttc.menu_type, count(ttc.menu_type) as 'active', IFNULL(inactive, 0) as inactive, IFNULL(removed, 0) as removed, IFNULL(expired, 0) as expired FROM tt_content ttc
LEFT JOIN (
SELECT menu_type, count(menu_type) as 'inactive' FROM tt_content WHERE hidden = 1 GROUP BY menu_type
) as ttch ON ttc.menu_type = ttch.menu_type
LEFT JOIN (
SELECT menu_type, count(menu_type) as 'removed' FROM tt_content WHERE deleted = 1 GROUP BY menu_type
) as ttcd ON ttc.menu_type = ttcd.menu_type
LEFT JOIN (
SELECT menu_type, count(menu_type) as 'expired' FROM tt_content WHERE endtime < UNIX_TIMESTAMP() AND endtime != 0 GROUP BY menu_type
) as ttce ON ttc.menu_type = ttce.menu_type
 
GROUP BY ttc.menu_type ORDER BY count(*) DESC;
			";

        return $this->doSqlRequest($sql);
    }

    /**
     * @return array
     */
    protected function getListTypes()
    {
        $sql = "SELECT ttc.list_type, count(ttc.list_type) as 'active', IFNULL(inactive, 0) as inactive, IFNULL(removed, 0) as removed, IFNULL(expired, 0) as expired FROM tt_content ttc
LEFT JOIN (
SELECT list_type, count(list_type) as 'inactive' FROM tt_content WHERE hidden = 1 GROUP BY list_type
) as ttch ON ttc.list_type = ttch.list_type
LEFT JOIN (
SELECT list_type, count(list_type) as 'removed' FROM tt_content WHERE deleted = 1 GROUP BY list_type
) as ttcd ON ttc.list_type = ttcd.list_type
LEFT JOIN (
SELECT list_type, count(list_type) as 'expired' FROM tt_content WHERE endtime < UNIX_TIMESTAMP() AND endtime != 0 GROUP BY list_type
) as ttce ON ttc.list_type = ttce.list_type
 
GROUP BY ttc.list_type ORDER BY count(*) DESC;
			";

        return $this->doSqlRequest($sql);
    }

    /**
     * @param string $sql
     * @return array
     */
    protected function doSqlRequest($sql)
    {
        if (version_compare(TYPO3_branch, '7.6', '=')) {
            $res = $GLOBALS['TYPO3_DB']->sql_query($sql);

            $result = [];
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                $result[] = $row;
            }
        } else {
            $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
            $connection = $connectionPool->getConnectionForTable('be_users');
            $stmt = $connection->query($sql);
            $stmt->execute();

            $result = [];
            while ($row = $stmt->fetch()) {
                $result[] = $row;
            }
        }

        return $result;
    }

    protected function getServerInfos()
    {
        $imageRendering = $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_version_5'];
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'];
        $phpVersion = substr(phpversion(), 0, 6);
        preg_match('/[0-9]+\.[0-9]+\.[0-9]+-(\w*)-/', $this->getServerVersion(), $db);
        $database = $db[1];
        preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', shell_exec('mysql -V'), $m);
        $sqlVersion = $m[0];
        $applicationContext = GeneralUtility::getApplicationContext()->isProduction() ? 'ok' : InformationStatus::STATUS_WARNING;
        preg_match('/\w* [0-9]+\.[0-9]+\.[0-9]+/', php_uname('s') . ' ' . php_uname('r'), $n);
        $operatingSystem = $n[0];
        $utf8 = '';

        return [
            'imageRendering' => $imageRendering,
            'serverSoftware' => $serverSoftware,
            'phpVersion' => $phpVersion,
            'database' => $database,
            'sqlVersion' => $sqlVersion,
            'applicationContext' => $applicationContext,
            'operatingSystem' => $operatingSystem,
            'utf8' => $utf8,
        ];
    }

    protected function getServerVersion()
    {
        if (version_compare(TYPO3_branch, '7.6', '=')) {
            return $GLOBALS['TYPO3_DB']->getServerVersion();
        } else {
            //todo
            return true;
        }
    }

    protected function getDomains()
    {
        if (version_compare(TYPO3_branch, '7.6', '=')) {
            $stmt = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                'domainName,redirectTo,hidden',
                'sys_domain',
                ''
            );

            $domain = [];
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($stmt)) {
                $domain[] = $row;
            }
        } else {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_domain');
            $queryBuilder->getRestrictions()->removeAll();
            $stmt = $queryBuilder
                ->select('domainName,redirectTo,hidden')
                ->from('sys_domain')
                ->execute();

            $domain = [];
            while ($row = $stmt->fetch()) {
                $domain[] = $row;
            }
        }
        return $domain;
    }

}
