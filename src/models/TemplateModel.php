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
class TemplateModel extends Model
{
    // Public Properties
    // =========================================================================
    public $id;
    public $form_handle;
    public $email_handle;
    public $recipient_name;
    public $email_subject;
    public $template_role;
    public $template_id;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [];
    }
}
