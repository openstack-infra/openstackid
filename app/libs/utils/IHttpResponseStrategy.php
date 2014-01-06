<?php
namespace utils;

/**
 * Interface IHttpResponseStrategy
 * Defines an interface to handle http responses
 * @package utils
 */
interface IHttpResponseStrategy {
    public function handle($response);
} 