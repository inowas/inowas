<?php

namespace Inowas\ModflowBundle\Model\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonArrayType;
use Inowas\ModflowBundle\Model\GridSize;

/**
 * Type that maps an SQL VARCHAR to a PHP string.
 *
 * @since 2.0
 */
class GridSizeType extends JsonArrayType
{
    const NAME = 'grid_size';

     /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        /** @var GridSize $value */
        $gs = array();
        $gs['n_x'] = $value->getNX();
        $gs['n_y'] = $value->getNY();
        return json_encode($gs);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = (is_resource($value)) ? stream_get_contents($value) : $value;
        $gs = json_decode($value, true);
        return new GridSize($gs['n_x'], $gs['n_y']);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}