<?php

abstract class ICChart extends CComponent
{
    protected $counter;

    public function __construct(ICCounter $counter)
    {
        $this->setCounter($counter);
    }

    abstract public function getSeries();

    abstract public function getXaxis();

    abstract public function getYaxis();

    public function getCounter()
    {
        return $this->counter;
    }

    protected function setCounter(ICCounter $counter)
    {
        $this->counter = $counter;
    }
}
