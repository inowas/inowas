<?php

namespace Inowas\Common\SchemaValidator;

use League\JsonReference\Dereferencer;

class DereferencerFactory
{
    public static function create(): Dereferencer
    {
        $dereferencer = Dereferencer::draft4();
        $dereferencer->getLoaderManager()->registerLoader('https', new UrlReplaceLoader());
        return $dereferencer;
    }
}
