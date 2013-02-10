<?php
namespace PhpClosure;
/**
 * Copyright 2010 Daniel Pupius (http://code.google.com/p/php-closure/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */


/**
 * PHP wrapper for the Google Closure JS Compiler web service.
 *
 * Handles caching and recompilation of sources.  A recompilation will occur
 * whenever a source file, or the script calling the compiler, changes.
 *
 * The class will handle the Last-Modified and 304 redirects for you, but if
 * you should set Cache-Control headers appropriate for your needs.
 *
 * Example usage:
 *
 * include("php-closure.php");
 *
 * $c = new PhpClosure();
 * $c->add("my-app.js")
 *   ->add("popup.js")
 *   ->advancedMode()
 *   ->useClosureLibrary()
 *   ->cacheDir("/tmp/js-cache/")
 *   ->write();
 *
 * See http://code.google.com/closure/compiler/docs/api-ref.html for more
 * details on the compiler options.
 */
class PhpClosure
{
    const MODE_WHITESPACE_ONLY = 'WHITESPACE_ONLY';
    const MODE_SIMPLE_OPTIMIZATIONS = 'SIMPLE_OPTIMIZATIONS';
    const MODE_ADVANCED_OPTIMIZATIONS = 'ADVANCED_OPTIMIZATIONS';

    const WORNING_LEVEL_DEFAULT = "DEFAULT";
    const WORNING_LEVEL_QUITE = "QUITE";
    const WORNING_LEVEL_VERBOSE = "VERBOSE";

    const OUTPUT_FORMAT_XML = 'xml';
    const OUTPUT_FORMAT_JSON = 'json';

    private $_srcs = array();
    private $_mode = self::MODE_SIMPLE_OPTIMIZATIONS;
    private $_warning_level = self::WORNING_LEVEL_DEFAULT;
    private $_use_closure_library = false;
    private $_pretty_print = false;
    private $_debug = true;
    private $_cache_dir = "";
    private $_code_url_prefix = "";
    private $_output_format = self::OUTPUT_FORMAT_XML;

    public function __construct()
    {
    }

    /**
     * @return static
     */
    public static function create()
    {
        return new static;
    }

    /**
     * Adds a source file to the list of files to compile.  Files will be
     * concatenated in the order they are added.
     *
     * @param $file
     * @return $this
     */
    public function add($file)
    {
        $this->_srcs[] = $file;
        return $this;
    }

    /**
     * Sets the directory where the compilation results should be cached, if
     * not set then caching will be disabled and the compiler will be invoked
     * for every request (NOTE: this will hit ratelimits pretty fast!)
     *
     * @param $dir
     * @return $this
     */
    public function cacheDir($dir)
    {
        $this->_cache_dir = $dir;
        return $this;
    }

    /**
     * Sets whether to use the Closure Library.  i.e. goog.requires will be
     * resolved and the library code made available.
     *
     * @return $this
     */
    public function useClosureLibrary()
    {
        $this->_use_closure_library = true;
        return $this;
    }

    /**
     * Sets the URL prefix to use with the Closure Compiler service's code_url
     * parameter.
     *
     * By default PHP-Closure posts the scripts to the compiler service, however,
     * this is subject to a 200000-byte size limit for the whole post request.
     *
     * Using code_url tells the compiler service the URLs of the scripts to
     * fetch.  The file paths added in add() must therefore be relative to this
     * URL.
     *
     * Example usage:
     *
     * $c->add("js/my-app.js")
     *   ->add("js/popup.js")
     *   ->useCodeUrl('http://www.example.com/app/')
     *   ->cacheDir("/tmp/js-cache/")
     *   ->write();
     *
     * This assumes your PHP script is in a directory /app/ and that the JS is in
     * /app/js/ and accessible via HTTP.
     *
     * @param $code_url_prefix
     * @return $this
     */
    public function useCodeUrl($code_url_prefix)
    {
        $this->_code_url_prefix = $code_url_prefix;
        return $this;
    }

    /**
     * Tells the compiler to pretty print the output.
     *
     * @return $this
     */
    public function prettyPrint()
    {
        $this->_pretty_print = true;
        return $this;
    }

    /**
     * Turns of the debug info.
     * By default statistics, errors and warnings are logged to the console.
     *
     * @return $this
     */
    public function hideDebugInfo()
    {
        $this->_debug = false;
        return $this;
    }

    /**
     * Sets the compilation mode to optimize whitespace only.
     *
     * @return $this
     */
    public function whitespaceOnly()
    {
        $this->_mode = self::MODE_WHITESPACE_ONLY;
        return $this;
    }

    /**
     * Sets the compilation mode to simple optimizations.
     *
     * @return $this
     */
    public function simpleMode()
    {
        $this->_mode = self::MODE_SIMPLE_OPTIMIZATIONS;
        return $this;
    }

    /**
     * Sets the compilation mode to advanced optimizations (recommended).
     *
     * @return $this
     */
    public function advancedMode()
    {
        $this->_mode = self::MODE_ADVANCED_OPTIMIZATIONS;
        return $this;
    }

    /**
     * Gets the compilation mode from the URL, set the mode param to
     * 'w', 's' or 'a'.
     *
     * @return $this
     */
    public function getModeFromUrl()
    {
        if ($_GET['mode'] == 's') $this->simpleMode();
        else if ($_GET['mode'] == 'a') $this->advancedMode();
        else $this->whitespaceOnly();
        return $this;
    }

    /**
     * Sets the warning level to QUIET.
     *
     * @return $this
     */
    public function quiet()
    {
        $this->_warning_level = self::WORNING_LEVEL_QUITE;
        return $this;
    }

    /**
     * Sets the default warning level.
     *
     * @return $this
     */
    public function defaultWarnings()
    {
        $this->_warning_level = self::WORNING_LEVEL_DEFAULT;
        return $this;
    }

    /**
     * Sets the warning level to VERBOSE.
     *
     * @return $this
     */
    public function verbose()
    {
        $this->_warning_level = self::WORNING_LEVEL_VERBOSE;
        return $this;
    }

    /**
     * @param $format
     * @return $this
     */
    public function setOutputFormat($format)
    {
        $this->_output_format = $format;
        return $this;
    }

    /**
     * Writes the compiled response.  Reading from either the cache, or
     * invoking a recompile, if necessary.
     */
    public function write()
    {
        header("Content-Type: text/javascript");

        // No cache directory so just dump the output.
        if ($this->_cache_dir == "") {
            echo $this->_compile();

        } else {
            $cache_file = $this->_getCacheFileName();
            if ($this->_isRecompileNeeded($cache_file)) {
                $result = $this->_compile();
                file_put_contents($cache_file, $result);
                echo $result;
            } else {
                // No recompile needed, but see if we can send a 304 to the browser.
                $cache_mtime = filemtime($cache_file);
                $etag = md5_file($cache_file);
                header("Last-Modified: " . gmdate("D, d M Y H:i:s", $cache_mtime) . " GMT");
                header("Etag: $etag");
                if (@strtotime(@$_SERVER['HTTP_IF_MODIFIED_SINCE']) == $cache_mtime ||
                    @trim(@$_SERVER['HTTP_IF_NONE_MATCH']) == $etag
                ) {
                    header("HTTP/1.1 304 Not Modified");
                } else {
                    // Read the cache file and send it to the client.
                    echo file_get_contents($cache_file);
                }
            }
        }
    }

    /**
     * @param $cache_file
     * @return bool
     */
    private function _isRecompileNeeded($cache_file)
    {
        // If there is no cache file, we obviously need to recompile.
        if (!file_exists($cache_file)) return true;

        $cache_mtime = filemtime($cache_file);

        // If the source files are newer than the cache file, recompile.
        foreach ($this->_srcs as $src) {
            if (filemtime($src) > $cache_mtime) return true;
        }

        // If this script calling the compiler is newer than the cache file,
        // recompile.  Note, this might not be accurate if the file doing the
        // compilation is loaded via an include().
        if (filemtime($_SERVER["SCRIPT_FILENAME"]) > $cache_mtime) return true;

        // Cache is up to date.
        return false;
    }

    /**
     * @return string
     */
    private function _compile()
    {
        $response = $this->_prepareResponse($this->_makeRequest(), $this->_output_format);
        return $response;
    }

    /**
     * @param $response
     * @param string $type
     * @return string
     */
    private function _prepareResponse($response, $type = self::OUTPUT_FORMAT_XML)
    {
        switch (true) {
            case self::OUTPUT_FORMAT_JSON === $type:
                $result = $this->_prepareResponseJson($response);
                break;
            case self::OUTPUT_FORMAT_XML === $type:
                $result = $this->_prepareResponseXml($response);
                break;
            default:
                $result = '';
        }
        return $result;
    }

    /**
     * @param $response
     * @return string
     */
    private function _prepareResponseXml($response)
    {
        // Quieten strict notices.
        $code = $originalSize = $originalGzipSize = $compressedSize = $compressedGzipSize = $compileTime = '';
        $tree = $this->_parseXml($response);

        foreach ($tree as $node) {
            switch ($node["tag"]) {
                case "compiledCode":
                    $code = $node["value"];
                    break;
                case "warnings":
                    $warnings = $node["value"];
                    break;
                case "errors":
                    $errors = $node["value"];
                    break;
                case "statistics":
                    foreach ($node["value"] as $stat) {
                        switch ($stat["tag"]) {
                            case "originalSize":
                                $originalSize = $stat["value"];
                                break;
                            case "originalGzipSize":
                                $originalGzipSize = $stat["value"];
                                break;
                            case "compressedSize":
                                $compressedSize = $stat["value"];
                                break;
                            case "compressedGzipSize":
                                $compressedGzipSize = $stat["value"];
                                break;
                            case "compileTime":
                                $compileTime = $stat["value"];
                                break;
                        }
                    }
                    break;
            }
        }
        $result = "";
        if ($this->_debug) {
            $result = "if(window.console&&window.console.log){\r\n" .
                "window.console.log('Closure Compiler Stats:\\n" .
                "-----------------------\\n" .
                "Original Size: $originalSize\\n" .
                "Original Gzip Size: $originalGzipSize\\n" .
                "Compressed Size: $compressedSize\\n" .
                "Compressed Gzip Size: $compressedGzipSize\\n" .
                "Compile Time: $compileTime\\n" .
                "Generated: " . Date("Y/m/d H:i:s T") . "');\r\n";
            if (isset($errors)) $result .= $this->_printWarnings($errors, "error", $this->_output_format);
            if (isset($warnings)) $result .= $this->_printWarnings($warnings, "warn", $this->_output_format);
            $result .= "}\r\n\r\n";
        }
        $result .= "$code \r\n";
        return $result;
    }

    /**
     * @param $response
     * @return string
     */
    private function _prepareResponseJson($response)
    {
        $result = '';
        try {
            $response = json_decode($response, true);
            if ($this->_debug) {
                $result = "if(window.console&&window.console.log){\r\n" .
                    "window.console.log('Closure Compiler Stats:\\n" .
                    "-----------------------\\n" .
                    "Original Size: $response[statistics][originalSize]\\n" .
                    "Original Gzip Size: $response[statistics][originalGzipSize]\\n" .
                    "Compressed Size: $response[statistics][compressedSize]\\n" .
                    "Compressed Gzip Size: $response[statistics][compressedGzipSize]\\n" .
                    "Compile Time: $response[statistics][compileTime]\\n" .
                    "Generated: " . Date("Y/m/d H:i:s T") . "');\r\n";
                if (isset($response['errors'])) $result .= $this->_printWarnings($response['errors'], "error", $this->_output_format);
                if (isset($response['warnings'])) $result .= $this->_printWarnings($response['warnings'], "warn", $this->_output_format);
                $result .= "}\r\n\r\n";
            }
            $result .= "$response[compiledCode] \r\n";
        } catch (Exception $e) {
        }
        return $result;
    }

    /**
     * @param $warnings
     * @param string $level
     * @param string $outputType
     * @return string
     */
    private function _printWarnings($warnings, $level = "log", $outputType = self::OUTPUT_FORMAT_XML)
    {
        switch (true) {
            case self::OUTPUT_FORMAT_JSON === $outputType:
                $result = $this->_printWarningsJson($warnings, $level);
                break;
            case self::OUTPUT_FORMAT_XML === $outputType:
                $result = $this->_printWarningsXml($warnings, $level);
                break;
            default:
                $result = '';
        }
        return $result;
    }

    /**
     * @param $warnings
     * @param string $level
     * @return string
     */
    private function _printWarningsXml($warnings, $level = "log")
    {
        $result = "";
        foreach ($warnings as $warning) {
            $desc = addslashes($warning["value"]);
            $type = $warning["attributes"]["type"];
            $lineno = $warning["attributes"]["lineno"];
            $charno = $warning["attributes"]["charno"];
            $line = addslashes($warning["attributes"]["line"]);
            $result .= "window.console.$level('$type: $desc\\nLine: $lineno\\nChar: $charno\\nLine: $line');\r\n";
        }
        return $result;
    }

    /**
     * @param $warnings
     * @param string $level
     * @return string
     */
    private function _printWarningsJson($warnings, $level = "log")
    {
        $result = "";
        foreach ($warnings as $warning) {
            $desc = addslashes($warning["warning"]);
            $type = $warning["type"];
            $lineno = $warning["lineno"];
            $charno = $warning["charno"];
            $line = addslashes($warning["line"]);
            $result .= "window.console.$level('$type: $desc\\nLine: $lineno\\nChar: $charno\\nLine: $line');\r\n";
        }
        return $result;
    }

    /**
     * @return string
     */
    private function _getCacheFileName()
    {
        return $this->_cache_dir . $this->_getHash() . ".js";
    }

    /**
     * @return string
     */
    private function _getHash()
    {
        return md5(implode(",", $this->_srcs) . "-" .
            $this->_mode . "-" .
            $this->_warning_level . "-" .
            $this->_use_closure_library . "-" .
            $this->_pretty_print . "-" .
            $this->_debug);
    }

    /**
     * @return string
     */
    private function _getParams()
    {
        $params = array();
        foreach ($this->_getParamList() as $key => $value) {
            $params[] = preg_replace("/_[0-9]$/", "", $key) . "=" . urlencode($value);
        }
        return implode("&", $params);
    }

    /**
     * @return array
     */
    private function _getParamList()
    {
        $params = array();
        if ($this->_code_url_prefix) {
            // Send the URL to each source file instead of the raw source.
            $i = 0;
            foreach ($this->_srcs as $file) {
                $params["code_url_$i"] = $this->_code_url_prefix . $file;
                $i++;
            }
        } else {
            $params["js_code"] = $this->_readSources();
        }
        $params["compilation_level"] = $this->_mode;
        $params["output_format"] = $this->_output_format;
        $params["warning_level"] = $this->_warning_level;
        if ($this->_pretty_print) $params["formatting"] = "pretty_print";
        if ($this->_use_closure_library) $params["use_closure_library"] = "true";
        $params["output_info_1"] = "compiled_code";
        $params["output_info_2"] = "statistics";
        $params["output_info_3"] = "warnings";
        $params["output_info_4"] = "errors";
        return $params;
    }

    /**
     * @return string
     */
    private function _readSources()
    {
        $code = "";
        foreach ($this->_srcs as $src) {
            $code .= file_get_contents($src) . "\n\n";
        }
        return $code;
    }

    /**
     * @return string
     */
    private function _makeRequest()
    {
        $data = $this->_getParams();
        $referer = @$_SERVER["HTTP_REFERER"] or "";

        $fp = fsockopen("closure-compiler.appspot.com", 80) or die("Unable to open socket");

        if ($fp) {
            fputs($fp, "POST /compile HTTP/1.1\r\n");
            fputs($fp, "Host: closure-compiler.appspot.com\r\n");
            fputs($fp, "Referer: $referer\r\n");
            fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
            fputs($fp, "Content-length: " . strlen($data) . "\r\n");
            fputs($fp, "Connection: close\r\n\r\n");
            fputs($fp, $data);

            $result = "";
            while (!feof($fp)) {
                $result .= fgets($fp, 128);
            }

            fclose($fp);
        }

        $data = substr($result, (strpos($result, "\r\n\r\n") + 4));
        if (strpos(strtolower($result), "transfer-encoding: chunked") !== FALSE) {
            $data = $this->_unchunk($data);
        }

        return $data;
    }

    /**
     * @param $data
     * @return string
     */
    private function _unchunk($data)
    {
        $fp = 0;
        $outData = "";
        while ($fp < strlen($data)) {
            $rawnum = substr($data, $fp, strpos(substr($data, $fp), "\r\n") + 2);
            $num = hexdec(trim($rawnum));
            $fp += strlen($rawnum);
            $chunk = substr($data, $fp, $num);
            $outData .= $chunk;
            $fp += strlen($chunk);
        }
        return $outData;
    }

    /**
     * @param $data
     * @return array|null
     */
    private function _parseXml($data)
    {
        $xml = new \SimpleXMLElement($data);
        return $this->_parseXmlHelper($xml);
    }

    /**
     * @param $xml
     * @return array|null
     */
    private function _parseXmlHelper($xml)
    {
        $tree = null;
        foreach ($xml->children() as $name => $child) {
            $value = (string)$child;
            $node = array('tag' => $name, 'value' => count($child->children()) == 0 ? $value : $this->_parseXmlHelper($child));

            foreach ($child->attributes() as $attr => $value) {
                $node['attributes'][$attr] = $value[0];
            }
            $tree[] = $node;
        }
        return $tree;
    }
}