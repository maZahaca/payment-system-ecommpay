<?php
namespace RedCode\PaymentSystem;
/**
 * @author maZahaca
 */
interface IDepositManager
{
    /**
     * Return rendered deposit page template
     * @param array $params
     * @return string
     */
    public function renderTemplate($params = []);

    /**
     * Check payment status
     * @param array $params
     * @return bool
     */
    public function checkSign(array $params);
} 