<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\AMQP;

use Inowas\Common\Status\StatusCode;

class ReadDataResponse
{
    const REQUEST_TYPE_LAYER_DATA = 'layerdata';
    const REQUEST_TYPE_TIME_SERIES = 'timeseries';

    const DATA_TYPE_HEAD = 'head';
    const DATA_TYPE_DRAWDOWN = 'drawdown';
    const DATA_TYPE_budget = 'budget';

    const VERSION = '3.2.6';

    protected $data = [];

    /** @var  StatusCode */
    protected $statusCode;

    public static function fromJson(string $json): ReadDataResponse
    {
        $obj = json_decode($json);
        $self = new self();
        $self->statusCode = StatusCode::fromInt((int)$obj->status_code);

        if ($self->statusCode->ok()){
            if (property_exists($obj->request,'timeseries')) {
                $timeSeries = [];
                $data = $obj->response;
                foreach ($data as $dataSet){
                    $key = (int)$dataSet[0];
                    $value = (float)$dataSet[1];
                    $timeSeries[$key] = $value;
                }

                $self->data = $timeSeries;
                return $self;
            }

            $self->data = $obj->response;
        }

        return $self;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function statusCode(): StatusCode
    {
        return $this->statusCode;
    }
}
