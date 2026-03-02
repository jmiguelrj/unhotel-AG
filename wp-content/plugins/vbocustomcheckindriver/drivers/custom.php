<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Simplified custom Vik Booking data collector driver.
 */
if (!class_exists('VBOCheckinPaxfieldsCustom')) {

    class VBOCheckinPaxfieldsCustom {

        public $collector_id = 'custom';

        public function getName() {
            return 'Custom data collector';
        }

        // Include children on both precheckin and back-office.
        public function registerChildren($precheckin = false) {
            return true;
        }

        // Headings and field labels (plain strings).
        public function getLabels() {
            return array(
                'sec_guest'     => 'Guest details',
                'sec_id'        => 'Identity document',
                'sec_address'   => 'Address',
                'sec_arrival'   => 'Arrival & departure',
                'sec_notes'     => 'Notes',

                'first_name'    => 'First name',
                'last_name'     => 'Last name',
                'date_birth'    => 'Date of birth',
                'nationality'   => 'Nationality',
                'id_type'       => 'ID type',
                'id_number'     => 'ID number',
                'id_upload'     => 'Upload ID',
                'address'       => 'Address line',
                'city'          => 'City',
                'zip'           => 'Postcode',
                'country'       => 'Country',
                'est_arrival'   => 'Estimated arrival',
                'est_departure' => 'Estimated departure',
                'notes'         => 'Additional notes',
            );
        }

        // Field attributes and grouping with placeholders/hints.
        public function getAttributes() {
            $id_options = array(
                'RG'  => 'RG',
                'CNH' => 'CNH',
                'RNE' => 'RNE',
                'PASSPORT' => 'Passport',
                'MERCOSUR_DNI' => 'MERCOSUR DNI',
                'OTHER' => 'Other',
            );

            return array(
                'sec_guest'     => 'custom_heading',
                'sec_id'        => 'custom_heading',
                'sec_address'   => 'custom_heading',
                'sec_arrival'   => 'custom_heading',
                'sec_notes'     => 'custom_heading',

                'first_name' => array('type' => 'custom_text', 'placeholder' => 'As on the document'),
                'last_name'  => array('type' => 'custom_text', 'placeholder' => 'As on the document'),
                'date_birth' => array('type' => 'custom_date', 'placeholder' => 'DD/MM/YYYY'),
                'nationality'=> array('type' => 'custom_text', 'placeholder' => 'Type to select'),

                'id_type'    => array('type' => 'custom_select', 'options' => $id_options, 'placeholder' => 'Type to select'),
                'id_number'  => array('type' => 'custom_text', 'placeholder' => 'Numbers & letters only'),
                'id_upload'  => array('type' => 'custom_file', 'accept' => 'image/jpeg,image/png,application/pdf,image/webp', 'hint' => 'Accepted: jpeg, jpg, png, pdf, webp'),

                'address'    => array('type' => 'custom_text', 'placeholder' => 'Street and number'),
                'city'       => array('type' => 'custom_text', 'placeholder' => 'Town or city'),
                'zip'        => array('type' => 'custom_text', 'placeholder' => 'e.g., SW1A 1AA'),
                'country'    => array('type' => 'custom_text', 'placeholder' => 'Select your country'),

                'est_arrival'   => array('type' => 'custom_time', 'placeholder' => 'HH:MM', 'main_guest_only' => true),
                'est_departure' => array('type' => 'custom_time', 'placeholder' => 'HH:MM', 'main_guest_only' => true),

                'notes'      => array('type' => 'custom_textarea', 'placeholder' => 'Anything you wish to share with reception', 'main_guest_only' => true),
            );
        }

        // Preserve default 'file' field from Vik Booking, if present
        public function listPrecheckinFields($def_fields) {
            if (isset($def_fields['file'])) {
                return array('file');
            }
            return array();
        }

        // Fix for controller calls: provide this method even if we don't use it.
        public function validateRegistrationFieldTypes() {
            // No-op; Vik Booking just expects this to exist.
            return;
        }

        // Permissive validation: only check provided values.
        public function validateRegistrationFields(array $booking, array $booking_rooms, array $data, bool $precheckin = true) {
            if (!$precheckin) { return; }

            $ext_whitelist = array('jpeg','jpg','png','pdf','webp');

            foreach ($booking_rooms as $room_index => $room) {
                $adults   = isset($room['adults']) ? (int)$room['adults'] : 1;
                $children = isset($room['children']) ? (int)$room['children'] : 0;
                $count = $adults + $children;

                for ($guest_no = 1; $guest_no <= $count; $guest_no++) {
                    $g = isset($data[$room_index][$guest_no]) ? $data[$room_index][$guest_no] : array();

                    // Date of birth: if provided, cannot be in the future.
                    if (!empty($g['date_birth'])) {
                        $dob = $this->normaliseDate($g['date_birth']);
                        if ($dob && $dob > date('Y-m-d')) {
                            throw new Exception('The date of birth cannot be in the future.', 500);
                        }
                    }

                    // File extension check if a file name is present.
                    if (!empty($g['id_upload']) && is_string($g['id_upload'])) {
                        $ext = strtolower(pathinfo($g['id_upload'], PATHINFO_EXTENSION));
                        if (!in_array($ext, $ext_whitelist, true)) {
                            throw new Exception('The uploaded file type is not allowed.', 500);
                        }
                    }
                }
            }
        }

        public function onPrecheckinDataStored(array $data, array $booking, array $customer) {
            return;
        }

        private function normaliseDate($s) {
            $s = trim((string)$s);
            if ($s === '') return null;
            if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $s, $m)) {
                return $m[3] . '-' . $m[2] . '-' . $m[1];
            }
            if (preg_match('#^(\d{4})-(\d{2})-(\d{2})$#', $s)) {
                return $s;
            }
            return null;
        }
    }
}
