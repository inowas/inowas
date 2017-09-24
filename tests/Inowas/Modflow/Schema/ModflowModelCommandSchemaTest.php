<?php

declare(strict_types=1);

namespace Tests\Inowas\Modflow\Schema;

use Inowas\Common\SchemaValidator\UrlReplaceLoader;
use League\JsonGuard\Validator;
use League\JsonReference\Dereferencer;
use PHPUnit_Framework_TestCase as BaseTestCase;

class ModflowModelCommandSchemaTest extends BaseTestCase
{
    public function providerModel()
    {
        return [
            [
                file_get_contents('spec/example/modflow/command/addChdBoundary.json'),
                file_get_contents('spec/schema/modflow/command/addBoundary.json'),
                true
            ],
            [
                file_get_contents('spec/example/modflow/command/updateBoundary.json'),
                file_get_contents('spec/schema/modflow/command/updateBoundary.json'),
                true
            ],
            [
                file_get_contents('spec/example/modflow/command/removeBoundary.json'),
                file_get_contents('spec/schema/modflow/command/removeBoundary.json'),
                true
            ],
            [
                file_get_contents('spec/example/modflow/command/createModflowModel.json'),
                file_get_contents('spec/schema/modflow/command/createModflowModel.json'),
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
    public function it_validates_create_model_command(string $json, string $schema, bool $expected)
    {
        $dereferencer = Dereferencer::draft4();
        $dereferencer->getLoaderManager()->registerLoader('https', new UrlReplaceLoader());
        $dereferencedSchema = $dereferencer->dereference(json_decode($schema));

        $validator = new Validator(json_decode($json), $dereferencedSchema);
        $this->assertSame($expected, $validator->passes(), var_export($validator->errors(), true));
    }
}
