<?php

namespace openid\helpers;

use openid\exceptions\InvalidTLDException;

define('OpenIdUriHelper_TLDs',
    '/\.(ac|ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|asia' .
    '|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br' .
    '|bs|bt|bv|bw|by|bz|ca|cat|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co' .
    '|com|coop|cr|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg' .
    '|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl' .
    '|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie' .
    '|il|im|in|info|int|io|iq|ir|is|it|je|jm|jo|jobs|jp|ke|kg|kh' .
    '|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly' .
    '|ma|mc|md|me|mg|mh|mil|mk|ml|mm|mn|mo|mobi|mp|mq|mr|ms|mt' .
    '|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no' .
    '|np|nr|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt' .
    '|pw|py|qa|re|ro|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl' .
    '|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tel|tf|tg|th|tj|tk|tl|tm' .
    '|tn|to|tp|tr|travel|tt|tv|tw|tz|ua|ug|uk|us|uy|uz|va|vc|ve' .
    '|vg|vi|vn|vu|wf|ws|xn--0zwm56d|xn--11b5bs3a9aj6g' .
    '|xn--80akhbyknj4f|xn--9t4b11yi5a|xn--deba0ad|xn--g6w251d' .
    '|xn--hgbk6aj7f53bba|xn--hlcj6aya9esc7a|xn--jxalpdlp' .
    '|xn--kgbechtv|xn--zckzah|ye|yt|yu|za|zm|zw)\.?$/');

define('OpenIdUriHelper_HostSegmentRe', "/^(?:[-a-zA-Z0-9!$&'\\(\\)\\*+,;=._~]|%[a-zA-Z0-9]{2})*$/");


class OpenIdUriHelper
{

    const AuthorityPattern = "/^([^@]*@)?([^:]*)(:.*)?/";
    const URIPattern = "&^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?&";
    const EncodedPattern = "/%([0-9A-Fa-f]{2})/";
    const URLIllegalCharRE = "/([^-A-Za-z0-9:\/\?#\[\]@\!\$&'\(\)\*\+,;=\._~\%])/";

    /**
     * Returns an absolute URL for the given one
     *
     * @param string $url absilute or relative URL
     * @return string
     */
    public static function absoluteUrl($url)
    {
        if (empty($url)) {
            return Zend_OpenId::selfUrl();
        } else if (!preg_match('|^([^:]+)://|', $url)) {
            if (preg_match('|^([^:]+)://([^:@]*(?:[:][^@]*)?@)?([^/:@?#]*)(?:[:]([^/?#]*))?(/[^?]*)?((?:[?](?:[^#]*))?(?:#.*)?)$|', Zend_OpenId::selfUrl(), $reg)) {
                $scheme = $reg[1];
                $auth = $reg[2];
                $host = $reg[3];
                $port = $reg[4];
                $path = $reg[5];
                $query = $reg[6];
                if ($url[0] == '/') {
                    return $scheme
                    . '://'
                    . $auth
                    . $host
                    . (empty($port) ? '' : (':' . $port))
                    . $url;
                } else {
                    $dir = dirname($path);
                    return $scheme
                    . '://'
                    . $auth
                    . $host
                    . (empty($port) ? '' : (':' . $port))
                    . (strlen($dir) > 1 ? $dir : '')
                    . '/'
                    . $url;
                }
            }
        }
        return $url;
    }

    /**
     * Normalizes URL according to RFC 3986 to use it in comparison operations.
     * The function gets URL argument by reference and modifies it.
     * It returns true on success and false of failure.
     *
     * @param string &$id url to be normalized
     * @return bool
     */
    public static function normalizeUrl(&$id)
    {
        // RFC 3986, 6.2.2.  Syntax-Based Normalization

        // RFC 3986, 6.2.2.2 Percent-Encoding Normalization
        $i = 0;
        $n = strlen($id);
        $res = '';
        while ($i < $n) {
            if ($id[$i] == '%') {
                if ($i + 2 >= $n) {
                    return false;
                }
                ++$i;
                if ($id[$i] >= '0' && $id[$i] <= '9') {
                    $c = ord($id[$i]) - ord('0');
                } else if ($id[$i] >= 'A' && $id[$i] <= 'F') {
                    $c = ord($id[$i]) - ord('A') + 10;
                } else if ($id[$i] >= 'a' && $id[$i] <= 'f') {
                    $c = ord($id[$i]) - ord('a') + 10;
                } else {
                    return false;
                }
                ++$i;
                if ($id[$i] >= '0' && $id[$i] <= '9') {
                    $c = ($c << 4) | (ord($id[$i]) - ord('0'));
                } else if ($id[$i] >= 'A' && $id[$i] <= 'F') {
                    $c = ($c << 4) | (ord($id[$i]) - ord('A') + 10);
                } else if ($id[$i] >= 'a' && $id[$i] <= 'f') {
                    $c = ($c << 4) | (ord($id[$i]) - ord('a') + 10);
                } else {
                    return false;
                }
                ++$i;
                $ch = chr($c);
                if (($ch >= 'A' && $ch <= 'Z') ||
                    ($ch >= 'a' && $ch <= 'z') ||
                    $ch == '-' ||
                    $ch == '.' ||
                    $ch == '_' ||
                    $ch == '~'
                ) {
                    $res .= $ch;
                } else {
                    $res .= '%';
                    if (($c >> 4) < 10) {
                        $res .= chr(($c >> 4) + ord('0'));
                    } else {
                        $res .= chr(($c >> 4) - 10 + ord('A'));
                    }
                    $c = $c & 0xf;
                    if ($c < 10) {
                        $res .= chr($c + ord('0'));
                    } else {
                        $res .= chr($c - 10 + ord('A'));
                    }
                }
            } else {
                $res .= $id[$i++];
            }
        }

        if (!preg_match('|^([^:]+)://([^:@]*(?:[:][^@]*)?@)?([^/:@?#]*)(?:[:]([^/?#]*))?(/[^?#]*)?((?:[?](?:[^#]*))?)((?:#.*)?)$|', $res, $reg)) {
            return false;
        }
        $scheme = $reg[1];
        $auth = $reg[2];
        $host = $reg[3];
        $port = $reg[4];
        $path = $reg[5];
        $query = $reg[6];
        $fragment = $reg[7]; /* strip it */ /* ZF-4358 Fragment retained under OpenID 2.0 */

        if (empty($scheme) || empty($host)) {
            return false;
        }

        // RFC 3986, 6.2.2.1.  Case Normalization
        $scheme = strtolower($scheme);
        $host = strtolower($host);

        // RFC 3986, 6.2.2.3.  Path Segment Normalization
        if (!empty($path)) {
            $i = 0;
            $n = strlen($path);
            $res = "";
            while ($i < $n) {
                if ($path[$i] == '/') {
                    ++$i;
                    while ($i < $n && $path[$i] == '/') {
                        ++$i;
                    }
                    if ($i < $n && $path[$i] == '.') {
                        ++$i;
                        if ($i < $n && $path[$i] == '.') {
                            ++$i;
                            if ($i == $n || $path[$i] == '/') {
                                if (($pos = strrpos($res, '/')) !== false) {
                                    $res = substr($res, 0, $pos);
                                }
                            } else {
                                $res .= '/..';
                            }
                        } else if ($i != $n && $path[$i] != '/') {
                            $res .= '/.';
                        }
                    } else {
                        $res .= '/';
                    }
                } else {
                    $res .= $path[$i++];
                }
            }
            $path = $res;
        }

        // RFC 3986,6.2.3.  Scheme-Based Normalization
        if ($scheme == 'http') {
            if ($port == 80) {
                $port = '';
            }
        } else if ($scheme == 'https') {
            if ($port == 443) {
                $port = '';
            }
        }
        if (empty($path)) {
            $path = '/';
        }

        $id = $scheme
            . '://'
            . $auth
            . $host
            . (empty($port) ? '' : (':' . $port))
            . $path
            . $query
            . $fragment;
        return true;
    }

    /**
     * Does this URL match the given trust root?
     *
     * Return whether the URL falls under the given trust root. This
     * does not check whether the trust root is sane. If the URL or
     * trust root do not parse, this function will return false.
     *
     * @param string $trust_root The trust root to match against
     *
     * @param string $url The URL to check
     *
     * @return bool $matches Whether the URL matches against the
     * trust root
     */
    public static function checkRealm($trust_root, $url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) return false;
        if (!self::_isSane($trust_root)) return false;

        $trust_root_parsed = self::_parse($trust_root);
        $url_parsed = self::_parse($url);
        if (!$trust_root_parsed || !$url_parsed) {
            return false;
        }

        // Check hosts matching
        if ($url_parsed['wildcard']) {
            return false;
        }
        if ($trust_root_parsed['wildcard']) {
            $host_tail = $trust_root_parsed['host'];
            $host = $url_parsed['host'];
            if ($host_tail &&
                substr($host, -(strlen($host_tail))) != $host_tail &&
                substr($host_tail, 1) != $host
            ) {
                return false;
            }
        } else {
            if ($trust_root_parsed['host'] != $url_parsed['host']) {
                return false;
            }
        }

        // Check path and query matching
        $base_path = $trust_root_parsed['path'];
        $path = $url_parsed['path'];
        if (!isset($trust_root_parsed['query'])) {
            if ($base_path != $path) {
                if (substr($path, 0, strlen($base_path)) != $base_path) {
                    return false;
                }
                if (substr($base_path, strlen($base_path) - 1, 1) != '/' &&
                    substr($path, strlen($base_path), 1) != '/'
                ) {
                    return false;
                }
            }
        } else {
            $base_query = $trust_root_parsed['query'];
            $query = @$url_parsed['query'];
            $qplus = substr($query, 0, strlen($base_query) + 1);
            $bqplus = $base_query . '&';
            if ($base_path != $path ||
                ($base_query != $query && $qplus != $bqplus)
            ) {
                return false;
            }
        }

        // The port and scheme need to match exactly
        return ($trust_root_parsed['scheme'] == $url_parsed['scheme'] &&
            $url_parsed['port'] === $trust_root_parsed['port']);
    }

    /**
     * Is this trust root sane?
     *
     * A trust root is sane if it is syntactically valid and it has a
     * reasonable domain name. Specifically, the domain name must be
     * more than one level below a standard TLD or more than two
     * levels below a two-letter tld.
     *
     * For example, '*.com' is not a sane trust root, but '*.foo.com'
     * is.  '*.co.uk' is not sane, but '*.bbc.co.uk' is.
     *
     * This check is not always correct, but it attempts to err on the
     * side of marking sane trust roots insane instead of marking
     * insane trust roots sane. For example, 'kink.fm' is marked as
     * insane even though it "should" (for some meaning of should) be
     * marked sane.
     *
     * This function should be used when creating OpenID servers to
     * alert the users of the server when a consumer attempts to get
     * the user to accept a suspicious trust root.
     *
     * @static
     * @param string $trust_root The trust root to check
     * @throws \openid\exceptions\InvalidTLDException
     * @return bool $sanity Whether the trust root looks OK
     */
    private static function _isSane($trust_root)
    {
        $parts = self::_parse($trust_root);
        if ($parts === false) {
            return false;
        }

        // Localhost is a special case
        if ($parts['host'] == 'localhost') {
            return true;
        }

        $host_parts = explode('.', $parts['host']);
        if ($parts['wildcard']) {
            // Remove the empty string from the beginning of the array
            array_shift($host_parts);
        }

        if ($host_parts && !$host_parts[count($host_parts) - 1]) {
            array_pop($host_parts);
        }

        if (!$host_parts) {
            return false;
        }

        // Don't allow adjacent dots
        if (in_array('', $host_parts, true)) {
            return false;
        }

        // Get the top-level domain of the host. If it is not a valid TLD,
        // it's not sane.
        preg_match(OpenIdUriHelper_TLDs, $parts['host'], $matches);
        if (!$matches) {
            throw new InvalidTLDException(sprintf("current host %s",$parts['host']));
        }
        $tld = $matches[1];

        if (count($host_parts) == 1) {
            return false;
        }

        if ($parts['wildcard']) {
            // It's a 2-letter tld with a short second to last segment
            // so there needs to be more than two segments specified
            // (e.g. *.co.uk is insane)
            $second_level = $host_parts[count($host_parts) - 2];
            if (strlen($tld) == 2 && strlen($second_level) <= 3) {
                return count($host_parts) > 2;
            }
        }

        return true;
    }

    private static function _parse($trust_root)
    {
        $trust_root = self::urinorm($trust_root);
        if ($trust_root === null) {
            return false;
        }

        if (preg_match("/:\/\/[^:]+(:\d+){2,}(\/|$)/", $trust_root)) {
            return false;
        }

        $parts = @parse_url($trust_root);
        if ($parts === false) {
            return false;
        }

        $required_parts = array('scheme', 'host');
        $forbidden_parts = array('user', 'pass', 'fragment');
        $keys = array_keys($parts);
        if (array_intersect($keys, $required_parts) != $required_parts) {
            return false;
        }

        if (array_intersect($keys, $forbidden_parts) != array()) {
            return false;
        }

        if (!preg_match(OpenIdUriHelper_HostSegmentRe, $parts['host'])) {
            return false;
        }

        $scheme = strtolower($parts['scheme']);
        $allowed_schemes = array('http', 'https');
        if (!in_array($scheme, $allowed_schemes)) {
            return false;
        }
        $parts['scheme'] = $scheme;

        $host = strtolower($parts['host']);
        $hostparts = explode('*', $host);
        switch (count($hostparts)) {
            case 1:
                $parts['wildcard'] = false;
                break;
            case 2:
                if ($hostparts[0] ||
                    ($hostparts[1] && substr($hostparts[1], 0, 1) != '.')
                ) {
                    return false;
                }
                $host = $hostparts[1];
                $parts['wildcard'] = true;
                break;
            default:
                return false;
        }
        if (strpos($host, ':') !== false) {
            return false;
        }

        $parts['host'] = $host;

        if (isset($parts['path'])) {
            $path = strtolower($parts['path']);
            if (substr($path, 0, 1) != '/') {
                return false;
            }
        } else {
            $path = '/';
        }

        $parts['path'] = $path;
        if (!isset($parts['port'])) {
            $parts['port'] = false;
        }


        $parts['unparsed'] = $trust_root;

        return $parts;
    }

    private static function urinorm($uri)
    {
        $uri_matches = array();
        preg_match(self::URIPattern, $uri, $uri_matches);

        if (count($uri_matches) < 9) {
            for ($i = count($uri_matches); $i <= 9; $i++) {
                $uri_matches[] = '';
            }
        }

        $illegal_matches = array();
        preg_match(self::URLIllegalCharRE,
            $uri, $illegal_matches);
        if ($illegal_matches) {
            return null;
        }

        $scheme = $uri_matches[2];
        if ($scheme) {
            $scheme = strtolower($scheme);
        }

        $scheme = $uri_matches[2];
        if ($scheme === '') {
            // No scheme specified
            return null;
        }

        $scheme = strtolower($scheme);
        if (!in_array($scheme, array('http', 'https'))) {
            // Not an absolute HTTP or HTTPS URI
            return null;
        }

        $authority = $uri_matches[4];
        if ($authority === '') {
            // Not an absolute URI
            return null;
        }

        $authority_matches = array();
        preg_match(self::AuthorityPattern,
            $authority, $authority_matches);
        if (count($authority_matches) === 0) {
            // URI does not have a valid authority
            return null;
        }

        if (count($authority_matches) < 4) {
            for ($i = count($authority_matches); $i <= 4; $i++) {
                $authority_matches[] = '';
            }
        }

        list($_whole, $userinfo, $host, $port) = $authority_matches;

        if ($userinfo === null) {
            $userinfo = '';
        }

        if (strpos($host, '%') !== -1) {
            $host = strtolower($host);
            $host = preg_replace_callback(
                self::EncodedPattern,
                function ($mo) {
                    return chr(intval($mo[1], 16));
                }, $host);
            // NO IDNA.
            // $host = unicode($host, 'utf-8').encode('idna');
        } else {
            $host = strtolower($host);
        }

        if ($port) {
            if (($port == ':') ||
                ($scheme == 'http' && $port == ':80') ||
                ($scheme == 'https' && $port == ':443')
            ) {
                $port = '';
            }
        } else {
            $port = '';
        }

        $authority = $userinfo . $host . $port;

        $path = $uri_matches[5];
        $path = preg_replace_callback(
            self::EncodedPattern,
            function ($mo) {
                $_unreserved = OpenIdUriHelper::getUnreserved();

                $i = intval($mo[1], 16);
                if ($_unreserved[$i]) {
                    return chr($i);
                } else {
                    return strtoupper($mo[0]);
                }

                return $mo[0];
            }, $path);

        $path = self::remove_dot_segments($path);
        if (!$path) {
            $path = '/';
        }

        $query = $uri_matches[6];
        if ($query === null) {
            $query = '';
        }

        $fragment = $uri_matches[8];
        if ($fragment === null) {
            $fragment = '';
        }

        return $scheme . '://' . $authority . $path . $query . $fragment;
    }

    public static function getUnreserved()
    {
        $_unreserved = array();
        for ($i = 0; $i < 256; $i++) {
            $_unreserved[$i] = false;
        }

        for ($i = ord('A'); $i <= ord('Z'); $i++) {
            $_unreserved[$i] = true;
        }

        for ($i = ord('0'); $i <= ord('9'); $i++) {
            $_unreserved[$i] = true;
        }

        for ($i = ord('a'); $i <= ord('z'); $i++) {
            $_unreserved[$i] = true;
        }

        $_unreserved[ord('-')] = true;
        $_unreserved[ord('.')] = true;
        $_unreserved[ord('_')] = true;
        $_unreserved[ord('~')] = true;

        return $_unreserved;
    }

    private static function remove_dot_segments($path)
    {
        $result_segments = array();

        while ($path) {
            if (self::startswith($path, '../')) {
                $path = substr($path, 3);
            } else if (self::startswith($path, './')) {
                $path = substr($path, 2);
            } else if (self::startswith($path, '/./')) {
                $path = substr($path, 2);
            } else if ($path == '/.') {
                $path = '/';
            } else if (self::startswith($path, '/../')) {
                $path = substr($path, 3);
                if ($result_segments) {
                    array_pop($result_segments);
                }
            } else if ($path == '/..') {
                $path = '/';
                if ($result_segments) {
                    array_pop($result_segments);
                }
            } else if (($path == '..') ||
                ($path == '.')
            ) {
                $path = '';
            } else {
                $i = 0;
                if ($path[0] == '/') {
                    $i = 1;
                }
                $i = strpos($path, '/', $i);
                if ($i === false) {
                    $i = strlen($path);
                }
                $result_segments[] = substr($path, 0, $i);
                $path = substr($path, $i);
            }
        }

        return implode('', $result_segments);
    }

    private static function startswith($s, $stuff)
    {
        return strpos($s, $stuff) === 0;
    }

    public static function checkReturnTo($return_to)
    {
        if (!filter_var($return_to, FILTER_VALIDATE_URL)) return false;
        $url_parsed = self::_parse($return_to);
        if (!$url_parsed) {
            return false;
        }
        return true;
    }

    public static function isValidUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }
}