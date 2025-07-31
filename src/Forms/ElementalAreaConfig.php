<?php

namespace DNADesign\Elemental\Forms;

use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;

class ElementalAreaConfig extends GridFieldConfig
{
    public function __construct()
    {
        parent::__construct();

        $this->addComponent(new GridFieldDeleteAction(false));
        
        // Use custom detail form without versioning to avoid template errors
        $this->addComponent(new ElementalGridFieldDetailForm(null, false, false));

        $this->extend('updateConfig');
    }
}
