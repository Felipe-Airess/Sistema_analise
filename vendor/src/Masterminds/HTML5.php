<?php
namespace Masterminds;

use DOMDocument;

/**
 * Minimal fallback implementation of Masterminds\HTML5 used by dompdf when
 * the official Masterminds/html5-php package is not installed.
 *
 * This is intentionally small: it wraps DOMDocument to provide loadHTML and
 * saveHTML methods expected by dompdf. It does not implement full HTML5
 * parsing, but is sufficient for many basic HTML documents.
 */
class HTML5
{
    protected $options = [];

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Parse HTML string and return a DOMDocument
     * @param string $str
     * @return DOMDocument
     */
    public function loadHTML($str)
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        // Suppress warnings from malformed HTML
        @$doc->loadHTML($str, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOWARNING | LIBXML_NOERROR);
        return $doc;
    }

    /**
     * Serialize a DOMDocument (or DOMNode) back to HTML string
     * @param DOMDocument|
     * @return string
     */
    public function saveHTML($dom)
    {
        if ($dom instanceof DOMDocument) {
            return $dom->saveHTML();
        }
        // Fallback for DOMNode
        if (is_object($dom) && method_exists($dom, 'ownerDocument') && $dom->ownerDocument instanceof DOMDocument) {
            return $dom->ownerDocument->saveHTML($dom);
        }
        return '';
    }
}
