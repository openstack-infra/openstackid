<?php

namespace utils\services;

/**
 * Interface ISecurityPolicyCounterMeasure
 * implements Checkpoint Pattern
 * depicted on Architectural Patterns for Enabling Application Security - Yoder/Barcalow
 * Defines contract for a custom Security Policy Counter measure
 * @package services
 */
interface ISecurityPolicyCounterMeasure
{
    public function trigger(array $params = array());
} 