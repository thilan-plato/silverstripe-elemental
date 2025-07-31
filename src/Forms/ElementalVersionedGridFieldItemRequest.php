<?php

namespace DNADesign\Elemental\Forms;

use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Versioned\VersionedGridFieldItemRequest;

/**
 * Custom VersionedGridFieldItemRequest for elements to handle template rendering errors
 */
class ElementalVersionedGridFieldItemRequest extends VersionedGridFieldItemRequest
{
    /**
     * Override getFormActions to prevent template rendering errors
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
            
            // Add publish action if versioned
            if ($record->hasExtension('SilverStripe\Versioned\Versioned')) {
                $actions->push(
                    FormAction::create('publish', 'Publish')
                        ->setUseButtonTag(true)
                        ->addExtraClass('btn btn-success')
                );
            }
            
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

    /**
     * Override addVersionedButtons to prevent template rendering errors
     * 
     * @param BaseElement $record
     * @param FieldList $actions
     */
    protected function addVersionedButtons($record, $actions)
    {
        // Only proceed if this is a BaseElement
        if (!$record instanceof BaseElement) {
            return parent::addVersionedButtons($record, $actions);
        }

        try {
            // Try to add versioned buttons normally
            return parent::addVersionedButtons($record, $actions);
        } catch (\Exception $e) {
            // If template rendering fails, log the error but don't break the form
            error_log('ElementalVersionedGridFieldItemRequest template error: ' . $e->getMessage());
            
            // Add simple fallback buttons instead
            $actions->push(
                FormAction::create('save', 'Save')
                    ->setUseButtonTag(true)
                    ->addExtraClass('btn btn-primary')
            );
            
            if ($record->hasExtension('SilverStripe\Versioned\Versioned')) {
                $actions->push(
                    FormAction::create('publish', 'Publish')
                        ->setUseButtonTag(true)
                        ->addExtraClass('btn btn-success')
                );
            }
        }
    }
} 