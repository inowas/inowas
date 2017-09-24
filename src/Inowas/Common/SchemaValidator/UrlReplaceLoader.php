<?php

namespace Inowas\Common\SchemaValidator;

use League\JsonReference\JsonDecoder\JsonDecoder;
use League\JsonReference\JsonDecoderInterface;
use League\JsonReference\LoaderInterface;

class UrlReplaceLoader implements LoaderInterface
{

    /**
     * @var JsonDecoderInterface
     */
    private $jsonDecoder;

    /**
     * @param JsonDecoderInterface $jsonDecoder
     */
    public function __construct(JsonDecoderInterface $jsonDecoder = null)
    {
        $this->jsonDecoder = $jsonDecoder ?: new JsonDecoder();
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function load($path)
    {
        $path = str_replace(
            'inowas.com/',
            'spec/',
            $path);

        return $this->jsonDecoder->decode(file_get_contents($path));
    }
}
