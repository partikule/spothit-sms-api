<?php

namespace Spothit\Api;

use Spothit\Api\Exception\RequestException;
use Spothit\Api\Exception\ResponseException;
use Spothit\Api\Exception\ResponseCodeException;

/**
 * Spothit API client.
 */
class Client
{
    const BASE_URL = 'http://www.spot-hit.fr';

    const SMS_TYPE_LOWCOST = 'lowcost';
    const SMS_TYPE_PREMIUM = 'premium';

    /**
     * User login (email address).
     *
     * @var string
     */
    private $userLogin;

    /**
     * API key available on your manager.
     *
     * @var string
     */
    private $apiKey;

    /**
     * @var string self::SMS_TYPE_*
     */
    private $smsType = self::SMS_TYPE_LOWCOST;

    /**
     * Numbers in international format + XXZZZZZ.
     *
     * @var array
     */
    private $smsRecipients = [];

    /**
     * @var DateTime
     */
    private $sendingTime;

    /**
     * Sender of the message (if the user allows it), 3-11 alphanumeric characters (a-zA-Z).
     *
     * @var string
     */
    private $smsSender = 'OneSender';

    /**
     * Allow long SMS
     *
     * @var bool
     */
    private $allowLongSms = 1;

    /**
     * callback URL
     *
     * @var string
     */
    private $callbackUrl;


    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->sendingTime = new \DateTime();
    }

    public function setSmsType($smsType)
    {
        $this->smsType = $smsType;
    }

    public function setSmsRecipients(array $smsRecipients)
    {
        $this->smsRecipients = $smsRecipients;
    }

    public function setSendingTime(\DateTime $sendingTime)
    {
        $this->sendingTime = $sendingTime;
    }

    public function setSmsSender($smsSender)
    {
        $this->smsSender = $smsSender;
    }

    public function setCallbackUrl($url)
    {
        $this->callbackUrl=$url;
    }

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

    /**
     * Returns credit balance as a number of Euros left on account.
     *
     * @return array
     *
     * @see https://www.spot-hit.fr/api/credits
     */
    public function getCredit()
    {
        $data = [
            'key' => $this->apiKey,
        ];

        return $this->httpRequest('/api/credits', $data);
    }

    private function httpRequest($path, array $fields)
    {
        set_time_limit(0);

        $qs = [];
        foreach ($fields as $k => $v) {
            $qs[] = $k.'='.urlencode($v);
        }

        $request = implode('&', $qs);

        if (false === $ch = curl_init(self::BASE_URL.$path)) {
            throw new RequestException(sprintf('Request initialization to "%s" failed.', $path));
        }

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_PORT, 80);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if (false === $result = curl_exec($ch)) {
            curl_close($ch);

            throw new ResponseException(sprintf(
                'Failed to get response from "%s". Response: %s.',
                $path,
                $result
            ));
        }

        if (200 !== $code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
            throw new ResponseException(sprintf(
                'Server returned "%s" status code. Response: %s.',
                $code,
                $result
            ));
        }

        curl_close($ch);

        $responseArray = json_decode($result, true);

        // If 'resultat' == 1, the message was send properly
        if ($responseArray['resultat'] != 1) {
            throw new ResponseCodeException(sprintf(
                'Server returned "%s" error code.',
                $responseArray['erreurs']
            ), $responseArray['erreurs']);
        }

        return $responseArray;
    }
}
