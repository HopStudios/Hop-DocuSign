<?php
/**
 * Hop DocuSign plugin for Craft CMS 3.x
 *
 * Integrates DocuSign functionalities into your forms.
 *
 * @link      https://www.hopstudios.com
 * @copyright Copyright (c) 2020 Hop Studios
 */

namespace hopstudios\hopdocusign;

use hopstudios\hopdocusign\controllers\TemplatesController as TemplatesController;
use hopstudios\hopdocusign\services\TemplatesService as TemplatesService;
use hopstudios\hopdocusign\services\SigningUrlsService as SigningUrlsService;
use hopstudios\hopdocusign\models\SigningUrlModel as SigningUrlModel;
use hopstudios\hopdocusign\models\Settings;

use Solspace\Freeform\Freeform as Freeform;
use Solspace\Freeform\Library\Composer\Components\Form as Form;
use Solspace\Freeform\Services\SubmissionsService as SubmissionsService;
use Solspace\Freeform\Events\Submissions\SubmitEvent as SubmitEvent;
use Solspace\Freeform\Services\FormsService as FormsService;
use Solspace\Freeform\Events\Forms\ReturnUrlEvent as ReturnUrlEvent;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 * Class HopDocusign
 *
 * @author    Hop Studios
 * @package   HopDocusign
 * @since     1.0.0
 *
 * @property  HopDocusignServiceService $hopDocusignService
 */
class HopDocusign extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var HopDocusign
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * @var bool
     */
    public $hasCpSection = true;

    // Public Methods
    // =========================================================================
    
    /**
     * @return Plugin|Hop DocuSign
     */
    public static function getInstance(): HopDocuSign
    {
        return parent::getInstance();
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->initControllers();
        $this->initRoutes();

        self::$plugin = $this;

        Event::on(
            FormsService::class,
            FormsService::EVENT_AFTER_GENERATE_RETURN_URL,
            function (ReturnUrlEvent $event) {
                $submission = $event->getSubmission();
                // Grab the return signing request url from the db and change the return url
                $token = $submission->token;

                // Get the signing url
                $signingUrlsService = new SigningUrlsService();

                // Remove signing urls older than 1 day
                $signingUrlsService->deleteOldSigningUrls();

                $signing_url = $signingUrlsService->getSigningUrlByToken($token)->signing_url;

                if (!empty($signing_url)) {
                    $event->setReturnUrl($signing_url);
                }

                return true;
            }
        );

        // Submissions
        Event::on(
            SubmissionsService::class,
            SubmissionsService::EVENT_AFTER_SUBMIT,
            function (SubmitEvent $event) {
                // Setting the docusign configurations
                if ($this->settings->is_live) {
                    $docusign_user = $this->settings->live_docusign_user;
                    $docusign_pass = $this->settings->live_docusign_pass;
                    $docusign_host = 'https://www.docusign.net/restapi';
                } else {
                    $docusign_user = $this->settings->sandbox_docusign_user;
                    $docusign_pass = $this->settings->sandbox_docusign_pass;
                    $docusign_host = 'https://demo.docusign.net/restapi';
                }

                $docusign_integrator_key = $this->settings->integrator_key;

                // Get the form instance
                $form = $event->getForm(); // src/Library/Composer/Components/Form.php
                $form_handle = $form->getHandle();
                $token = $event->getElement()->token; // We will use this to identify the signing url

                // Do we have a record?
                $templatesService = new TemplatesService;
                $template         = $templatesService->getTemplateByHandle($form_handle);
    
                if (!$template) { // No record in Hop DocuSign
                    return null;
                }

                // Get the return url. This logic is from freeform ApiController.php
                $postedReturnUrl = \Craft::$app->request->post(Form::RETURN_URI_KEY);
                if ($postedReturnUrl) {
                    $returnUrl = \Craft::$app->security->validateData($postedReturnUrl);
                    if ($returnUrl === false) {
                        $returnUrl = $form->getReturnUrl();
                    }
                } else {
                    $returnUrl = $form->getReturnUrl();
                }

                // We're sending this to docusign so that they know where to send the user back to after signing
                $return_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . $returnUrl;

                $fields = $form->getLayout()->getFields();

                // // Iterate over all posted fields and get their values
                // foreach ($fields as $field) {

                //     // Bypass fields such as HTML or Submit, etc.
                //     if ($field instanceof NoStorageInterface) {
                //         continue;
                //     }

                //     // $field->getValue();
                //     echo '<pre>';
                //     print_r($field->getType());
                //     echo '</pre>';
                //     echo '<pre>';
                //     print_r($field->getHandle());
                //     echo '</pre>';
                //     echo '<pre>';
                //     var_dump($field->getValue());
                //     echo '</pre>';
                // }

                require_once('Library/docusign/esign-client/autoload.php');

                // Let the magic begin...
                // Initial configuration for the api
                $config = new \DocuSign\eSign\Configuration();
                $config->setHost($docusign_host);
                $config->addDefaultHeader("X-DocuSign-Authentication", "{\"Username\":\"" . $docusign_user . "\",\"Password\":\"" . $docusign_pass . "\",\"IntegratorKey\":\"" . $docusign_integrator_key . "\"}");

                $apiClient = new \DocuSign\eSign\ApiClient($config);
                $accountId = null;

                // Generating a unique customer key
                // Since we are not sending the user the DocuSign email, we need to provide a unique custmer key for DocuSign as an identifier to validate the signing
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $charactersLength = strlen($characters);
                $clientUserId = date('Ymd_');
                for ($i = 0; $i < 6; $i++) {
                    $clientUserId .= $characters[rand(0, $charactersLength - 1)];
                }
                $clientUserId .= date('_His');

                try {
                    // Login api using the configurations we set above to get the Account ID and baseURL
                    $authenticationApi = new \DocuSign\eSign\Api\AuthenticationApi($apiClient);
                    $options = new \DocuSign\eSign\Api\AuthenticationApi\LoginOptions();
                    $loginInformation = $authenticationApi->login($options);

                    // If logged in
                    if (!empty($loginInformation)) {

                        $loginAccount = $loginInformation->getLoginAccounts()[0];

                        // Use the DocuSign handler url
                        $host = $loginAccount->getBaseUrl();
                        $host = explode("/v2",$host);
                        $host = $host[0];

                        // Update configuration object
                        $config->setHost($host);

                        // Instantiate a new DocuSign api client with the new configurations
                        $apiClient = new \DocuSign\eSign\ApiClient($config);

                        if (isset($loginInformation)) {

                            // Get the account id for creating the envelop later
                            $accountId = $loginAccount->getAccountId();

                            if (!empty($accountId)) {

                                // Signature Request from a Template
                                // create envelope call is available in the EnvelopesApi
                                $envelopeApi = new \DocuSign\eSign\Api\EnvelopesApi($apiClient);

                                // Assign recipient to template role by setting name, email, and role name
                                $templateRole = new \DocuSign\eSign\Model\TemplateRole();

                                // Template role information must match the placeholder role name saved in your account template
                                $templateRole->setRoleName($template->template_role);
                                // The clientUserId is the only unique parameter
                                // We use this to verify the signer later 
                                $templateRole->setClientUserId($clientUserId);

                                // You need to specify which types of fields you are sending
                                // Yes it is annoying but different types of fields are handled differently
                                $data = array(
                                    'textTabs' => array(),
                                    'emailTabs' => array(),
                                    'checkboxTabs' => array(),
                                    'radioGroupTabs' => array()
                                );

                                // We're going to loop through the fields now. Some of them may be used to build the recipient name
                                $template_role_name = $template->recipient_name;

                                // Assigning fields into their 'tab', we're only using 4 types of field here so 4 tabs
                                foreach ($fields as $field) {
                                    // Bypass fields such as HTML or Submit, etc.
                                    if ($field instanceof NoStorageInterface) {
                                        continue;
                                    }

                                    $value = $field->getValue();
                                    $key = $field->getHandle();

                                    // Determine which tab to put into
                                    switch ($field->getType()) {
                                        case 'email':
                                            $emailTab = new \DocuSign\eSign\Model\Email();
                                            $emailTab->setTabLabel($key);
                                            $value = !empty($value[0]) ? $value[0] : trim(implode(' ', $value));
                                            $emailTab->setValue($value);
                                            $data['emailTabs'][] = $emailTab;
                                            break;
                                        case 'text':
                                            $textTab = new \DocuSign\eSign\Model\Text();
                                            $textTab->setTabLabel($key);
                                            $textTab->setValue($value);
                                            $data['textTabs'][] = $textTab;

                                            // Replace if key is used to build the recipient name
                                            $template_role_name = str_replace('{' . $key . '}', $value, $template_role_name);
                                            break;
                                        case 'checkbox':
                                            $checkboxTab = new \DocuSign\eSign\Model\Checkbox();
                                            $checkboxTab->setTabLabel($key);
                                            if ($value) {
                                                $checkboxTab->setSelected(true);
                                            }
                                            $data['checkboxTabs'][] = $checkboxTab;
                                            break;
                                        case 'radio_group':
                                            $radioGroupTab = new \DocuSign\eSign\Model\RadioGroup();
                                            $radioGroupTab->setGroupName($key);
                                            $radio = new \DocuSign\eSign\Model\Radio();
                                            $radio->setValue($value);
                                            $radio->setSelected(true);
                                            $radioGroupTab->setRadios(array($radio));
                                            $data['radioGroupTabs'][] = $radioGroupTab;
                                            break;
                                    }
                                }

                                // Create a Tab Model with the fields with values
                                $tabs = new \DocuSign\eSign\Model\Tabs();
                                $tabs->setCheckboxTabs($data['checkboxTabs']);
                                $tabs->setRadioGroupTabs($data['radioGroupTabs']);
                                $tabs->setEmailTabs($data['emailTabs']);
                                $tabs->setTextTabs($data['textTabs']);

                                // Setting the email and name for the template role
                                $template_role_email = implode(',', $form->get($template->email_handle)->getValue());

                                $templateRole->setEmail($template_role_email);
                                $templateRole->setName($template_role_name);

                                // Attach the tabs to the template role - Paitent
                                $templateRole->setTabs($tabs);

                                // Instantiate a new envelope object and configure settings
                                $envelop_definition = new \DocuSign\eSign\Model\EnvelopeDefinition();
                                $envelop_definition->setEmailSubject($template->email_subject . $clientUserId);

                                // Set which template to create and which role is signing
                                $envelop_definition->setTemplateId($template->template_id);
                                $envelop_definition->setTemplateRoles(array($templateRole));
                                
                                // Set envelope status to "sent" to immediately send the signature request
                                $envelop_definition->setStatus("sent");

                                // Optional envelope parameters
                                $options = new \DocuSign\eSign\Api\EnvelopesApi\CreateEnvelopeOptions();
                                $options->setCdseMode(null);
                                $options->setMergeRolesOnDraft(null);

                                // The api will return the envelope information
                                $envelope = $envelopeApi->createEnvelope($accountId, $envelop_definition, $options);
                                // The response is a json object so we have to decode it
                                $envelope = json_decode($envelope);

                                // Creating the embed signing ceremony
                                $recipentViewRequest = new \DocuSign\eSign\Model\RecipientViewRequest();
                                $recipentViewRequest->setAuthenticationMethod('email');

                                
                                // This will take the user outside of the client site, so we are setting the return url after the signing
                                $recipentViewRequest->setReturnUrl($return_url);
                                // We now tell DocuSign the matching Client ID
                                $recipentViewRequest->setClientUserId($clientUserId);
                                // Let DocuSign know who is signing
                                $recipentViewRequest->setEmail($template_role_email);
                                $recipentViewRequest->setUserName($template_role_name);

                                // Initiate the signing process
                                $signingView = $envelopeApi->createRecipientView($accountId, $envelope->envelopeId, $recipentViewRequest);

                                // Save the signing url to our signing_urls table to modify the return url when \Solspace\Freeform\Services\FormsService::EVENT_AFTER_GENERATE_RETURN_URL fires
                                //$token;
                                $signing_url = $signingView->getUrl();

                                $session = Craft::$app->getSession();

                                $signingUrlsService = new SigningUrlsService();
                                $signingUrl = new SigningUrlModel();

                                $signingUrl->token = $token;
                                $signingUrl->signing_url = $signing_url;

                                $signingUrlsService->saveSigningUrl($signingUrl);

                                $session->setNotice(Craft::t('hop-docusign', 'Signing URL added.'));
                            }
                        }
                    }
                } catch (DocuSign\eSign\ApiException $ex) {

                    if (isset($_GET['debug'])) {
                        echo '<pre>';
                        var_dump($ex);

                        echo '</pre>';
                        echo "Exception: " . $ex->getMessage() . "\n";
                    }

                    return false;
                }

                return true;
            }
        );

        Craft::info(
            Craft::t(
                'hop-docusign',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    private function initControllers()
    {
        if (!\Craft::$app->request->isConsoleRequest) {
            $this->controllerMap = [
                'templates' => TemplatesController::class,
            ];
        }
    }

    private function initRoutes()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $routes       = include __DIR__ . '/routes.php';
                $event->rules = array_merge($event->rules, $routes);
            }
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'hop-docusign/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
