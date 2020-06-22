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
 * @since     1.1.0
 */
class TemplateModel extends Model
{
    // Public Properties
    // =========================================================================
    public $id;
    public $form_handle;
    public $template_id;
    public $template_role;
    public $template_role_email;
    public $template_role_name;
    public $email_subject;

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
