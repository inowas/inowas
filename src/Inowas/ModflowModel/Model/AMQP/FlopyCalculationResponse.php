<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\AMQP;

use Inowas\Common\Id\CalculationId;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Status\StatusCode;
use Inowas\ModflowModel\Model\Exception\ResponseNotValidException;

class FlopyCalculationResponse
{

    /** @var  StatusCode */
    protected $statusCode;

    /** @var CalculationId */
    protected $calculationId;

    /** @var ModflowId */
    protected $modelId;

    /** @var string */
    protected $message;

    /** @var  array */
    protected $budgets = [];

    /** @var  array */
    protected $drawdowns = [];

    /** @var  array */
    protected $heads = [];

    /** @var  int */
    protected $numberOfLayers = 1;

    public static function fromJson(string $json): FlopyCalculationResponse
    {
        $arr = json_decode($json, true);
        if (! is_array($arr)){
            throw ResponseNotValidException::withResponse($json);
        }
        return self::fromArray($arr);
    }

    public static function fromArray(array $arr): FlopyCalculationResponse
    {
        $self = new self();
        $self->statusCode = StatusCode::fromInt((int)$arr['status_code']);
        $self->modelId = ModflowId::fromString($arr['model_id']);
        $self->calculationId = CalculationId::fromString($arr['calculation_id']);
        $self->message = $arr['message'];

        if (array_key_exists('data', $arr)) {
            $data = $arr['data'];

            if (array_key_exists('budgets', $data)) {
                $self->budgets = $data['budgets'];
            }

            if (array_key_exists('drawdowns', $data)) {
                $self->drawdowns = $data['drawdowns'];
            }

            if (array_key_exists('heads', $data)) {
                $self->heads = $data['heads'];
            }

            if (array_key_exists('number_of_layers', $data)) {
                $self->numberOfLayers = $data['number_of_layers'];
            }
        }

        return $self;
    }

    public function toArray(): array
    {
        return array(
            'status_code' => $this->statusCode->toInt(),
            'model_id' => $this->modelId->toString(),
            'calculation_id' => $this->calculationId->toString(),
            'message' => $this->message,
            'data' => array(
                'budgets' => $this->budgets,
                'drawdowns' => $this->drawdowns,
                'heads' => $this->heads,
                'number_of_layers' => $this->numberOfLayers
            )
        );
    }

    public function statusCode(): StatusCode
    {
        return $this->statusCode;
    }

    public function modelId(): ModflowId
    {
        return $this->modelId;
    }

    public function calculationId(): CalculationId
    {
        return $this->calculationId;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function budgets(): array
    {
        return $this->budgets;
    }

    public function drawdowns(): array
    {
        return $this->drawdowns;
    }

    public function heads(): array
    {
        return $this->heads;
    }

    public function numberOfLayers(): int
    {
        return $this->numberOfLayers;
    }
}
