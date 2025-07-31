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
 * Custom GridFieldAddNewMultiClass that creates a popup interface like the original
 */
class ElementalGridFieldAddNewMultiClass extends GridFieldAddNewMultiClass
{
    /**
     * Override getHTMLFragments to create a popup interface
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
        
        // Create a single "Add block" button that will trigger a popup
        $html = '<div class="ss-gridfield-add-new-multi-class">';
        $html .= '<button type="button" class="btn btn-primary font-icon-plus btn__addnewmulticlass" data-icon="add" onclick="openBlockSelector()">' . $this->getTitle() . '</button>';
        
        // Add the popup HTML
        $html .= '<div id="block-selector-popup" class="block-selector-popup" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border: 1px solid #ccc; border-radius: 4px; padding: 20px; z-index: 1000; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">';
        $html .= '<div style="margin-bottom: 15px;">';
        $html .= '<input type="text" id="block-search" placeholder="Search blocks" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">';
        $html .= '</div>';
        $html .= '<div id="block-list" style="max-height: 300px; overflow-y: auto;">';
        
        foreach ($classes as $class => $title) {
            $html .= '<div class="block-option" data-class="' . htmlspecialchars($class) . '" style="padding: 8px; border-bottom: 1px solid #eee; cursor: pointer;" onclick="selectBlock(\'' . htmlspecialchars($class) . '\')">';
            $html .= '<span style="margin-right: 10px;">📄</span>' . htmlspecialchars($title);
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '<div style="margin-top: 15px; text-align: right;">';
        $html .= '<button type="button" onclick="closeBlockSelector()" style="margin-right: 10px; padding: 6px 12px; border: 1px solid #ddd; background: #f5f5f5; border-radius: 4px;">Cancel</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Add JavaScript for the popup functionality
        $html .= '<script>
        function openBlockSelector() {
            document.getElementById("block-selector-popup").style.display = "block";
        }
        
        function closeBlockSelector() {
            document.getElementById("block-selector-popup").style.display = "none";
        }
        
        function selectBlock(className) {
            // Create a form and submit it to add the block
            var form = document.createElement("form");
            form.method = "POST";
            form.action = "' . $gridField->Link('add') . '";
            
            var input = document.createElement("input");
            input.type = "hidden";
            input.name = "ClassName";
            input.value = className;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
        
        // Search functionality
        document.getElementById("block-search").addEventListener("input", function() {
            var searchTerm = this.value.toLowerCase();
            var blockOptions = document.querySelectorAll(".block-option");
            
            blockOptions.forEach(function(option) {
                var title = option.textContent.toLowerCase();
                if (title.includes(searchTerm)) {
                    option.style.display = "block";
                } else {
                    option.style.display = "none";
                }
            });
        });
        
        // Close popup when clicking outside
        document.addEventListener("click", function(event) {
            var popup = document.getElementById("block-selector-popup");
            var button = document.querySelector(".btn__addnewmulticlass");
            if (!popup.contains(event.target) && !button.contains(event.target)) {
                closeBlockSelector();
            }
        });
        </script>';
        
        $html .= '</div>';
        
        return [
            $this->getFragment() => $html
        ];
    }
} 