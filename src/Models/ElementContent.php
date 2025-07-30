<?php

namespace DNADesign\Elemental\Models;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\ORM\FieldType\DBField;

/**
 * @property string $HTML
 */
class ElementContent extends BaseElement
{
    private static $icon = 'font-icon-block-content';

    private static $db = [
        'HTML' => 'HTMLText'
    ];

    private static $table_name = 'ElementContent';

    private static $singular_name = 'content block';

    private static $plural_name = 'content blocks';

    /**
     * @deprecated 5.4.0 use class_description instead.
     */
    private static $description = 'HTML text block';

    private static $class_description = 'HTML text block';

    /**
     * Re-title the HTML field to Content
     *
     * {@inheritDoc}
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            /** @var HTMLEditorField $editorField */
            $editorField = $fields->fieldByName('Root.Main.HTML');
            $editorField->setTitle(_t(__CLASS__ . '.ContentLabel', 'Content'));
        });

        return parent::getCMSFields();
    }

    public function getSummary()
    {
        error_log('ElementContent getSummary called for ID: ' . $this->ID);
        error_log('HTML content: ' . ($this->HTML ? 'has content' : 'no content'));
        
        $summary = DBField::create_field('HTMLText', $this->HTML)->Summary(20);
        error_log('Generated summary: ' . $summary);
        
        return $summary;
    }

    public function getType()
    {
        return _t(__CLASS__ . '.BlockType', 'Content');
    }
}
