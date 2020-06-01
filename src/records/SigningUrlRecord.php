<?php
/**
 * Hop DocuSign plugin for Craft CMS 3.x
 *
 * Integrates DocuSign functionalities into your forms.
 *
 * @link      https://www.hopstudios.com
 * @copyright Copyright (c) 2020 Hop Studios
 */

namespace hopstudios\hopdocusign\records;

use hopstudios\hopdocusign\HopDocusign;

use Craft;
use craft\db\ActiveRecord;

/**
 * @author    Hop Studios
 * @package   HopDocusign
 * @since     1.0.0
 */
class SigningUrlRecord extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%hopdocusign_signing_urls}}';
    }

    /**
     * Factory Method
     *
     * @return FormRecord
     */
    public static function create(): SigningUrlRecord
    {
        $signing_url = new self();

        return $signing_url;
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            [['token'], 'unique'],
            [['token', 'signing_url'], 'required'],
        ];
    }
}