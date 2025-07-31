<?php

namespace DNADesign\Elemental\Extensions;

use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Core\Extension;
use SilverStripe\Versioned\VersionedGridFieldItemRequest;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\Forms\FieldList;

/**
 * @extends Extension<VersionedGridFieldItemRequest>
 */
class GridFieldDetailFormItemRequestExtension extends Extension
{
    public function updateBreadcrumbs($crumbs)
    {
        $record = $this->owner->getRecord();

        if ($record instanceof BaseElement) {
            $last = $crumbs->Last();

            $last->Title = DBField::create_field('HTMLVarchar', sprintf(
                "%s <small>(%s)</small>",
                DBField::create_field('Varchar', $last->Title)->XML(),
                $record->getType()
            ));
        }
    }

    /**
     * Override addVersionedButtons to prevent template rendering errors
     * 
     * @param BaseElement $record
     * @param FieldList $actions
     */
    public function addVersionedButtons($record, $actions)
    {
        // Only proceed if this is a BaseElement
        if (!$record instanceof BaseElement) {
            return;
        }

        try {
            // Try to add versioned buttons normally
            $this->owner->addVersionedButtons($record, $actions);
        } catch (\Exception $e) {
            // If template rendering fails, log the error but don't break the form
            error_log('VersionedGridFieldItemRequest template error: ' . $e->getMessage());
            
            // Add a simple fallback button instead
            $actions->push(
                \SilverStripe\Forms\FormAction::create('save', 'Save')
                    ->setUseButtonTag(true)
                    ->addExtraClass('btn btn-primary')
            );
        }
    }
}
