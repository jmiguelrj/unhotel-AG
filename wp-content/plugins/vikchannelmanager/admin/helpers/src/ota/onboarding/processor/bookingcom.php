<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2024 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Executes the onboarding process on Booking.com.
 * 
 * @since 1.9.2
 */
final class VCMOtaOnboardingProcessorBookingcom extends VCMOtaOnboardingProcessoraware
{
    /**
     * Whether we are creating a new property or not.
     * 
     * @var bool
     * @since 1.9.4
     */
    protected $newProperty = true;

    /**
     * The ID of the created hotel.
     * It should be automatically filled in when only a new
     * room type should be created.
     * 
     * @var int|null
     */
    protected $hotelId = null;

    /**
     * An array holding all the photos to upload.
     * 
     * @var string[]
     */
    protected $uploadPhotos = [];

    /**
     * The ID of the associated rate plan.
     * 
     * @var int|null
     */
    protected $ratePlanId = null;

    /**
     * Whether the property facilities have been created.
     * 
     * @var bool
     * @since 1.9.15
     */
    protected $propertyFacilitiesCreated = false;

    /**
     * Whether the contacts has been imported.
     * 
     * @var bool
     * @since 1.9.4
     */
    protected $contactsImported = null;

    /**
     * Whether the invoice settings has been updated.
     * 
     * @var bool
     * @since 1.9.15
     */
    protected $invoiceSettingsUpdated = null;

    /**
     * The ID of the created room type.
     * 
     * @var int|null
     */
    protected $roomTypeId = null;

    /**
     * Whether the new room rate has been created.
     * 
     * @var bool
     */
    protected $roomRateCreated = false;

    /**
     * Whether the room facilities have been created.
     * 
     * @var bool
     * @since 1.9.15
     */
    protected $roomFacilitiesCreated = false;

    /**
     * Whether the new room has been mapped onto the channel manager.
     * 
     * @var bool
     */
    protected $roomMapped = false;

    /**
     * Whether the bulk actions have been already sent.
     * 
     * @var bool
     */
    protected $bulkSent = false;

    /**
     * The last triggered status change.
     * 
     * @var string|null
     * @since 1.9.4
     */
    protected $statusTriggered = null;

    /**
     * @inheritDoc
     */
    public function onboard(object $data)
    {
        // new property activation
        $this->createProperty($data);
        $this->uploadPhotos();
        $this->createRatePlan();
        $this->createPropertyFacilities($data);
        $this->importContacts($data);
        $this->updateInvoiceSettings($data);

        // new room type activation
        $this->createRoomType($data);
        $this->createRoomRate($data);
        $this->createRoomFacilities($data);

        // channel manager mapping
        $this->mapRoom();

        // send bulk actions
        $this->sendBulkActions();
        
        // trigger the "open" status
        $this->triggerStatusChange('Open');

        // process completed successfully
        $this->complete();
    }

    /**
     * Creates a new property on Booking.com according to the provided details.
     */
    protected function createProperty(object $data)
    {
        if ($this->hotelId) {
            // hotel ID already available
            return;
        }

        if (!empty($data->account)) {
            // hotel ID provided, we should create only a room type
            $this->hotelId = (int) $data->account;
            $this->newProperty = false;

            // update progress data
            $this->setProgressData('hotelId', $this->hotelId);

            try {
                // load all the available rate plans while creating a new room type
                $ratePlans = VCMOtaListing::getInstance()->fetchBookingcomRatePlans(
                    $this->hotelId,
                    [
                        'active' => 1,
                        'parent' => 1,
                        'name'   => 'Standard Rate',
                    ]
                );

                // assign the first available rate plan
                $this->ratePlanId = $ratePlans[0]->id ?? null;
            } catch (Exception $error) {
                // rate plan not available, it will be created later
            }

            return;
        }

        if (empty($data->legal_entity_id)) {
            // the legal entity ID is mandatory while creating a new property on Booking.com
            throw new InvalidArgumentException('The legal entity ID is mandatory while creating a new property.', 400);
        }

        // set up photos to upload
        if (!is_array($data->photos ?? null)) {
            // build a list of room photos
            $data->photos = [];

            if (!empty($this->room->img)) {
                // push main photo
                $data->photos[] = VBO_SITE_URI . 'resources/uploads/' . $this->room->img;
            }

            foreach (array_filter(explode(';;', (string) $this->room->moreimgs)) as $extraPhoto) {
                // push extra photo
                $data->photos[] = VBO_SITE_URI . 'resources/uploads/big_' . $extraPhoto;
            }
        }

        // register in a specific property all the photos to upload
        $this->uploadPhotos = $data->photos;

        if (empty($data->languages)) {
            // get all known languages
            $data->languages = array_map('strtolower', array_keys(VikBooking::getVboApplication()->getKnownLanguages()));

            // make sure the en-GB language is supported
            $data->languages = array_values(array_unique(array_merge(
                ['en-gb'],
                $data->languages
            )));
        }

        // preserve only the spoken languages that are actually supported by Booking.com
        $data->languages = array_values(
            array_intersect(
                $data->languages,
                array_keys(VCMBookingcomContent::getLanguageCodes())
            )
        );

        // set the new OTA account ID involved by creating a new property
        $this->hotelId = VCMOtaListing::getInstance(['server' => 'master'])->createBookingcomProperty([
            'legal_id'       => $data->legal_entity_id,
            'name'           => $data->prop_name ?? null,
            'category'       => (int) ($data->prop_category_code ?? 0),
            'currency'       => $data->currency ?? null,
            'latitude'       => $data->latitude ?? null,
            'longitude'      => $data->longitude ?? null,
            'checkin_from'   => $data->checkin_start ?? null,
            'checkin_until'  => $data->checkin_end ?? null,
            'checkout_until' => $data->checkout_end ?? null,
            'city'           => $data->city ?? null,
            'country'        => strtoupper($data->country ?? ''),
            'address'        => $data->address ?? null,
            'postcode'       => $data->postcode ?? null,
            'units'          => 1,
            'languages'      => $data->languages,
            // credentials related values (add new)
            'notify_url' => JUri::root(),
            'cms'        => VCMPlatformDetection::isJoomla() ? 'j' : 'wp',
        ]);

        // update progress data
        $this->setProgressData('hotelId', $this->hotelId);
        $this->setProgressData('summary', 'Hotel ID created.');
    }

    /**
     * Upload the configured photos for the specified property.
     */
    protected function uploadPhotos()
    {
        if (!$this->uploadPhotos) {
            // there are no remaining photos to upload
            return;
        }

        // make a request to upload the property photos from the current listing
        VCMOtaListing::getInstance()->uploadBookingcomPropertyPhotos($this->hotelId, $this->uploadPhotos);
            
        // all photos have been uploaded
        $this->uploadPhotos = [];

        // update progress data
        $this->setProgressData('summary', 'Photos uploaded.');
    }

    /**
     * Creates a new rate plan for the specified property.
     */
    protected function createRatePlan()
    {
        if ($this->ratePlanId) {
            // rate plan already available
            return;
        }

        // make a request for creating one rate plan at property-level
        $this->ratePlanId = VCMOtaListing::getInstance()->createBookingcomRatePlan(
            $this->hotelId,
            [
                'name' => 'Standard Rate',
            ]
        );

        // update progress data
        $this->setProgressData('summary', 'Rate plan ID created.');
    }

    /**
     * Creates the facilities for the specified property.
     * 
     * @since 1.9.15
     */
    protected function createPropertyFacilities(object $data)
    {
        if ($this->propertyFacilitiesCreated) {
            // property facilities already created
            return;
        }

        // apply the "NON-SMOKING ROOMS" facility to the property
        VCMOtaListing::getInstance()->setBookingcomPropertyFacilities($this->hotelId, null, [
            [
                'facility_id' => 16,
                'state' => 'PRESENT',
            ],
        ]);

        $this->propertyFacilitiesCreated = true;

        // update progress data
        $this->setProgressData('summary', 'Property facilities created.');
    }

    /**
     * Imports the provided contacts within the newly created property.
     * 
     * @since 1.9.4
     */
    protected function importContacts(object $data)
    {
        if (!$this->newProperty || $this->contactsImported) {
            // contacts already imported
            return;
        }

        if (empty($data->contact_details)) {
            // the general contact details are usually required by Booking.com
            throw new InvalidArgumentException('Missing required contact details.', 400);
        }

        if (is_string($data->contact_details)) {
            // decode from JSON when we have a string
            $data->contact_details = json_decode($data->contact_details);
        }

        /**
         * Make sure the address under the contact details has been fulfilled.
         * In case at least one of the mandatory properties is blank, the address
         * specified for the physical location will be used instead.
         * 
         * @since 1.9.14
         */
        foreach ($data->contact_details as &$contactDetails) {
            // cast contact details to object
            $contactDetails = (object) $contactDetails;

            // cast address details to object
            $address = (object) ($contactDetails->address ?? new stdClass);

            // check if we have an invalid address
            if (empty($address->city_name)
                || empty($address->country_code)
                || empty($address->postal_code)
                || empty($address->address_line)
                || empty($address->language_code)) {
                // missing required information, use the same address specified for the physical location
                $address->city_name = $data->city ?? null;
                $address->country_code = $data->country ?? null;
                $address->address_line = $data->address ?? null;
                $address->postal_code = $data->postcode ?? null;
                $address->language_code = $address->language_code ?? 'en';

                $contactDetails->address = $address;
            }
        }

        // import contacts
        VCMOtaListing::getInstance()->setBookingcomContactInfos($this->hotelId, $data->contact_details);

        $this->contactsImported = true;

        // update progress data
        $this->setProgressData('summary', 'Contacts imported.');
    }

    /**
     * Updates the invoice settings.
     * 
     * @since 1.9.15
     */
    protected function updateInvoiceSettings(object $data)
    {
        if (!$this->newProperty || $this->invoiceSettingsUpdated) {
            // invoice settings already updated
            return;
        }

        $otaListing = VCMOtaListing::getInstance();

        try {
            // download invoice settings from Booking.com for the first property available
            $invoiceSettings = $otaListing->getBookingcomPropertySettings()->invoice_settings ?? new stdClass;
        } catch (Exception $error) {
            // unable to obtain property settings, start from scratch
            $invoiceSettings = new stdClass;
        }

        if (empty($invoiceSettings->legal_name) && !empty($data->prop_name)) {
            // use property name as legal name
            $invoiceSettings->legal_name = $data->prop_name;
        }

        if (empty($invoiceSettings->contact_person)) {
            // define a callback to detect the contact name
            $getContactName = function($contacts) {
                if (empty($contacts)) {
                    return null;
                }

                if (is_string($contacts)) {
                    // decode from JSON when we have a string
                    $contacts = json_decode($contacts);
                }

                foreach ($contacts as $contact) {
                     // cast contact details to object
                    $contact = (object) $contact;

                    // access contact person object
                    $contactPerson = (object) ($contact->contact_person ?? null);

                    if (!empty($contactPerson->name)) {
                        return $contactPerson->name;
                    }
                }

                return null;
            };

            // set contact person
            $invoiceSettings->contact_person = $getContactName($data->contact_details ?? []) ?: JFactory::getUser()->name;
        }

        if (empty($invoiceSettings->address) && !empty($data->address)) {
            // use property address
            $invoiceSettings->address = $data->address;
        }

        if (empty($invoiceSettings->state) && !empty($data->state)) {
            // use property state
            $invoiceSettings->state = $data->state;
        }

        if (empty($invoiceSettings->notification_channel)) {
            // always use email as notification channel
            $invoiceSettings->notification_channel = 'EMAIL';
        }

        if (empty($invoiceSettings->country_code) && !empty($data->country)) {
            // use property country
            $invoiceSettings->country_code = $data->country;
        }

        if (empty($invoiceSettings->city) && !empty($data->city)) {
            // use property city
            $invoiceSettings->city = $data->city;
        }

        if (empty($invoiceSettings->postal_code) && !empty($data->postcode)) {
            // use property postal code
            $invoiceSettings->postal_code = $data->postcode;
        }

        // update invoice settings on Booking.com
        $otaListing->setBookingcomPropertySettings($this->hotelId, [
            'invoice_settings' => $invoiceSettings,
        ]);

        $this->invoiceSettingsUpdated = true;

        // update progress data
        $this->setProgressData('summary', 'Invoice settings updated.');
    }

    /**
     * Creates a new room type for the specified property.
     */
    protected function createRoomType(object $data)
    {
        if ($this->roomTypeId) {
            // room type already created
            return;
        }

        // set the new OTA account ID involved by creating a new property
        $this->roomTypeId = VCMOtaListing::getInstance()->createBookingcomRoomType(
            $this->hotelId,
            [
                'title' => $data->listing_name ?? null,
                'room' => [
                    'type' => (int) ($data->room_type_code ?? 1),
                    'units' => max(1, (int) $this->room->units),
                ],
                'occupancy' => [
                    'max' => (int) $this->room->totpeople,
                    'maxadults' => (int) $this->room->toadult,
                    'maxchild' => (int) $this->room->tochild,
                ],
            ]
        );

        // update progress data
        $this->setProgressData('roomTypeId', $this->roomTypeId);
        $this->setProgressData('summary', 'Room type ID created.');
    }

    /**
     * Creates a new room rate for the specified property.
     */
    protected function createRoomRate(object $data)
    {
        if ($this->roomRateCreated) {
            // room rate already created
            return;
        }

        // cancellation policy code
        $cancellationCode = null;

        // attempt to find an already defined cancellation policy code
        try {
            $cancPolicyCodes = VCMOtaListing::getInstance()->getBookingcomCancPolicyCodes($this->hotelId);
            $cancellationCode = array_keys($cancPolicyCodes)[0];
        } catch (Exception $e) {
            // guess the cancellation policy code as a fallback
            $cancellationCode = VCMOtaListing::getInstance()->guessBookingcomCancPolicy($data->pref_canc_policy ?? null);
        }

        // create the room-rate relation
        VCMOtaListing::getInstance()->createBookingcomRoomRate(
            $this->hotelId,
            $this->roomTypeId,
            $this->ratePlanId,
            [
                'canc_code' => $cancellationCode,
            ]
        );

        $this->roomRateCreated = true;

        // update progress data
        $this->setProgressData('summary', 'Room rate created.');
    }

    /**
     * Creates the facilities for the specified room property.
     * 
     * @since 1.9.15
     */
    protected function createRoomFacilities(object $data)
    {
        if ($this->roomFacilitiesCreated) {
            // room facilities already created
            return;
        }

        /**
         * Check whether the specified property category requires the kitchen facility.
         * 
         * - 3 (apartment): required
         * - 8 (condominium): required
         * - 35 (villa): recommended
         * - 5000 (aparthotel): required
         * - 5006 (holiday home): recommended
         * - 5009 (holiday park): recommended
         */
        if (in_array($data->prop_category_code, [3, 8, 35, 5000, 5006, 5009])) {
            // apply the "Kitchen" facility to the room type
            VCMOtaListing::getInstance()->setBookingcomPropertyFacilities($this->hotelId, $this->roomTypeId, [
                [
                    'room_facility_id' => 45,
                    'state' => 'PRESENT',
                ],
            ]);
        } else {
            // According to B.com documentation, we should specify at least one amenity per room for the property.
            // Among all the available ones, the INTERNET_FACILITIES seems to be the best choice.
            VCMOtaListing::getInstance()->setBookingcomPropertyFacilities($this->hotelId, $this->roomTypeId, [
                [
                    'room_facility_id' => 2,
                    'state' => 'PRESENT',
                ],
            ]);
        }

        $this->roomFacilitiesCreated = true;

        // update progress data
        $this->setProgressData('summary', 'Room facilities created.');
    }

    /**
     * Maps the created property/room onto the channel manager.
     */
    protected function mapRoom()
    {
        if ($this->roomMapped) {
            // room already mapped onto the channel manager
            return;
        }

        // obtain the room-rate information
        $roomRate = VCMOtaListing::getInstance()->fetchBookingcomRoomRates([
            'hotel_id'    => $this->hotelId,
            'ota_room_id' => $this->roomTypeId,
        ]);

        if (empty($roomRate['rate_plans'])) {
            // there must be an active rate plan
            throw new UnexpectedValueException('No active rate plans found.', 400);
        }

        // prepare record object
        $record = new stdClass;
        $record->idroomvb    = $this->room->id;
        $record->idroomota   = $this->roomTypeId;
        $record->idchannel   = VikChannelManagerConfig::BOOKING;
        $record->channel     = 'booking.com';
        $record->otaroomname = $roomRate['name'] ?? $this->room->name;
        $record->otapricing  = json_encode(['RatePlan' => $roomRate['rate_plans']]);
        $record->prop_name   = ($roomRate['hotel_name'] ?: $this->hotelId);
        $record->prop_params = json_encode(['hotelid' => (string) $this->hotelId]);

        // store the room mapping record
        JFactory::getDbo()->insertObject('#__vikchannelmanager_roomsxref', $record, 'id');

        $this->roomMapped = true;

        // update progress data
        $this->setProgressData('summary', 'Room type mapped.');
    }

    /**
     * Send the bulk actions.
     */
    protected function sendBulkActions()
    {
        if ($this->bulkSent) {
            // bulk actions already sent
            return;
        }

        // obtain the current date and time
        $dt = JFactory::getDate('now', JFactory::getApplication()->get('offset', 'UTC'));

        // trigger the bulk actions
        VikChannelManager::autoBulkActions([
            'forced_rooms' => $this->room->id,
            'from_date'    => $dt->format('Y-m-d'),
            'to_date'      => $dt->modify('+9 months')->format('Y-m-d'),
            'server'       => 'master',
            'uniquekey'    => VikChannelManagerConfig::BOOKING,
        ]);

        $this->bulkSent = true;

        // update progress data
        $this->setProgressData('summary', 'Bulk actions transmitted.');
    }

    /**
     * Trigger a status change on Booking.com.
     * 
     * @since 1.9.4
     */
    protected function triggerStatusChange(string $status)
    {
        if (!$this->newProperty || $this->statusTriggered === $status) {
            // this status has been already triggered
            return;
        }

        // trigger status change
        VCMOtaListing::getInstance()->triggerBookingcomHotelStatus($this->hotelId, $status);

        $this->statusTriggered = $status;

        // update progress data
        $this->setProgressData('summary', 'Triggered ' . $status . ' status change on Booking.com.');
    }
}
