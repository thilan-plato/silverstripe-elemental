<?php

namespace DNADesign\Elemental\Forms;

use DNADesign\Elemental\Controllers\ElementalAreaController;
use DNADesign\Elemental\Models\BaseElement;
use DNADesign\Elemental\Models\ElementalArea;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\TabSet;
use SilverStripe\ORM\DataObjectInterface;
use Symbiote\GridFieldExtensions\GridFieldAddNewMultiClass;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;

class ElementalAreaField extends GridField
{
    /**
     * @var ElementalArea $area
     */
    protected $area;

    /**
     * @var array $type
     */
    protected $types = [];

    /**
     * @var null
     */
    protected $inputType = null;

    protected $modelClassName = BaseElement::class;

    /**
     * @var array $recursionGuards
     */
    protected $recursionGuards = [];

    /**
     * @param string $name
     * @param ElementalArea $area
     * @param string[] $blockTypes
     */
    public function __construct($name, ElementalArea $area, array $blockTypes)
    {
        $this->setTypes($blockTypes);

        $config = new ElementalAreaConfig();

        // Debug: Log what block types are being passed
        error_log('ElementalAreaField constructor - block types: ' . print_r($blockTypes, true));

        // Temporarily disable GridFieldAddNewMultiClass to avoid template errors
        // TODO: Re-enable when template issues are resolved
        error_log('Using simple add button to avoid template errors');
        $config->addComponent(new \SilverStripe\Forms\GridField\GridFieldAddNewButton());
        
        /*
        // Re-enable GridFieldAddNewMultiClass to show block selection dropdown
        if (!empty($blockTypes)) {
            try {
                // Use our custom component that handles template rendering errors
                error_log('Attempting to create ElementalGridFieldAddNewMultiClass');
                if (class_exists(ElementalGridFieldAddNewMultiClass::class)) {
                    error_log('ElementalGridFieldAddNewMultiClass class exists');
                    $adder = Injector::inst()->create(ElementalGridFieldAddNewMultiClass::class);
                    error_log('ElementalGridFieldAddNewMultiClass instance created: ' . get_class($adder));
                    $adder->setClasses($blockTypes);
                    $config->addComponent($adder);
                    error_log('ElementalGridFieldAddNewMultiClass component added successfully');
                } else {
                    error_log('ElementalGridFieldAddNewMultiClass class not found, falling back to original');
                    $adder = Injector::inst()->create(GridFieldAddNewMultiClass::class);
                    $adder->setClasses($blockTypes);
                    $config->addComponent($adder);
                }
            } catch (\Exception $e) {
                error_log('ElementalGridFieldAddNewMultiClass not available: ' . $e->getMessage());
                $config->addComponent(new \SilverStripe\Forms\GridField\GridFieldAddNewButton());
            }
        } else {
            error_log('No block types provided, using simple add button');
            $config->addComponent(new \SilverStripe\Forms\GridField\GridFieldAddNewButton());
        }
        */

        // By default, no need for a title on the editor. If there is more than one area then use `setTitle` to describe
        parent::__construct($name, '', $area->Elements(), $config);
        $this->area = $area;

        $this->addExtraClass('element-editor__container no-change-track');
    }

    /**
     * @param array $types
     *
     * @return $this
     */
    public function setTypes($types)
    {
        $this->types = $types;

        return $this;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        $types = $this->types;

        $this->extend('updateGetTypes', $types);

        return $types;
    }

    /**
     * @return ElementalArea
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Overloaded to skip GridField implementation - this is copied from FormField.
     *
     * @param array $properties
     * @return \SilverStripe\ORM\FieldType\DBHTMLText|string
     */
    public function FieldHolder($properties = array())
    {
        // Prevent infinite recursion during template rendering
        if (isset($this->recursionGuards['rendering_field_holder']) && $this->recursionGuards['rendering_field_holder']) {
            return parent::FieldHolder($properties);
        }
        
        $this->recursionGuards['rendering_field_holder'] = true;
        
        try {
            $context = $this;

            if (count($properties ?? [])) {
                // Prevent infinite recursion during customisation
                if (!isset($this->recursionGuards['customising_field_holder'])) {
                    $this->recursionGuards['customising_field_holder'] = true;
                    try {
                        $context = $this->customise($properties);
                    } finally {
                        $this->recursionGuards['customising_field_holder'] = false;
                    }
                }
            }

            // Prevent infinite recursion during template rendering
            if (!isset($this->recursionGuards['rendering_with_templates'])) {
                $this->recursionGuards['rendering_with_templates'] = true;
                try {
                    return $context->renderWith($this->getFieldHolderTemplates());
                } finally {
                    $this->recursionGuards['rendering_with_templates'] = false;
                }
            } else {
                return parent::FieldHolder($properties);
            }
        } finally {
            $this->recursionGuards['rendering_field_holder'] = false;
        }
    }

    public function getSchemaDataDefaults()
    {
        // Prevent infinite recursion during template compilation
        if (isset($this->recursionGuards['processing_schema_data']) && $this->recursionGuards['processing_schema_data']) {
            return parent::getSchemaDataDefaults();
        }
        
        $this->recursionGuards['processing_schema_data'] = true;
        
        try {
            $schemaData = parent::getSchemaDataDefaults();

            $area = $this->getArea();
            $pageId = ($area && ($page = $area->getOwnerPage())) ? $page->ID : null;
            $schemaData['page-id'] = $pageId;
            $schemaData['elemental-area-id'] = $area ? (int) $area->ID : null;

            $allowedTypes = $this->getTypes();
            $schemaData['allowed-elements'] = array_keys($allowedTypes ?? []);

            return $schemaData;
        } finally {
            $this->recursionGuards['processing_schema_data'] = false;
        }
    }

    /**
     * A getter method that seems redundant in that it is a function that returns a function,
     * however the returned closure is used in an array map function to return a complete FieldList
     * representing a read only view of the element passed in (to the closure).
     *
     * @return callable
     */
    protected function getReadOnlyBlockReducer()
    {
        return function (BaseElement $element) {
            // Prevent infinite recursion when processing elements
            if (isset($this->recursionGuards['processing_element_' . $element->ID]) && $this->recursionGuards['processing_element_' . $element->ID]) {
                return FieldGroup::create()->setName('Element' . $element->ID);
            }
            
            $this->recursionGuards['processing_element_' . $element->ID] = true;
            
            try {
                $parentName = 'Element' . $element->ID;
                $elementFields = $element->getCMSFields();

                // Obtain highest impact fields for a summary (e.g. Title & Content)
                foreach ($elementFields as $field) {
                    if (is_object($field) && $field instanceof TabSet) {
                        // Assign the fields of the first Tab in the TabSet - most regularly 'Root.Main'
                        $elementFields = $field->FieldList()->first()->FieldList();
                        break;
                    }
                }

                // Set values (before names don't match anymore)
                $elementFields->setValues($element->getQueriedDatabaseFields());

                // Combine into an appropriately named group
                $elementGroup = FieldGroup::create($elementFields);
                $elementGroup->setForm($this->getForm());
                $elementGroup->setName($parentName);
                $elementGroup->addExtraClass('elemental-area__element--historic');

                // Also set the important data for the rendering Component
                $elementGroup->setSchemaData([
                    'data' => [
                        'ElementID' => $element->ID,
                        'ElementType' => $element->getType(),
                        'ElementIcon' => $element->config()->get('icon'),
                        'ElementTitle' => $element->Title,
                        'ElementEditLink' => Controller::join_links(
                            // Always get the edit link for the block directly, not the in-line edit form if supported
                            $element->CMSEditLink(true),
                            '#Root_History'
                        ),
                    ],
                ]);

                return $elementGroup;
            } finally {
                $this->recursionGuards['processing_element_' . $element->ID] = false;
            }
        };
    }

    /**
     * Provides a readonly representation of the GridField (superclass) Uses a reducer
     * {@see ElementalAreaField::getReadOnlyBlockReducer()} to fetch a read only representation of the listed class
     * {@see GridField::getModelClass()}
     *
     * @return CompositeField
     */
    public function performReadonlyTransformation()
    {
        /** @var CompositeField $readOnlyField */
        $readOnlyField = $this->castedCopy(CompositeField::class);
        $blockReducer = $this->getReadOnlyBlockReducer();
        $readOnlyField->setChildren(
            FieldList::create(array_map($blockReducer, $this->getArea()->Elements()->toArray() ?? []))
        );

        $readOnlyField = $readOnlyField->performReadonlyTransformation();

        // Ensure field names are unique between elements on parent form but only after transformations have been
        // performed
        /** @var FieldGroup $elementForm */
        foreach ($readOnlyField->getChildren() as $elementForm) {
            $parentName = $elementForm->getName();
            $elementForm->getChildren()->recursiveWalk(function (FormField $field) use ($parentName) {
                $field->setName($parentName . '_' . $field->getName());
            });
        }

        return $readOnlyField
            ->setReadOnly(true)
            ->setName($this->getName())
            ->addExtraClass('elemental-area--read-only');
    }

    public function setSubmittedValue($value, $data = null)
    {
        // Content comes through as a JSON encoded list through a hidden field.
        return $this->setValue(json_decode($value ?? '', true));
    }
}
