<?php

/**
 * DocuSign for Freeform
 *
 * @author        Gilbert
 */

// Load the library
require_once('docusign/esign-client/autoload.php');

function generateDocuSignUrl($storedValues, $returnUrl) {

	// Determine if we are in dev/staging or prod, configurations will vary depending on it
	$prod = in_array($_SERVER['HTTP_HOST'], array('eyecarumba.com', 'www.eyecarumba.com')) ? true : false;
	$thank_you_urls = ['thank-you-patient-history', 'thank-you-health-release'];

	// Require the configurations
	require_once 'config.php';

	if ($prod) {
		$docusign_config = new liveDocuSignConfig;
	} else {
		$docusign_config = new devDocuSignConfig;
	}

	// Initial configuration for the api
	$config = new DocuSign\eSign\Configuration();
	$config->setHost($docusign_config->docusign_host);
	$config->addDefaultHeader("X-DocuSign-Authentication", "{\"Username\":\"" . $docusign_config->docusign_user . "\",\"Password\":\"" . $docusign_config->docusign_pass . "\",\"IntegratorKey\":\"" . $docusign_config->integrator_key . "\"}");

	$apiClient = new DocuSign\eSign\ApiClient($config);
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
		$authenticationApi = new DocuSign\eSign\Api\AuthenticationApi($apiClient);
		$options = new \DocuSign\eSign\Api\AuthenticationApi\LoginOptions();
		$loginInformation = $authenticationApi->login($options);

		// If login
		if (!empty($loginInformation)) {

			$loginAccount = $loginInformation->getLoginAccounts()[0];

			// Use the DocuSign handler url
			$host = $loginAccount->getBaseUrl();
			$host = explode("/v2",$host);
			$host = $host[0];

			// Update configuration object
			$config->setHost($host);

			// Instantiate a new DocuSign api client with the new configurations
			$apiClient = new DocuSign\eSign\ApiClient($config);

			if (isset($loginInformation)) {

				// Get the account id for creating the envelop later
				$accountId = $loginAccount->getAccountId();

				if (!empty($accountId)) {

					// Signature Request from a Template
					// create envelope call is available in the EnvelopesApi
					$envelopeApi = new DocuSign\eSign\Api\EnvelopesApi($apiClient);

					// Assign recipient to template role by setting name, email, and role name
					$templateRole = new DocuSign\eSign\Model\TemplateRole();

					// Template role information must match the placeholder role name saved in your account template
					$templateRole->setRoleName($docusign_config->template_role);
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

					// You don't need to do this if the email you are using is a plain text field
					$emailTabs = ['emailAddress'];
					$checkboxTabs = ['recordOfLastExam', 'spectaclePrescription', 'contactLensesPrescription', 'cataractsMe', 'cataractsFamily', 'lazyEyeMe', 'lazyEyeFamily', 'surgeryInjuryMe', 'glaucomaMe', 'glaucomaFamily', 'macularDegenerationMe', 'macularDegenerationFamily', 'retinalDetachmentMe', 'retinalDetachmentFamily', 'arthritisMe', 'arthritisFamily', 'diabetesMe', 'diabetesFamily', 'highBloodPressureMe', 'highBloodPressureFamily', 'kidneyDiseaseMe', 'kidneyDiseaseFamily', 'thyroidDiseaseMe', 'thyroidDiseaseFamily', 'highCholesterolMe', 'highCholesterolFamily', 'cancerMe', 'cancerFamily', 'heartProblemsMe', 'heartProblemsFamily', 'hivMe', 'lungRespiratoryDiseaseMe', 'lungRespiratoryDiseaseFamily', 'liverHepatitisMe', 'liverHepatitisFamily', 'issuesHeadaches', 'issuesBlurredDistant', 'issuesBackPain', 'issuesSoreEyes', 'issuesDryWateryEyes', 'issuesNeckPain', 'issuesBluredNear', 'issuesBurningEyes', 'issuesDoubleVision', 'issuesGlareSensitivity'];
					$radioGroupTabs = ['preferredContactMethod', 'iHaveBeen', 'doYouWearGlasses', 'doYouWearContacts', 'doYouWorkWithComputers', 'pregnant', 'nursing', 'howIsYourOverallHealth', 'myWorkComputerIsA', 'myHomeComputerIsA', 'glassesWhileWorking', 'contactsWhileWorking', 'referenceMaterialWhileWorking', 'centerOfTheComputerScreen', 'referenceMaterialCenter', 'tobacco', 'alcohol'];

					// Duplicating the names of the patient so that they appear in both places on the template
                    $storedValues['firstName1'] = $storedValues['firstName'];
                    $storedValues['lastName1'] = $storedValues['lastName'];
                    $storedValues['middleInitial'] = empty($storedValues['middleInitial']) ? '' : $storedValues['middleInitial'];
                    $storedValues['middleInitial1'] = $storedValues['middleInitial'];

                    // Setting the email and name for the template role
                    $template_role_email = !empty($storedValues['emailAddress'][0]) ? $storedValues['emailAddress'][0] : trim(implode(' ', $storedValues['emailAddress']));

                    $template_role_name = $storedValues['firstName'] .' '. $storedValues['middleInitial'] .' '. $storedValues['lastName'];
					$templateRole->setEmail($template_role_email);
					$templateRole->setName($template_role_name);

                    // Assigning fields into their 'tab', we're only using 4 types of field here so 4 tabs
					foreach ($storedValues as $key => $value) {
						if (in_array($key, $checkboxTabs)) {
							$checkboxTab = new \DocuSign\eSign\Model\Checkbox();
							$checkboxTab->setTabLabel($key);
							if ($value == 'yes') {
								$checkboxTab->setSelected(true);
							}
							$data['checkboxTabs'][] = $checkboxTab;
						} elseif (in_array($key, $radioGroupTabs)) {
							$radioGroupTab = new \DocuSign\eSign\Model\RadioGroup();
							$radioGroupTab->setGroupName($key);
							$radio = new \DocuSign\eSign\Model\Radio();
							$radio->setValue($value);
							$radio->setSelected(true);
							$radioGroupTab->setRadios(array($radio));
							$data['radioGroupTabs'][] = $radioGroupTab;
						} elseif (in_array($key, $emailTabs)) {
							$emailTab = new \DocuSign\eSign\Model\Email();
							$emailTab->setTabLabel($key);
							$value = !empty($value[0]) ? $value[0] : trim(implode(' ', $value));
							$emailTab->setValue($value);
							$data['emailTabs'][] = $emailTab;
						} else {
							$textTab = new \DocuSign\eSign\Model\Text();
							$textTab->setTabLabel($key);
							$textTab->setValue($value);
							$data['textTabs'][] = $textTab;
						}
					}

					// Create a Tab Model with the fields with values
					$tabs = new DocuSign\eSign\Model\Tabs();
					$tabs->setCheckboxTabs($data['checkboxTabs']);
					$tabs->setRadioGroupTabs($data['radioGroupTabs']);
					$tabs->setEmailTabs($data['emailTabs']);
					$tabs->setTextTabs($data['textTabs']);

					// Attach the tabs to the template role - Paitent
					$templateRole->setTabs($tabs);

					// Instantiate a new envelope object and configure settings
					$envelop_definition = new DocuSign\eSign\Model\EnvelopeDefinition();
					$envelop_definition->setEmailSubject(($returnUrl == 'DocuSign-patient' ? 'Patient Intake Form: ' : 'Health Info Release: ') . $clientUserId);

					$templateID = $returnUrl == 'DocuSign-patient' ? $docusign_config->template_patient : $docusign_config->template_release;

					// Set which template to create and which role to use
					$envelop_definition->setTemplateId($templateID);
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

					$thank_you_url = $returnUrl == 'DocuSign-patient' ? $thank_you_urls[0] : $thank_you_urls[1];
					
					// This will take the user outside of the client site, so we are setting the return url after the signing
					$recipentViewRequest->setReturnUrl($docusign_config->current_host .'/pages/'. $thank_you_url);
					// We now tell DocuSign the matching Client ID
					$recipentViewRequest->setClientUserId($clientUserId);
					// Let DocuSign know who is signing
					$recipentViewRequest->setEmail($template_role_email);
					$recipentViewRequest->setUserName($template_role_name);

					// Initiate the signing
					$signingView = $envelopeApi->createRecipientView($accountId, $envelope->envelopeId, $recipentViewRequest);

					// Return the url of the signing view
					return $signingView->getUrl();

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
}
