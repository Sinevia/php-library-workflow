<?php

namespace Sinevia\Workflow;

class Step {

    public $name = null;
    public $type = "normal";
    public $title = "";
    public $description = "";
    public $resonsible = "Admin";

    function __construct($name) {
        $this->name = $name;
    }

    function getActionLink() {
        return action($this->responsible . '\ApplicationController@get' . $this->name);
    }

}
