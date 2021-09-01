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

namespace CPSIT\UpgradeAnalysis\Utility;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class Utility
{
    /**
     * Convert object to Json
     *
     * @param $object
     * @return string
     */
    public static function convertObjectToJson($object): string
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        return $serializer->serialize($object, 'json');
    }

    /**
     * Write file to path. Files will be overwritten
     *
     * @param string $outputPath
     * @param string $content content to write in file
     */
    public static function writeFile($outputPath, $content): void
    {
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($outputPath, $content);
    }
}
