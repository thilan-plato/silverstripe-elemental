<?php

namespace DNADesign\Elemental\Forms;

use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;

/**
 * Custom GridFieldDetailForm_ItemRequest for elements to avoid versioning template errors
 */
class ElementalGridFieldDetailForm_ItemRequest extends GridFieldDetailForm_ItemRequest
{
    /**
     * Override getFormActions to provide simple actions without template rendering
     * 
     * @return FieldList
     */
    public function getFormActions()
    {
        $record = $this->getRecord();
        
        // If this is a BaseElement, provide simple actions without template rendering
        if ($record instanceof BaseElement) {
            $actions = FieldList::create();
            
            // Add basic save action
            $actions->push(
                FormAction::create('save', 'Save')
                    ->setUseButtonTag(true)
                    ->addExtraClass('btn btn-primary')
            );
            
            // Add cancel action
            $actions->push(
                FormAction::create('cancel', 'Cancel')
                    ->setUseButtonTag(true)
                    ->addExtraClass('btn btn-secondary')
            );
            
            return $actions;
        }
        
        // For non-BaseElement records, use parent method
        return parent::getFormActions();
    }
} 