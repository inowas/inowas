<?php

namespace AppBundle\Model;

use AppBundle\Exception\InvalidArgumentException;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ActiveCells
 * @package AppBundle\Model
 *
 * @JMS\ExclusionPolicy("none")
 */
class ActiveCells
{
    /**
     * @var
     * @JMS\Groups({"modelProperties"})
     */
    private $cells;

    private final function __construct(){}

    public static function fromArray(array $cells)
    {
        $instance = new self();

        if (!is_array($cells)){
            throw new \InvalidArgumentException(sprintf(
                'ActiveCells is supposed to be an two dimensional array, %s given',
                gettype($cells)
            ));
        }

        foreach ($cells as $cell){
            if (!is_array($cell)){
                throw new \InvalidArgumentException(sprintf(
                    'ActiveCells is supposed to be an two dimensional array, %s given',
                    gettype($cell)
                ));
            }
        }

        $instance->cells = $cells;
        return $instance;
    }

    public static function fromObject($obj)
    {
        $cells = array();
        foreach ($obj as $row => $cols){
            foreach ($cols as $col => $value){
                $cells[intval($row)][intval($col)] = $value;
            }
        }

        return self::fromArray($cells);
    }

    public static function fromJSON($json){
        $decodedJson = json_decode($json);

        if ($decodedJson == null){
            throw new InvalidArgumentException(sprintf(
                'Argument is supposed to be a valid JSON-String, %s given.',
                $json
            ));
        }

        if (is_array($decodedJson)){
            return self::fromArray($decodedJson);
        }

        if (is_object($decodedJson)){
            return self::fromObject($decodedJson);
        }

        return null;
    }

    public function toArray()
    {
        return $this->cells;
    }
}