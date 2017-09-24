<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Infrastructure\Projection;

/**
 * Class Table
 */
final class Table
{
    const CALCULATIONS   = 'mf_projection_calculations';
    const BOUNDARIES = 'mf_projection_boundaries';
    const MODFLOWMODELS = 'mf_projection_modflowmodels';
    const MODFLOWMODEL_HASH = 'mf_projection_modflowmodel_hash';
    const PACKAGES_HASH = 'mf_projection_packages_hash';
    const SOILMODELS = 'mf_projection_soilmodels';
    const SOILMODEL_LAYERS_LIST = 'mf_projection_soilmodel_layers';
}
