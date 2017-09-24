<?php

declare(strict_types=1);

namespace Tests\Inowas\Modflow\Schema;

use Inowas\Common\SchemaValidator\UrlReplaceLoader;
use League\JsonGuard\Validator;
use League\JsonReference\Dereferencer;
use PHPUnit_Framework_TestCase as BaseTestCase;

class BoundarySchemaTest extends BaseTestCase
{
    public function providerWellType()
    {
        return [
            ['puw', true],
            ['invalid', false],
        ];
    }

    /**
     * @dataProvider providerWellType
     * @test
     * @param string $type
     * @param bool $expected
     */
    public function it_validates_well_type(string $type, bool $expected)
    {
        $dereferencer = Dereferencer::draft4();
        $schema = $dereferencer->dereference('file://spec/schema/modflow/boundary/wellType.json');
        $data = json_encode(['well_type' => $type]);
        $validator = new Validator(json_decode($data), $schema);
        $this->assertSame($expected, $validator->passes(), var_export($validator->errors(), true));
    }

    public function providerWell()
    {
        $path = 'spec/example/modflow/boundary/';

        return [
            [file_get_contents($path . 'wellBoundary.json'), true],
//            [file_get_contents($path . 'well_invalid.json'), false],
        ];
    }

    /**
     * @dataProvider providerWell
     * @test
     * @param string $json
     * @param bool $expected
     */
    public function it_validates_well(string $json, bool $expected)
    {
        $jsonSchema = str_replace(
            'https://inowas.com/',
            'file://spec/',
            file_get_contents('spec/schema/modflow/boundary/wellBoundary.json')
        );
        $dereferencer = Dereferencer::draft4();
        $schema = $dereferencer->dereference(json_decode($jsonSchema));

        $validator = new Validator(json_decode($json), $schema);

        $this->assertSame($expected, $validator->passes(), var_export($validator->errors(), true));
    }

    public function providerBoundary()
    {
        return [
            [
                file_get_contents('spec/example/modflow/boundary/constantHeadBoundary.json'),
                file_get_contents('spec/schema/modflow/boundary/constantHeadBoundary.json'),
                true
            ],
            [
                file_get_contents('spec/example/modflow/boundary/generalHeadBoundary.json'),
                file_get_contents('spec/schema/modflow/boundary/generalHeadBoundary.json'),
                true
            ],
            [
                file_get_contents('spec/example/modflow/boundary/rechargeBoundary.json'),
                file_get_contents('spec/schema/modflow/boundary/rechargeBoundary.json'),
                true
            ],
            [
                file_get_contents('spec/example/modflow/boundary/riverBoundary.json'),
                file_get_contents('spec/schema/modflow/boundary/riverBoundary.json'),
                true
            ],
            [
                file_get_contents('spec/example/modflow/boundary/wellBoundary.json'),
                file_get_contents('spec/schema/modflow/boundary/wellBoundary.json'),
                true
            ],
            [
                file_get_contents('spec/example/modflow/boundary/constantHeadBoundary.json'),
                file_get_contents('spec/schema/modflow/boundary/boundary.json'),
                true
            ],
            [
                file_get_contents('spec/example/modflow/boundary/generalHeadBoundary.json'),
                file_get_contents('spec/schema/modflow/boundary/boundary.json'),
                true
            ],
            [
                file_get_contents('spec/example/modflow/boundary/rechargeBoundary.json'),
                file_get_contents('spec/schema/modflow/boundary/boundary.json'),
                true
            ],
            [
                file_get_contents('spec/example/modflow/boundary/riverBoundary.json'),
                file_get_contents('spec/schema/modflow/boundary/boundary.json'),
                true
            ],
            [
                file_get_contents('spec/example/modflow/boundary/wellBoundary.json'),
                file_get_contents('spec/schema/modflow/boundary/boundary.json'),
                true
            ],

        ];
    }

    /**
     * @dataProvider providerBoundary
     * @test
     * @param string $json
     * @param string $schema
     * @param bool $expected
     */
    public function it_validates_boundaries(string $json, string $schema, bool $expected)
    {
        $dereferencer = Dereferencer::draft4();
        $dereferencer->getLoaderManager()->registerLoader('https', new UrlReplaceLoader());
        $dereferencedSchema = $dereferencer->dereference(json_decode($schema));

        $payload = json_decode($json, true);
        $validator = new Validator(json_decode(json_encode($payload), false), $dereferencedSchema);
        $this->assertSame($expected, $validator->passes(), var_export($validator->errors(), true));
    }

}
