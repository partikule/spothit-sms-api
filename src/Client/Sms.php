<?php

namespace Partikule\Spothit\Client;

use Partikule\Spothit\Base;

/**
 * Spothit API client.
 */
class Sms extends Base
{

    /**
     * Sends a simple SMS.
     *
     * @param string $sender Sender of the message (if the user allows it), 3-11 alphanumeric characters (a-zA-Z).
     * @param string $smsText Message text (maximum 459 characters).
     *
     * @return array
     *
     * @see https://www.spot-hit.fr/documentation-api#chapter2para1
     */
    public function send($smsText)
    {
        $data = [
            'key' => $this->apiKey,
            'message' => $smsText,
            'destinataires' => implode(',', $this->smsRecipients),
            'nom' => $this->campaignName,
            'expediteur' => $this->smsSender,
            'smslong' => $this->allowLongSms
        ];

        if ($this->callbackUrl) {
            $data['url'] = $this->callbackUrl;
        }

        return $this->httpRequest('/api/envoyer/sms', $data);
    }
}
