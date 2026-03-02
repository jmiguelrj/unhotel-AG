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

final class VBOCheckinPaxfieldTypeCustomSelect extends VBOCheckinPaxfieldType {
    public function render() {
        $attrs = $this->field->getAttributes();
        $id   = $this->getFieldIdAttr();
        $name = $this->getFieldNameAttr();
        $val  = $this->getFieldValueAttr();

        $options = array();
        if (isset($attrs['options']) && is_array($attrs['options'])) {
            $options = $attrs['options'];
        }

        $placeholder = isset($attrs['placeholder']) ? esc_attr($attrs['placeholder']) : '';
        $hint = isset($attrs['hint']) ? esc_html($attrs['hint']) : '';

        $out = '<select id="'.$id.'" name="'.$name.'" class="vbo-cid-input vbo-cid-select">';
        $out .= '<option value="">' . esc_html($placeholder) . '</option>';
        foreach ($options as $k => $label) {
            $sel = ((string)$val === (string)$k) ? ' selected' : '';
            $out .= '<option value="'.esc_attr($k).'"'.$sel.'>'.esc_html($label).'</option>';
        }
        $out .= '</select>';
        if ($hint !== '') $out .= '<small class="vbo-cid-hint">'.$hint.'</small>';
        return $out;
    }
}
