<?php 

namespace Partikule\Spothit;

use Partikule\Spothit\Exception\RequestException;
use Partikule\Spothit\Exception\ResponseException;
use Partikule\Spothit\Exception\ResponseCodeException;

class Base {

    const BASE_URL = 'https://www.spot-hit.fr';

    const SMS_TYPE_LOWCOST = 'lowcost';
    const SMS_TYPE_PREMIUM = 'premium';

    /**
     * API key available on your manager.
     *
     * @var string
     */
    public $apiKey;

    /**
     * @var string self::SMS_TYPE_*
     */
    public $smsType = self::SMS_TYPE_LOWCOST;

    /**
     * Numbers in international format + XXZZZZZ.
     *
     * @var array
     */
    public $smsRecipients = [];

    /**
     * @var DateTime
     */
    public $sendingTime;

    /**
     * Sender of the message (if the user allows it), 3-11 alphanumeric characters (a-zA-Z).
     *
     * @var string
     */
    public $smsSender = 'OneSender';

    /**
     * Allow long SMS
     *
     * @var bool
     */
    public $allowLongSms = 1;

    /**
     * callback URL
     *
     * @var string
     */
    public $callbackUrl;


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
        $this->callbackUrl = $url;
    }

    public function httpRequest($path, array $fields)
    {
        set_time_limit(0);

        $qs = [];
        foreach ($fields as $k => $v) {
            $qs[] = $k . '=' . urlencode($v);
        }

        $request = implode('&', $qs);

        if (false === $ch = curl_init(self::BASE_URL . $path)) {
            throw new RequestException(sprintf('Request initialization to "%s" failed.', $path));
        }

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

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

}