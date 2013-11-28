<?php

namespace strategies;

interface IConsentStrategy {
    public function getConsent();
    public function postConsent($trust_action);
} 