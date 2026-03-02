<?php
if (!defined('ABSPATH')) { exit; }
if (!class_exists('VBOCheckinPaxfieldType')) {
    abstract class VBOCheckinPaxfieldType {
        protected $field;
        public function __construct($field) { $this->field = $field; }
        protected function getFieldIdAttr()    { return esc_attr($this->field->getId()); }
        protected function getFieldNameAttr()  { return esc_attr($this->field->getName()); }
        protected function getFieldValueAttr() { return esc_attr($this->field->getValue()); }
    }
}

final class VBOCheckinPaxfieldTypeCustomHeading extends VBOCheckinPaxfieldType {
    public function render() {
        $label = esc_html($this->field->getLabel());
        return '<h3 class="vbo-cid-section">'.$label.'</h3>';
    }
}
