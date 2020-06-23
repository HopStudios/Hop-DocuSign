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
 * @since     1.1.0
 */
class TemplateRecord extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%hopdocusign_templates}}';
    }

    /**
     * Factory Method
     *
     * @return FormRecord
     */
    public static function create(): TemplateRecord
    {
        $template = new self();

        return $template;
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            [['form_handle'], 'unique'],
            [['form_handle', 'template_id', 'template_role', 'template_role_email', 'template_role_name'], 'required'],
        ];
    }
}