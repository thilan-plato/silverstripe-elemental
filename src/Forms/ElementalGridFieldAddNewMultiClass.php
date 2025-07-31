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
 * Custom GridFieldAddNewMultiClass that generates HTML directly without templates
 */
class ElementalGridFieldAddNewMultiClass extends GridFieldAddNewMultiClass
{
    /**
     * Override getHTMLFragments to generate HTML directly without templates
     * 
     * @param GridField $gridField
     * @return array
     */
    public function getHTMLFragments($gridField)
    {
        $classes = $this->getClasses($gridField);
        if (empty($classes)) {
            return [];
        }
        
        // Generate HTML directly without using templates
        $dropdown = DropdownField::create(
            'ClassName',
            'Select block type',
            $classes
        );
        
        $button = FormAction::create('add', 'Add block')
            ->setUseButtonTag(true)
            ->addExtraClass('btn btn-primary font-icon-plus');
        
        // Generate the HTML directly
        $html = '<div class="ss-gridfield-add-new-multi-class">';
        $html .= '<div class="field">';
        $html .= '<label class="left">Select block type</label>';
        $html .= '<div class="middleColumn">';
        $html .= '<select name="ClassName" class="dropdown">';
        foreach ($classes as $class => $title) {
            $html .= '<option value="' . htmlspecialchars($class) . '">' . htmlspecialchars($title) . '</option>';
        }
        $html .= '</select>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<a href="#" data-href="' . $gridField->Link('add') . '" data-add-multiclass class="btn btn-primary font-icon-plus btn__addnewmulticlass" data-icon="add">Add block</a>';
        $html .= '</div>';
        
        return [
            $this->getFragment() => $html
        ];
    }
} 