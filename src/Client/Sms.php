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
            'type' => $this->smsType,
            'message' => $smsText,
            'destinataires' => implode(',', $this->smsRecipients),
            'expediteur' => $this->smsSender,
            'smslong' => $this->allowLongSms
        ];

        if ($this->sendingTime > (new \DateTime())) {
            $data['date'] = $this->sendingTime->getTimestamp();
        }

        if ($this->callbackUrl) {
            $data['url'] = $this->callbackUrl;
        }

        return $this->httpRequest('/api/envoyer/sms', $data);
    }
}
