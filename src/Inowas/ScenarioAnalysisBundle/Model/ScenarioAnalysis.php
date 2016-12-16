<?php

namespace Inowas\ScenarioAnalysisBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ramsey\Uuid\Uuid;

class ScenarioAnalysis
{
    /**
     * @var Uuid
     */
    protected $id;

    /**
     * @var Uuid
     */
    protected $userId;

    /**
     * @var ArrayCollection
     */
    protected $scenarios;

    /**
     * ScenarioAnalysis constructor.
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    /**
     * @return Uuid
     */
    public function getId(): Uuid
    {
        return $this->id;
    }

    /**
     * @param Uuid $id
     * @return ScenarioAnalysis
     */
    public function setId(Uuid $id): ScenarioAnalysis
    {
        $this->id = $id;
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
     * @return ScenarioAnalysis
     */
    public function setUserId(Uuid $userId): ScenarioAnalysis
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getScenarios(): ArrayCollection
    {
        return $this->scenarios;
    }

    /**
     * @param ArrayCollection $scenarios
     * @return ScenarioAnalysis
     */
    public function setScenarios(ArrayCollection $scenarios): ScenarioAnalysis
    {
        $this->scenarios = $scenarios;
        return $this;
    }

    /**
     * @param Scenario $scenario
     * @return $this
     */
    public function addScenario(Scenario $scenario){
        $this->scenarios[] = $scenario;
        return $this;
    }
}
