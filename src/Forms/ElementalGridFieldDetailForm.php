<?php

namespace DNADesign\Elemental\Forms;

use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;

/**
 * Custom GridFieldDetailForm for elements to avoid versioning template errors
 */
class ElementalGridFieldDetailForm extends GridFieldDetailForm
{
    /**
     * Override to use custom item request class for elements
     * 
     * @param mixed $gridField
     * @param mixed $request
     * @return GridFieldDetailForm_ItemRequest
     */
    public function handleItem($gridField, $request)
    {
        // Get the record from the request
        $record = $this->getRecordFromRequest($gridField, $request);
        
        // If this is a BaseElement, use our custom item request class
        if ($record instanceof BaseElement) {
            $itemRequestClass = ElementalGridFieldDetailForm_ItemRequest::class;
        } else {
            $itemRequestClass = $this->getItemRequestClass();
        }
        
        return Injector::inst()->create($itemRequestClass, $gridField, $this, $record, $this->getForm(), $request, $this->getName());
    }
} 