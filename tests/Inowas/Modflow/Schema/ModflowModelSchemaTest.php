<?php

declare(strict_types=1);

namespace Tests\Inowas\Modflow\Schema;

use Inowas\Common\SchemaValidator\UrlReplaceLoader;
use League\JsonGuard\Validator;
use League\JsonReference\Dereferencer;
use PHPUnit_Framework_TestCase as BaseTestCase;

class ModflowModelSchemaTest extends BaseTestCase
{
    public function providerModel()
    {
        $path = __DIR__.'/_files/';

        return [
            [
                file_get_contents($path . 'modflowModel.json'),
                file_get_contents('spec/schema/modflow/modflowModel.json'),
                true
            ]
        ];
    }

    /**
     * @dataProvider providerModel
     * @test
     * @param string $json
     * @param string $schema
     * @param bool $expected
     */
    public function it_validates_model(string $json, string $schema, bool $expected)
    {
        $dereferencer = Dereferencer::draft4();
        $dereferencer->getLoaderManager()->registerLoader('https', new UrlReplaceLoader());
        $dereferencedSchema = $dereferencer->dereference(json_decode($schema));

        $payload = json_decode($json, true);
        $validator = new Validator(json_decode(json_encode($payload), false), $dereferencedSchema);
        $this->assertSame($expected, $validator->passes(), var_export($validator->errors(), true));
    }
}
