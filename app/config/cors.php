<?php
/**
 * CORS Configuration
 */
return array(
    /**
     * http://www.w3.org/TR/cors/#access-control-allow-credentials-response-header
     */
    'AllowCredentials'    => 'true',
    /**
     * http://www.w3.org/TR/cors/#access-control-max-age-response-header
     */
    'UsePreflightCaching' => true,
    'MaxAge'              => 32000,
    /**
     * http://www.w3.org/TR/cors/#access-control-allow-headers-response-header
     */
    'AllowedHeaders'      => 'origin, content-type, accept, authorization, x-requested-with',
    /**
     * http://www.w3.org/TR/cors/#access-control-allow-methods-response-header
     */
    'AllowedMethods'      => 'GET, POST, OPTIONS, PUT, DELETE',
);