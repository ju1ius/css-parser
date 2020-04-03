<?php

namespace ju1ius\Text\Source;


/**
 * A source file
 */
class File extends Bytes
{
    protected
        $url;

    /**
     * @param Bytes $url
     * @param Bytes $contents
     * @param Bytes $encoding
     **/
    public function __construct($url, $contents, $encoding = "utf-8")
    {
        $this->url = $url;
        parent::__construct($contents, $encoding);
    }

    /**
     * Returns the url of the source file
     *
     * @return Bytes
     **/
    public function getUrl()
    {
        return $this->url;
    }
}
