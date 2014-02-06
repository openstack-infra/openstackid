<?php

namespace oauth2\services;

/**
 * Interface IAllowedOriginService
 * CRUD Service for clients allowed origins
 * @package oauth2\services
 */
interface IAllowedOriginService {

    public function get($id);
    public function getByUri($uri);
    public function create($uri,$client_id);
    public function delete($id);
    public function deleteByUri($uri);
} 