<?php
/**
 * Hop DocuSign plugin for Craft CMS 3.x
 *
 * Integrates DocuSign functionalities into your forms.
 *
 * @link      https://www.hopstudios.com
 * @copyright Copyright (c) 2020 Hop Studios
 */

namespace hopstudios\hopdocusign\assetbundles\hopdocusign;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Hop Studios
 * @package   HopDocusign
 * @since     1.0.0
 */
class HopDocusignAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@hopstudios/hopdocusign/assetbundles/hopdocusign/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/HopDocusign.js',
        ];

        $this->css = [
            'css/HopDocusign.css',
        ];

        parent::init();
    }
}
