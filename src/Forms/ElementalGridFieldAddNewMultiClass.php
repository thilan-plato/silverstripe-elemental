<?php

namespace DNADesign\Elemental\Forms;

use Symbiote\GridFieldExtensions\GridFieldAddNewMultiClass;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_URLHandler;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;

/**
 * Custom GridFieldAddNewMultiClass that handles template rendering errors
 */
class ElementalGridFieldAddNewMultiClass extends GridFieldAddNewMultiClass
{
    /**
     * Override getHTMLFragments to handle template rendering errors
     * 
     * @param GridField $gridField
     * @return array
     */
    public function getHTMLFragments($gridField)
    {
        try {
            // Try to use parent method first
            return parent::getHTMLFragments($gridField);
        } catch (\Exception $e) {
            // If template rendering fails, provide a simple fallback
            error_log('GridFieldAddNewMultiClass template error: ' . $e->getMessage());
            
            $classes = $this->getClasses($gridField);
            if (empty($classes)) {
                return [];
            }
            
            // Create a simple dropdown without template rendering
            $dropdown = DropdownField::create(
                'ClassName',
                'Select block type',
                $classes
            );
            
            $button = FormAction::create('add', 'Add block')
                ->setUseButtonTag(true)
                ->addExtraClass('btn btn-primary font-icon-plus');
            
            $html = '<div class="ss-gridfield-add-new-multi-class">';
            $html .= $dropdown->FieldHolder();
            $html .= $button->FieldHolder();
            $html .= '</div>';
            
            return [
                $this->getFragment() => $html
            ];
        }
    }
} 