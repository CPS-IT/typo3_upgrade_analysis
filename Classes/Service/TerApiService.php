<?php
namespace CPSIT\Typo3UpgradeAnalysis\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility;

class TerApiService extends \TYPO3\CMS\Core\Service\AbstractService
{
    /**
     * @var string
     */
    protected $accessToken = '';

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function injectObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * TerApiService constructor.
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->accessToken = $this->getAccessToken();
    }

    /**
     * https://extensions.typo3.org/faq/rest-api
     *
     * @param string $extensionKey
     * @return mixed
     */
    public function getVersionsForExtensionFromTer($extensionKey)
    {
        $url = "https://extensions.typo3.org/api/v1/extension/" . $extensionKey . "/versions?access_token=" . $this->accessToken;
        $rsp = file_get_contents($url);
        $rsp_obj = json_decode($rsp);

        if (!empty($rsp_obj['status'])) {
            //todo fehlerbehandlung, bzw extensions sie nicht aus dem ter sind
            return false;
        } else {
            $versions = [];
            foreach ($rsp_obj as $obj) {
                $typo3Versions = $obj['typo3_versions'];
                foreach ($obj['typo3_versions'] as $version) {
                    // get lates existing version for TYPO3 version
                    if (empty($versions[$version]) || $versions[$version] < $obj['number']) {
                        $versions[$version] = $obj['number'];
                    }
                }

            }

            return $rsp_obj;
        }
    }

    /**
     * @return string
     */
    protected function getAccessToken()
    {
        /** @var ConfigurationUtility $configurationUtility */
        $configurationUtility = $this->objectManager->get(ConfigurationUtility::class);
        $configuration = $configurationUtility->getCurrentConfiguration('upgrade_analysis');
        return $configuration['ter_access_token']['value'];
    }

}