<?php

declare(strict_types=1);

namespace tests\Inowas\ModflowCalculation\Model;

use Inowas\ModflowModel\Model\AMQP\ReadDataResponse;

class FlopyReadTimeseriesResponseTest extends \PHPUnit_Framework_TestCase
{

    public function test_from_json_with_valid_response(): void
    {
        $json = '{
           "status_code": 200,
           "id": "123-test-456",
           "type":"flopy_read_data",
           "version":"3.2.6",
           "request":{
              "timeseries": {
                 "type": "head",
                 "layer": 0,
                 "row": 12,
                 "column": 23
              }
           },
           "response": [
              [  1.00000000e+00,   9.27655041e-01],
              [  2.00000000e+00,   7.99995363e-01],
              [  3.00000000e+00,   6.30003035e-01],
              [  1.09200000e+03,  -8.87391891e+01],
              [  1.09300000e+03,  -8.89029694e+01]
           ]
        }';


        $response = ReadDataResponse::fromJson($json);
        $this->assertTrue($response->statusCode()->ok());
        $this->assertEquals(200, $response->statusCode()->toInt());

        $expectedData = array(
            1 => 9.27655041e-01,
            2 => 7.99995363e-01,
            3 => 6.30003035e-01,
            1092 => -8.87391891e+01,
            1093 => -8.89029694e+01
        );

        $this->assertEquals($expectedData, $response->data());
    }
}
