<?php
/**
 * Hop DocuSign plugin for Craft CMS 3.x
 *
 * Integrates DocuSign functionalities into your forms.
 *
 * @link      https://www.hopstudios.com
 * @copyright Copyright (c) 2020 Hop Studios
 */

namespace hopstudios\hopdocusign\models;

use hopstudios\hopdocusign\HopDocusign;

use Craft;
use craft\base\Model;

/**
 * @author    Hop Studios
 * @package   HopDocusign
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $is_live = false;
    public $live_docusign_user = '';
    public $live_docusign_pass = '';
    public $sandbox_docusign_user = '';
    public $sandbox_docusign_pass = '';
    public $integrator_key = '';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['is_live', 'boolean'],
            ['is_live', 'default', 'value' => false],
        ];
    }
}
