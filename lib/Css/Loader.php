<?php

namespace ju1ius\Css;

use ju1ius\Css\Exception\StyleSheetNotFoundException;
use ju1ius\Text\Source;
use ju1ius\Uri;

/**
 * Handles loading of stylesheets.
 * If the source is not ASCII or UTF-8 encoded, it will be converted to UTF-8.
 *
 * Relies on the CURL extension to load network urls
 **/
class Loader
{
    const MAX_AVG_LINE_LENGTH = 1024;

    /**
     * Loads a Css file or url.
     *
     * @param string|ju1ius\Uri $url
     *
     * @return Source\File
     **@throws StyleSheetNotFoundException if file doesn't exist or is not readable
     *
     */
    public static function load($url)
    {/*{{{*/
        $uri = Uri::parse($url);

        if ($uri->isAbsoluteUrl()) {
            return self::loadUrl($uri);
        }

        return self::loadFile($uri);
    }/*}}}*/

    /**
     * Loads a CSS file into a Source\File object.
     *
     * @param string|ju1ius\Uri $url The path to the file
     *
     * @return Source\File
     **@throws StyleSheetNotFoundException if file doesn't exist or is not readable
     *
     */
    public static function loadFile($url)
    {/*{{{*/
        $uri = Uri::parse($url);
        $path = realpath($uri);

        if (false === $path || !is_file($path) || !is_readable($path)) {
            throw new StyleSheetNotFoundException($path);
        }

        $content = file_get_contents($path);
        if (false === $content) {
            throw new StyleSheetNotFoundException($path);
        }

        $infos = self::_loadString($content);

        return new Source\File($path, $infos['contents'], $infos['charset']);
    }/*}}}*/

    /**
     * Loads a CSS file into a Source\File object.
     *
     * Relies on the CURL extension to load network urls
     *
     * @param string|ju1ius\Uri $url The url of the file
     *
     * @return Source\File
     **@throws StyleSheetNotFoundException if file doesn't exist or is not readable
     *
     */
    public static function loadUrl($url)
    {/*{{{*/
        $uri = Uri::parse($url);
        $response = self::_loadUrl($uri);
        $content = $response['body'];
        $charset = $response['charset'];
        $infos = self::_loadString($response['body']);
        // FIXME: Http header sometimes return wrong results
        /*
        if ($response['charset'] && !$preferFileCharset) {
            $infos['charset'] = $response['charset'];
        }
        */
        return new Source\File($url, $infos['contents'], $infos['charset']);
    }/*}}}*/

    /**
     * Loads a CSS string into a Source\String object.
     *
     * @param string $str The CSS string
     *
     * @return Source\Bytes
     **/
    public static function loadString($str, $encoding = null)
    {/*{{{*/
        $infos = self::_loadString($str);

        return new Source\Bytes($infos['contents'], $infos['charset']);
    }/*}}}*/

    private static function _loadString($str)
    {/*{{{*/
        // detect charset from BOM and/or @charset rule
        $charset = Util\Charset::detect($str);
        // Or defaults to utf-8
        if (!$charset) {
            $charset = 'utf-8';
        }
        $str = Util\Charset::removeBOM($str);

        if (!Util\Charset::isSameEncoding($charset, 'ascii')
            || !Util\Charset::isSameEncoding($charset, 'utf-8')
        ) {
            $str = Util\Charset::convert($str, 'utf-8', $charset);
            $charset = 'utf-8';
        }

        $str = self::normalizeLineLength($str);

        return [
            'contents' => $str,
            'charset' => $charset,
        ];
    }/*}}}*/

    private static function _loadUrl(Uri $url)
    {/*{{{*/
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        //curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_ENCODING, 'deflate,gzip');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'ju1ius/CssParser v0.1');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        if (false === $response) {
            throw new StyleSheetNotFoundException($url, curl_error($curl));
        };
        $infos = curl_getinfo($curl);

        curl_close($curl);

        $results = [
            'charset' => null,
            'body' => $response,
        ];
        if ($infos['content_type']) {
            if (preg_match('/charset=([a-zA-Z0-9-]*)/', $infos['content_type'], $matches)) {
                $results['charset'] = $matches[0];
            }
        }

        return $results;
    }/*}}}*/

    private static function normalizeLineLength($input)
    {/*{{{*/
        // estimate average line length
        $len = strlen($input);
        $numlines = substr_count($input, "\n") + 1;
        $avg_line_length = round($len / $numlines);
        if ($avg_line_length < self::MAX_AVG_LINE_LENGTH) return $input;

        // quick & dirty tokenizer:
        // finds position of all '}' not inside a string literal
        $patterns = Lexer::getPatterns();
        $pos = 0;
        $tokens = [];
        while ($pos < $len) {
            $chr = $input[$pos];
            if ('"' === $chr || "'" === $chr) {
                if (preg_match('/\G' . $patterns['string'] . '/iu', $input, $matches, 0, $pos)) {
                    $pos += strlen($matches[0]);
                } else {
                    preg_match('/\G' . $patterns['badstring'] . '/iu', $input, $matches, 0, $pos);
                    $pos += strlen($matches[0]);
                }
            } else if ('}' === $chr) {
                $pos++;
                $tokens[] = $pos;
                $pos++;
            } else {
                $pos++;
            }
        }

        // add a newline character after each '}'
        $output = substr($input, 0, $tokens[0]) . "\n";
        foreach ($tokens as $i => $pos) {
            $next = $i + 1;
            if (!isset($tokens[$next])) {
                break;
            }
            $output .= substr($input, $tokens[$i], $tokens[$next] - $tokens[$i]) . "\n";
        }
        $last = end($tokens);
        $output .= substr($input, $last, $len - $last);

        return $output;
    }/*}}}*/
}
