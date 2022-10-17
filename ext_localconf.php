<?php

defined('TYPO3') || defined('TYPO3_MODE') || die();

if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::getNumericTypo3Version() < \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionsStringToVersionNumbers('12.0')) {
    // Content Element registration for TYPO3 v11 and older. For v12 see Services.yaml
    $GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects'] = array_merge($GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects'] ?? [], [
        'WEBCOMPONENT' => \Smic\Webcomponents\ContentObject\WebComponentContentObject::class,
    ]);
}
