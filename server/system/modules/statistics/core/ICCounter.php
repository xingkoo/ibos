<?php

abstract class ICCounter extends CApplicationComponent
{
    abstract public function getID();

    abstract public function getCount();
}
