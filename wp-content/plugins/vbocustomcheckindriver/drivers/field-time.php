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

final class VBOCheckinPaxfieldTypeCustomTime extends VBOCheckinPaxfieldType {
    public function render() {
        $attrs = $this->field->getAttributes();
        if (!empty($attrs['main_guest_only']) && method_exists($this->field, 'getGuestNumber')) {
            if ((int)$this->field->getGuestNumber() > 1) { return ''; }
        }
        $placeholder = isset($attrs['placeholder']) ? esc_attr($attrs['placeholder']) : '';
        $hint = isset($attrs['hint']) ? esc_html($attrs['hint']) : '';
        $id   = $this->getFieldIdAttr();
        $name = $this->getFieldNameAttr();
        $val  = $this->getFieldValueAttr();
        $out = '<input type="time" id="'.$id.'" name="'.$name.'" value="'.$val.'" placeholder="'.$placeholder.'" class="vbo-cid-input vbo-cid-time" />';
        if ($hint !== '') $out .= '<small class="vbo-cid-hint">'.$hint.'</small>';
        return $out;
    }
}
