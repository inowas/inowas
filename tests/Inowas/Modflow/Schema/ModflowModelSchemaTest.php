<?php

declare(strict_types=1);

namespace Tests\Inowas\Modflow\Schema;

use League\JsonGuard\Validator;
use League\JsonReference\Dereferencer;
use PHPUnit_Framework_TestCase as BaseTestCase;

class ModflowModelSchemaTest extends BaseTestCase
{
    public function providerModel()
    {
        $path = __DIR__.'/_files/';

        return [
            [file_get_contents($path . 'modflowModel.json'), true]
        ];
    }

    /**
     * @dataProvider providerModel
     * @test
     * @param string $json
     * @param bool $expected
     */
    public function it_validates_model(string $json, bool $expected)
    {
        $jsonSchema = str_replace(
            'https://inowas.com/',
            'file://spec/',
            file_get_contents('spec/schema/modflow/modflowModel.json')
        );
        $dereferencer = Dereferencer::draft4();
        $schema = $dereferencer->dereference(json_decode($jsonSchema));

        $validator = new Validator(json_decode($json), $schema);

        $this->assertSame($expected, $validator->passes(), var_export($validator->errors(), true));
    }

}