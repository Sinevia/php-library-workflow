<?php
// ========================================================================= //
// SINEVIA PUBLIC                                        http://sinevia.com  //
// ------------------------------------------------------------------------- //
// COPYRIGHT (c) 2008-2018 Sinevia Ltd                   All rights reserved //
// ------------------------------------------------------------------------- //
// LICENCE: All information contained herein is, and remains, property of    //
// Sinevia Ltd at all times.  Any intellectual and technical concepts        //
// are proprietary to Sinevia Ltd and may be covered by existing patents,    //
// patents in process, and are protected by trade secret or copyright law.   //
// Dissemination or reproduction of this information is strictly forbidden   //
// unless prior written permission is obtained from Sinevia Ltd per domain.  //
//===========================================================================//
    
namespace Sinevia\Workflow;

class Step {

    public $name = null;
    public $type = "normal";
    public $title = "";
    public $description = "";
    public $responsible = "Admin";

    function __construct($name) {
        $this->name = $name;
    }

    function getActionLink() {
        return action($this->responsible . '\ApplicationController@get' . $this->name);
    }

}
