<?php namespace Utils\Services;

/**
 * Interface ISecurityPolicyCounterMeasure
 * implements Checkpoint Pattern
 * depicted on Architectural Patterns for Enabling Application Security - Yoder/Barcalow
 * Defines contract for a custom Security Policy Counter measure
 * @package Utils\Services
 */
interface ISecurityPolicyCounterMeasure
{
    /**
     * @param array $params
     * @return $this
     */
    public function trigger(array $params = array());
} 