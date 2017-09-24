<?php

declare(strict_types=1);

namespace tests\Inowas\ModflowCalculation\Model;

use Inowas\ModflowModel\Model\AMQP\FlopyCalculationResponse;

class FlopyCalculationResponseTest extends \PHPUnit_Framework_TestCase
{

    public function test_from_json_with_valid_response(): void
    {
        $json = '{
            "status_code": "200", 
            "model_id": "baa36c98-c166-4c4a-92ea-04cf747c8702", 
            "calculation_id": "ahdguqwzeuqz89882", 
            "message": "testMessage", 
            "data": {
                "number_of_layers": 4, 
                "drawdowns": [], 
                "heads": [31.0, 59.0, 90.0, 120.0, 151.0, 181.0, 212.0, 243.0, 273.0, 304.0, 334.0, 365.0, 396.0, 424.0, 455.0, 485.0, 516.0, 546.0, 577.0, 608.0, 638.0, 669.0, 699.0, 730.0, 761.0, 789.0, 820.0, 850.0, 881.0, 911.0, 942.0, 973.0, 1003.0, 1034.0, 1064.0, 1094.0], 
                "budgets": [31.0, 59.0, 90.0, 120.0, 151.0, 181.0, 212.0, 243.0, 273.0, 304.0, 334.0, 365.0, 396.0, 424.0, 455.0, 485.0, 516.0, 546.0, 577.0, 608.0, 638.0, 669.0, 699.0, 730.0, 761.0, 789.0, 820.0, 850.0, 881.0, 911.0, 942.0, 973.0, 1003.0, 1034.0, 1064.0, 1094.0]
            }
        }';
        $response = FlopyCalculationResponse::fromJson($json);

        $this->assertTrue($response->statusCode()->ok());
        $this->assertEquals(200, $response->statusCode()->toInt());
        $this->assertEquals('baa36c98-c166-4c4a-92ea-04cf747c8702', $response->modelId()->toString());
        $this->assertEquals('ahdguqwzeuqz89882', $response->calculationId()->toString());
        $this->assertEquals('testMessage', $response->message());
        $this->assertEquals(4, $response->numberOfLayers());
        $this->assertTrue(is_array($response->heads()));
        $this->assertCount(36, $response->heads());
        $this->assertEquals(31, (int)$response->heads()[0]);
        $this->assertEquals(59, (int)$response->heads()[1]);
        $this->assertTrue(is_array($response->budgets()));
        $this->assertTrue(is_array($response->drawdowns()));
    }
}
