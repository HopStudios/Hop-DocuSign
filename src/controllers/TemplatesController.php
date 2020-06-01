<?php
/**
 * Hop DocuSign plugin for Craft CMS 3.x
 *
 * Integrates DocuSign functionalities into your forms.
 *
 * @link      https://www.hopstudios.com
 * @copyright Copyright (c) 2020 Hop Studios
 */

namespace hopstudios\hopdocusign\controllers;

use hopstudios\hopdocusign\HopDocusign;
use hopstudios\hopdocusign\services\TemplatesService;
use hopstudios\hopdocusign\models\TemplateModel;

use Craft;
use craft\web\Controller;

/**
 * @author    Hop Studios
 * @package   HopDocusign
 * @since     1.0.0
 */
class TemplatesController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['index', 'create', 'edit', 'delete'];

    // Public Methods
    // =========================================================================

    /**
     * @return void
     */
    public function actionIndex()
    {
        $templatesService = $this->getTemplatesService();
        $templates        = $templatesService->getAllTemplates();

        $this->renderTemplate(
            'hop-docusign/templates',
            [
                'templates' => $templates,
            ]
        );
    }

    public function actionCreate()
    {
        $template = new TemplateModel();

        $this->renderTemplate(
            'hop-docusign/templates/edit',
            [
                'template' => $template
            ]
        );
    }

    public function actionEdit(int $id = null)
    {
        $template = $this->getTemplatesService()->getTemplateById($id);

        if (!$template) {
            throw new FreeformException(
                Freeform::t('Template with ID {id} not found', ['id' => $id])
            );
        }

        $this->renderTemplate(
            'hop-docusign/templates/edit',
            [
                'template' => $template
            ]
        );
    }

    public function actionSaveTemplate()
    {
        // Tells Craft we're expecting a post request
        $this->requirePostRequest();

        $session = Craft::$app->getSession();

        // Get the entire request body
        $request = Craft::$app->getRequest();

        // Initiate templates service for saving
        $templatesService = $this->getTemplatesService();

        $id = $request->getBodyParam('id');

        if (!$id) {
            $template = new TemplateModel();
        } else {
            $template = $templatesService->getTemplateById($id);
        }

        // Get the input fields
        $template->form_handle = $request->getBodyParam('form_handle');
        $template->email_handle = $request->getBodyParam('email_handle');
        $template->recipient_name = $request->getBodyParam('recipient_name');
        $template->email_subject = $request->getBodyParam('email_subject');
        $template->template_role = $request->getBodyParam('template_role');
        $template->template_id = $request->getBodyParam('template_id');

        // Save the template
        if (!$templatesService->saveTemplate($template)) {
            Craft::$app->getUrlManager()->setRouteParams([
                'template' => $template
            ]);
            return null;
        }

        $session->setNotice(Craft::t('hop-docusign', 'Template saved.'));
        return $this->redirectToPostedUrl($template);
    }

    /**
     * @return TemplatesService
     */
    private function getTemplatesService()
    {
        return new TemplatesService;
    }

    public function actionDefaultView(): Response
    {
        return $this->redirect(UrlHelper::cpUrl('freeform/' . Freeform::VIEW_DASHBOARD));
    }
}
