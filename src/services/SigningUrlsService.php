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
use hopstudios\hopdocusign\models\SigningUrlModel;
use hopstudios\hopdocusign\records\SigningUrlRecord;

use Craft;
use craft\base\Component;
use craft\db\Query;

/**
 * @author    Hop Studios
 * @package   HopDocusign
 * @since     1.1.0
 */
class SigningUrlsService extends Component
{
    // Public Methods
    // =========================================================================

    /*
     * @return signing_url model
     */
    public function getSigningUrlByToken($token)
    {
        $signing_url = $this->getSigningUrlQuery()->where(['token' => $token])->one();

        return new SigningUrlModel($signing_url);
    }

    /*
     * @return bool
     */
    public function deleteOldSigningUrls()
    {
        $signingUrlRecord = new SigningUrlRecord;

        // Using ActiveRecord to delete the signing urls
        $signingUrlRecord::deleteAll('dateCreated < DATE_SUB(CURDATE(), INTERVAL 1 DAY)');

        return true;
    }

    public function saveSigningUrl(SigningUrlModel $signing_url)
    {
        $record = SigningUrlRecord::create();

        $record->token       = $signing_url->token;
        $record->signing_url = $signing_url->signing_url;

        $record->validate();
        $signing_url->addErrors($record->getErrors());

        if ($signing_url->hasErrors()) {
            Craft::info('Signing URL not saved due to validation error.', __METHOD__);
            return false;
        }

        // Let's save our signing_url
        try {
            $record->save();

            return true;
        } catch (\Exception $e) {
            echo '<pre>';
            print_r($e);
            echo '</pre>';
            throw $e;
        }
    }

    /**
     * @return Query
     */
    private function getSigningUrlQuery(): Query
    {
        return (new Query())
            ->select(
                [
                    'signing_urls.id',
                    'signing_urls.token',
                    'signing_urls.signing_url',
                ]
            )
            ->from(SigningUrlRecord::tableName() . ' signing_urls')
            ->orderBy(['signing_urls.id' => SORT_ASC]);
    }
}
