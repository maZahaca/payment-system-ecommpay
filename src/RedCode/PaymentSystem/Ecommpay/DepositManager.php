<?php

namespace RedCode\PaymentSystem\Ecommpay;
use devcookies\SignatureGenerator;
use RedCode\PaymentSystem\IDepositManager;

/**
 * @author maZahaca
 */ 
class DepositManager implements IDepositManager
{
    private $twig;

    private $terminal;

    private $siteId;

    private $salt;

    private $successPage;

    private $failurePage;

    public function __construct(\Twig_Environment $twig, $siteId, $salt, $successPage, $failurePage, $isTest = false)
    {
        $this->twig         = $twig;
        $this->salt         = $salt;
        $this->siteId       = $siteId;
        $this->terminal     = !$isTest ? 'https://terminal.ecommpay.com/' : 'https://terminal-sandbox.ecommpay.com/';
        $this->successPage  = $successPage;
        $this->failurePage  = $failurePage;
        $this->signgen      = new SignatureGenerator($this->salt);

        $this->twig->getLoader()->addPath(__DIR__ . '/Template', 'RedCodePaymentSystemEcommpay');
    }

    public function renderTemplate($params = [])
    {
        $paramsMap = [
            'site_id'           => null,
            'amount'            => null,
            'external_id'       => null,
            'signature'         => null,
            'success_url'       => $this->successPage,
            'decline_url'       => $this->failurePage,
            'callback_method'   => 'POST',

            'language'          => 'ru',
            'iframe'            => 1,
            'currency'          => 'RUB',
            'description'       => '',
            'single_transaction'=> 1

        ];

        $params = array_intersect($params, $paramsMap);
        $params = array_merge($paramsMap, $params);

        $params['amount'] = (float)$params['amount'];
        if($params['amount'] <= 0) {
            throw new \Exception('Amount of order can\'t be null');
        }
        $params['amount'] = (int)($params['amount'] * 100);
        $params['signature'] = $this->signgen->assemble($params);
        $uri = $this->terminal . '?' . http_build_query($params);

        return $this->twig->render(
            '@RedCodePaymentSystemEcommpay/deposit_form.html.twig',
            ['uri' => $uri]
        );
    }

    public function checkSign(array $params)
    {
        $paramsMap = [
            'signature'             => null
        ];

        $params = array_intersect($params, $paramsMap);
        $params = array_merge($paramsMap, $params);
        if($params['signature'] != $this->signgen->assemble($params)) {
            return false;
        }
        return true;
    }
}
 