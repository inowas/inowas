<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\Uuid;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

/**
 * ModflowCalculation
 *
 * @ORM\Table(name="modflow_calculation")
 * @ORM\Entity()
 * @JMS\ExclusionPolicy("none")
 */
class ModflowCalculation
{

    const STATE_IN_QUEUE = 0;
    const STATE_RUNNING = 1;
    const STATE_FINISHED_SUCCESSFUL = 11;
    const STATE_FINISHED_WITH_ERRORS = 12;

    /**
     * @var Uuid
     * @ORM\Id
     * @ORM\Column(name="id", type="uuid", unique=true)
     * @JMS\Exclude()
     */
    private $id;

    /**
     * @var Uuid
     * @ORM\Column(name="process_id", type="uuid", nullable=true)
     * @JMS\Type("string")
     */
    private $processId;

    /**
     * @var Uuid
     * @ORM\Column(name="model_id", type="uuid", nullable=true)
     * @JMS\Type("string")
     */
    private $modelId;

    /**
     * @var Uuid
     * @ORM\Column(name="user_id", type="uuid", nullable=true)
     * @JMS\Type("string")
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="base_url", type="string", length=255, nullable=true)
     * @JMS\Type("string")
     */
    private $baseUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="data_folder", type="string", length=255, nullable=true)
     * @JMS\Type("string")
     */
    private $dataFolder;

    /**
     * @var integer $numberOfValues
     *
     * @ORM\Column(name="state", type="integer")
     * @JMS\Type("integer")
     */
    private $state = self::STATE_IN_QUEUE;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_time_add_to_queue", type="datetime", nullable=true)
     */
    private $dateTimeAddToQueue;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_time_start", type="datetime", nullable=true)
     */
    private $dateTimeStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_time_end", type="datetime", nullable=true)
     */
    private $dateTimeEnd;

    /**
     * @var string
     *
     * @ORM\Column(name="output", type="text", nullable=true)
     * @JMS\Type("string")
     */
    private $output;

    /**
     * @var string
     *
     * @ORM\Column(name="error_output", type="text", nullable=true)
     * @JMS\Type("string")
     */
    private $errorOutput;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->state = self::STATE_IN_QUEUE;
        $this->dateTimeAddToQueue = new \DateTime();
    }

    /**
     * @return Uuid
     */
    public function getId(): Uuid
    {
        return $this->id;
    }

    /**
     * @return Uuid
     */
    public function getProcessId(): Uuid
    {
        return $this->processId;
    }

    /**
     * @param Uuid $processId
     * @return ModflowCalculation
     */
    public function setProcessId(Uuid $processId): ModflowCalculation
    {
        $this->processId = $processId;
        return $this;
    }

    /**
     * @return Uuid
     */
    public function getModelId(): Uuid
    {
        return $this->modelId;
    }

    /**
     * @param Uuid $modelId
     * @return ModflowCalculation
     */
    public function setModelId(Uuid $modelId): ModflowCalculation
    {
        $this->modelId = $modelId;
        return $this;
    }

    /**
     * @return Uuid
     */
    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    /**
     * @param Uuid $userId
     * @return ModflowCalculation
     */
    public function setUserId(Uuid $userId): ModflowCalculation
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
     * @return ModflowCalculation
     */
    public function setBaseUrl(string $baseUrl): ModflowCalculation
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getDataFolder(): string
    {
        return $this->dataFolder;
    }

    /**
     * @param string $dataFolder
     * @return ModflowCalculation
     */
    public function setDataFolder(string $dataFolder): ModflowCalculation
    {
        $this->dataFolder = $dataFolder;
        return $this;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @param int $state
     * @return ModflowCalculation
     */
    public function setState(int $state): ModflowCalculation
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateTimeAddToQueue(): \DateTime
    {
        return $this->dateTimeAddToQueue;
    }

    /**
     * @param \DateTime $dateTimeAddToQueue
     * @return ModflowCalculation
     */
    public function setDateTimeAddToQueue(\DateTime $dateTimeAddToQueue): ModflowCalculation
    {
        $this->dateTimeAddToQueue = $dateTimeAddToQueue;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateTimeStart(): \DateTime
    {
        return $this->dateTimeStart;
    }

    /**
     * @param \DateTime $dateTimeStart
     * @return ModflowCalculation
     */
    public function setDateTimeStart(\DateTime $dateTimeStart): ModflowCalculation
    {
        $this->dateTimeStart = $dateTimeStart;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateTimeEnd(): \DateTime
    {
        return $this->dateTimeEnd;
    }

    /**
     * @param \DateTime $dateTimeEnd
     * @return ModflowCalculation
     */
    public function setDateTimeEnd(\DateTime $dateTimeEnd): ModflowCalculation
    {
        $this->dateTimeEnd = $dateTimeEnd;
        return $this;
    }

    /**
     * @return string
     */
    public function getOutput(): string
    {
        return $this->output;
    }

    /**
     * @param string $output
     * @return ModflowCalculation
     */
    public function setOutput(string $output): ModflowCalculation
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @return string
     */
    public function getErrorOutput(): string
    {
        return $this->errorOutput;
    }

    /**
     * @param string $errorOutput
     * @return ModflowCalculation
     */
    public function setErrorOutput(string $errorOutput): ModflowCalculation
    {
        $this->errorOutput = $errorOutput;
        return $this;
    }

    public function getRenderedOutput(){

        $input = array("\n");
        $output = array('<br>');
        return str_replace($input, $output, $this->output);
    }


}
