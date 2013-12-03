<?php
namespace utils;

interface IHttpResponseStrategy {
    public function handle($response);
} 