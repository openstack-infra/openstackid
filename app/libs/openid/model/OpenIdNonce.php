<?php

namespace openid\model;

use openid\exceptions\InvalidNonce;
use openid\helpers\OpenIdErrorMessages;

class OpenIdNonce
{

    const NonceRegexFormat = '/(\d{4})-(\d\d)-(\d\d)T(\d\d):(\d\d):(\d\d)Z(.*)/';
    const NonceTimeFormat = '%Y-%m-%dT%H:%M:%SZ';
    private $timestamp;
    private $unique_id;
    private $raw_format;

    /**
     * @param $nonce_str
     * @throws InvalidNonce
     */
    public function __construct($nonce_str)
    {
        // Extract a timestamp from the given nonce string
        $result = preg_match(self::NonceRegexFormat, $nonce_str, $matches);
        if ($result != 1 || count($matches) != 8) {
            throw new InvalidNonce(sprintf(OpenIdErrorMessages::InvalidNonceFormatMessage, $nonce_str));
        }

        list($unused,
            $tm_year,
            $tm_mon,
            $tm_mday,
            $tm_hour,
            $tm_min,
            $tm_sec,
            $unique_id) = $matches;

        $timestamp = @gmmktime($tm_hour, $tm_min, $tm_sec, $tm_mon, $tm_mday, $tm_year);

        if ($timestamp === false || $timestamp < 0) {
            throw new InvalidNonce(sprintf(OpenIdErrorMessages::InvalidNonceTimestampMessage, $nonce_str));
        }

        $this->timestamp = $timestamp;
        $this->unique_id = $unique_id;
        $this->raw_format = $nonce_str;
    }

    public function getRawFormat()
    {
        return $this->raw_format;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function getUniqueId()
    {
        return $this->$unique_id;
    }

   	/**
	 * The time-stamp MAY be used to reject responses that are too far away from the current time,
	 * limiting the amount of time that nonces must be stored to prevent attacks.
	 * The acceptable range is out of the scope of this specification.
	 * A larger range requires storing more nonces for a longer time.
	 * A shorter range increases the chance that clock-skew and transaction time will cause
	 * a spurious rejection.
	 * @param $allowed_skew
	 * @return bool
	 */
	public function isValid($allowed_skew)
    {
        $now = time();
        // Time after which we should not use the nonce
        $past = $now - $allowed_skew;
        // Time that is too far in the future for us to allow
        $future = $now + $allowed_skew;

        // the stamp is not too far in the future and is not too far
        // in the past
        return (($past <= $this->timestamp) && ($this->timestamp <= $future));
    }
} 