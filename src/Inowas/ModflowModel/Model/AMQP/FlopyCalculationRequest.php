<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\AMQP;

use Inowas\Common\Id\CalculationId;
use Inowas\Common\Id\ModflowId;
use Inowas\ModflowModel\Model\ModflowPackages;

class FlopyCalculationRequest implements \JsonSerializable
{
    private $author = '';
    private $project = '';

    /** @var  CalculationId */
    private $calculationId;

    /** @var  ModflowId */
    private $modelId;

    private $type = 'flopy_calculation';

    /** @var  ModflowPackages */
    private $packages;

    public static function fromParams(ModflowId $modelId, CalculationId $calculationId, ModflowPackages $packages): FlopyCalculationRequest
    {
        $self = new self();
        $self->calculationId = $calculationId->toString();
        $self->modelId = $modelId->toString();
        $self->packages = $packages;
        return $self;
    }

    public function jsonSerialize(): array
    {
        $data = $this->packages->toArray();
        $data['write_input'] = true;
        $data['run_model'] = true;

        return array(
            'author' => $this->author,
            'project' => $this->project,
            'calculation_id' => $this->calculationId,
            'model_id' => $this->modelId,
            'type' => $this->type,
            'version' => $this->packages->version(),
            'data' => $data
        );
    }
}
