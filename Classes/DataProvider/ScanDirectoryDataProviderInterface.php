<?php
/**
 * This file is part of the TYPO3 upgrade analysis tool project by Vladimir Falcon Piva.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 *
 */

namespace CPSIT\Typo3UpgradeAnalysis\DataProvider;

interface ScanDirectoryDataProviderInterface
{
    /**
     * @return iterable
     */
    public function getDirectoryList(): iterable;
}
