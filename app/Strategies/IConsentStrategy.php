<?php namespace Strategies;
/**
 * Interface IConsentStrategy
 * @package Strategies
 */
interface IConsentStrategy {
    /**
     * @return mixed
     */
    public function getConsent();

    /**
     * @param string $trust_action
     * @return mixed
     */
    public function postConsent($trust_action);
} 