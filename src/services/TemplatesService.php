<?php
/**
 * Hop DocuSign plugin for Craft CMS 3.x
 *
 * Integrates DocuSign functionalities into your forms.
 *
 * @link      https://www.hopstudios.com
 * @copyright Copyright (c) 2020 Hop Studios
 */

namespace hopstudios\hopdocusign\services;

use hopstudios\hopdocusign\HopDocusign;
use hopstudios\hopdocusign\models\TemplateModel;
use hopstudios\hopdocusign\records\TemplateRecord;

use Craft;
use craft\base\Component;
use craft\db\Query;

/**
 * @author    Hop Studios
 * @package   HopDocusign
 * @since     1.0.0
 */
class TemplatesService extends Component
{
    // Public Methods
    // =========================================================================

    /*
     * @return mixed
     */
    public function getAllTemplates()
    {
        $templates = $this->getTemplateQuery()->all();

        return $templates;
    }

    /*
     * @return template model
     */
    public function getTemplateById($id)
    {
        $template = $this->getTemplateQuery()->where(['id' => $id])->one();

        return new TemplateModel($template);
    }

    /*
     * @return template model
     */
    public function getTemplateByHandle($form_handle)
    {
        $template = $this->getTemplateQuery()->where(['form_handle' => $form_handle])->one();

        if ($template) {
            return new TemplateModel($template);
        }
        return false;
    }

    public function saveTemplate(TemplateModel $template)
    {
        $isNew = !$template->id;

        if (!$isNew) {
            $record = TemplateRecord::findOne(['id' => $template->id]);
        } else {
            $record = TemplateRecord::create();
        }

        $record->form_handle    = $template->form_handle;
        $record->email_handle = $template->email_handle;
        $record->recipient_name = $template->recipient_name;
        $record->email_subject = $template->email_subject;
        $record->template_role = $template->template_role;
        $record->template_id   = $template->template_id;

        $record->validate();
        $template->addErrors($record->getErrors());

        if ($template->hasErrors()) {
            Craft::info('Template not saved due to validation error.', __METHOD__);
            return false;
        }

        if (!\Craft::$app->getDb()->getTransaction()) {
            $transaction = \Craft::$app->getDb()->getTransaction() ?? \Craft::$app->getDb()->beginTransaction();
        }

        // Let's save our template
        try {
            $record->save(false);

            if ($isNew) {
                $template->id = $record->id;
            }

            if ($transaction !== null) {
                $transaction->commit();
            }

            return true;
        } catch (\Exception $e) {
            if ($transaction !== null) {
                $transaction->rollBack();
            }

            throw $e;
        }
    }

    /**
     * @param int $templateId
     *
     * @return bool
     * @throws \Exception
     */
    public function deleteById($templateId)
    {
        $record = $this->getTemplateById($templateId);

        if (!$record) {
            return false;
        }

        $transaction = \Craft::$app->getDb()->getTransaction() ?? \Craft::$app->getDb()->beginTransaction();
        try {
            $affectedRows = \Craft::$app
                ->getDb()
                ->createCommand()
                ->delete(TemplateRecord::tableName(), ['id' => $templateId])
                ->execute();

            if ($transaction !== null) {
                $transaction->commit();
            }

            return (bool) $affectedRows;
        } catch (\Exception $exception) {
            if ($transaction !== null) {
                $transaction->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * @return Query
     */
    private function getTemplateQuery(): Query
    {
        return (new Query())
            ->select(
                [
                    'templates.id',
                    'templates.form_handle',
                    'templates.email_handle',
                    'templates.recipient_name',
                    'templates.email_subject',
                    'templates.template_role',
                    'templates.template_id',
                ]
            )
            ->from(TemplateRecord::tableName() . ' templates')
            ->orderBy(['templates.id' => SORT_ASC]);
    }
}
