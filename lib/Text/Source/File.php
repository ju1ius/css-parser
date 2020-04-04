<?php declare(strict_types=1);

namespace ju1ius\Text\Source;

/**
 * A source file
 */
class File extends Bytes
{
    protected string $url;

    /**
     * @param string $url
     * @param string $contents
     * @param string $encoding
     **/
    public function __construct(string $url, string $contents, string $encoding = "utf-8")
    {
        $this->url = $url;
        parent::__construct($contents, $encoding);
    }

    /**
     * Returns the url of the source file
     **/
    public function getUrl(): string
    {
        return $this->url;
    }
}
