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
        $html = '<div class="ss-gridfield-add-new-multi-class" style="position: relative;">';
        $html .= '<button type="button" class="btn btn-primary font-icon-plus btn__addnewmulticlass" data-icon="add" onclick="openBlockSelector()">' . $this->getTitle() . '</button>';
        
        // Add the popup HTML positioned relative to the button
        $html .= '<div id="block-selector-popup" class="block-selector-popup" style="display: none; position: absolute; top: 100%; left: 0; background: white; border: 1px solid #ccc; border-radius: 4px; padding: 15px; z-index: 1000; box-shadow: 0 4px 6px rgba(0,0,0,0.1); min-width: 300px; margin-top: 5px;">';
        $html .= '<div style="margin-bottom: 15px; font-weight: bold; color: #333;">Search blocks</div>';
        $html .= '<div style="margin-bottom: 15px;">';
        $html .= '<input type="text" id="block-search" placeholder="Search blocks..." style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">';
        $html .= '</div>';
        $html .= '<div id="block-list" style="max-height: 300px; overflow-y: auto;">';
        
        foreach ($classes as $class => $title) {
            $html .= '<div class="block-option" data-class="' . htmlspecialchars($class) . '" style="padding: 10px; border-bottom: 1px solid #eee; cursor: pointer; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor=\'#f5f5f5\'" onmouseout="this.style.backgroundColor=\'transparent\'" onclick="selectBlock(\'' . htmlspecialchars($class) . '\')">';
            $html .= '<span style="margin-right: 10px; color: #666;">📄</span>' . htmlspecialchars($title);
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '<div style="margin-top: 15px; text-align: right; border-top: 1px solid #eee; padding-top: 10px;">';
        $html .= '<button type="button" onclick="closeBlockSelector()" style="padding: 6px 12px; border: 1px solid #ddd; background: #f5f5f5; border-radius: 4px; cursor: pointer;">Cancel</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Add JavaScript for the popup functionality
        $html .= '<script>
        function openBlockSelector() {
            var popup = document.getElementById("block-selector-popup");
            popup.style.display = "block";
            // Focus on search input
            setTimeout(function() {
                document.getElementById("block-search").focus();
            }, 100);
        }
        
        function closeBlockSelector() {
            document.getElementById("block-selector-popup").style.display = "none";
            // Clear search
            document.getElementById("block-search").value = "";
            // Show all options
            var blockOptions = document.querySelectorAll(".block-option");
            blockOptions.forEach(function(option) {
                option.style.display = "block";
            });
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
        document.addEventListener("DOMContentLoaded", function() {
            var searchInput = document.getElementById("block-search");
            if (searchInput) {
                searchInput.addEventListener("input", function() {
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
            }
        });
        
        // Close popup when clicking outside
        document.addEventListener("click", function(event) {
            var popup = document.getElementById("block-selector-popup");
            var button = document.querySelector(".btn__addnewmulticlass");
            if (popup && button && !popup.contains(event.target) && !button.contains(event.target)) {
                closeBlockSelector();
            }
        });
        
        // Close popup when pressing Escape
        document.addEventListener("keydown", function(event) {
            if (event.key === "Escape") {
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