<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/17/13
 * Time: 4:10 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\helpers;


class OpenIdUriHelper {

    /**
     * Returns an absolute URL for the given one
     *
     * @param string $url absilute or relative URL
     * @return string
     */
    static public function absoluteUrl($url)
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
    static public function normalizeUrl(&$id)
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
                    $ch == '~') {
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

    static public function checkRealmWildcard($root,$realm){
        $n = strpos($realm, '://*.');
        if ($n != false) {
            $regex = '/^'. preg_quote(substr($realm, 0, $n+3), '/'). '[A-Za-z1-9_\.]+?'. preg_quote(substr($realm, $n+4), '/'). '/';
            if (preg_match($regex, $root)) {
                return true;
            }
        }
        return false;
    }
}