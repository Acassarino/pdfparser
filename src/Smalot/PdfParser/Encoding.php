<?php

namespace Smalot\PdfParser;

use Smalot\PdfParser\Element\ElementNumeric;

/**
 * Class Encoding
 * @package PdfParser
 */
class Encoding extends Object
{
    /**
     * @var array
     */
    protected $encoding;

    /**
     * @var array
     */
    protected $differences;

    /**
     * @var array
     */
    protected $mapping;

    /**
     * @var bool
     */
    protected $init_done = false;

    /**
     *
     */
    protected function init()
    {
        if ($this->init_done) {
            return;
        } else {
            $this->init_done = true;
        }

        // Load reference table charset.
        $baseEncoding = $this->get('BaseEncoding')->getContent();
        $encoding     = array();
        $file         = $baseEncoding . '.php';

        if (file_exists($file)) {
            include $file;
        } else {
            die('Missing encoding data file: "' . $file . '".');
        }

        $this->encoding = $encoding;

        // Build table including differences.
        $differences       = $this->get('Differences')->getContent();
        $this->differences = array();
        $code              = 0;

        foreach ($differences as $difference) {
            /** @var ElementNumeric $difference */
            if ($difference instanceof ElementNumeric) {
                $code = $difference->getContent();
                continue;
            }

            $this->differences[$code] = $difference->getContent();

            // For the next char.
            $code++;
        }

        // Build final mapping (custom => standard).
        $this->mapping = array();
        $table         = array_flip(array_reverse($this->encoding, true));

        foreach ($this->differences as $code => $difference) {
            /** @var string $difference */
            $this->mapping[$code] = $table[$difference];
        }
    }

    /**
     * @param int  $char
     * @param bool $is_hexa
     *
     * @return int
     */
    public function translateChar($dec)
    {
        $this->init();

        if (isset($this->mapping[$dec])) {
            $char = chr($this->mapping[$dec]);
        } elseif (isset($this->encoding[$dec])) {
            $char = chr($dec);
        } else {
            $char = chr($dec);
        }

        return $char;
    }
}