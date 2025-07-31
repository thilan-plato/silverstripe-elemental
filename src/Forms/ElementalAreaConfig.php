<?php

namespace DNADesign\Elemental\Forms;

use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;

class ElementalAreaConfig extends GridFieldConfig
{
    public function __construct()
    {
        parent::__construct();

        $this->addComponent(new GridFieldDeleteAction(false));
        
        // Use standard detail form but with custom item request class
        $detailForm = new GridFieldDetailForm(null, false, false);
        $detailForm->setItemRequestClass(ElementalGridFieldDetailForm_ItemRequest::class);
        $this->addComponent($detailForm);

        $this->extend('updateConfig');
    }
}
