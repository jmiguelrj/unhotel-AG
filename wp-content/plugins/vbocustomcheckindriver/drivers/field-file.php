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

final class VBOCheckinPaxfieldTypeCustomFile extends VBOCheckinPaxfieldType {
    public function render() {
        $attrs = $this->field->getAttributes();
        $accept = isset($attrs['accept']) ? esc_attr($attrs['accept']) : '';
        $hint = isset($attrs['hint']) ? esc_html($attrs['hint']) : '';
        $id   = $this->getFieldIdAttr();
        $name = $this->getFieldNameAttr();
        $out = '<input type="file" id="'.$id.'" name="'.$name.'" accept="'.$accept.'" class="vbo-cid-input vbo-cid-file" />';
        if ($hint !== '') $out .= '<small class="vbo-cid-hint">'.$hint.'</small>';
        return $out;
    }
}
