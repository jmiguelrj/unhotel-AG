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
 * Executes the onboarding process on Airbnb.
 * 
 * @since 1.9.2
 */
final class VCMOtaOnboardingProcessorAirbnbapi extends VCMOtaOnboardingProcessoraware
{
    /**
     * The ID of the host.
     * 
     * @var int|null
     */
    protected $hostId = null;

    /**
     * The ID of the created listing.
     * 
     * @var int|null
     */
    protected $listingId = null;

    /**
     * The maximum cost defined for the selected room.
     * 
     * @var float|null
     */
    protected $listingMaxCost = null;

    /**
     * Flag used to check whether the listing has been finalized.
     * 
     * @var bool
     */
    protected $listingUpdated = false;

    /**
     * Flag used to check whether the listing descriptions have been updated.
     * 
     * @var bool
     */
    protected $descriptionsUpdated = false;

    /**
     * Flag used to check whether the listing photos have been uploaded.
     * 
     * @var bool
     */
    protected $photosUploaded = false;

    /**
     * Flag used to check whether the booking settings have been updated.
     * 
     * @var bool
     */
    protected $bookingSettingsUpdated = false;

    /**
     * Flag used to check whether the pna model has been defined.
     * 
     * @var bool
     */
    protected $pnaModelDefined = false;

    /**
     * Flag used to check whether the pricing settings have been applied.
     * 
     * @var bool
     */
    protected $pricingSettingsApplied = false;

    /**
     * Flag used to check whether the listing has been published.
     * 
     * @var bool
     */
    protected $listingPublished = false;

    /**
     * Flag used to check whether the new listing has been mapped onto the channel manager.
     * 
     * @var bool
     */
    protected $listingMapped = false;

    /**
     * Whether the bulk actions have been already sent.
     * 
     * @var bool
     */
    protected $bulkSent = false;

    /**
     * @inheritDoc
     */
    public function onboard(object $data)
    {
        if (!$this->hostId) {
            // internally register the host ID
            $this->hostId = $data->account ?? null;

            // update progress data
            $this->setProgressData('hostId', $this->hostId);
        }

        if ($this->listingMaxCost === null) {
            // calculate the maximum cost for the selected room
            $this->listingMaxCost = (float) VCMOtaListing::getInstance()->getListingMaxCost($this->room->id);
        }

        // new listing activation
        $this->createListing($data);
        $this->finalizeListing($data);
        $this->setDescriptions();
        $this->uploadPhotos();

        // pricing and settings
        $this->updateBookingSettings($data);
        $this->setPnaModel();
        $this->setPricingSettings();

        // activate listing
        $this->publishListing($data);

        // channel manager mapping
        $this->mapListing($data);

        // send bulk actions
        $this->sendBulkActions();

        try {
            // re-download the listing information for saving them internally
            VCMOtaListing::getInstance()->fetchRemoteDetails($this->listingId, [
                'channel_id' => VikChannelManagerConfig::AIRBNBAPI,
                'user_id' => $this->hostId,
            ], $save = true);
        } catch (Exception $e) {
            // do nothing
        }

        // process completed successfully
        $this->complete();
    }

    /**
     * Creates a new listing on Airbnb according to the provided details.
     * 
     * @param   object  $data  The onboarding details.
     * 
     * @return  void
     */
    protected function createListing(object $data)
    {
        if ($this->listingId) {
            // listing ID already available
            return;
        }

        if (empty($this->hostId)) {
            // the host ID is mandatory while creating a new listing on Airbnb
            throw new InvalidArgumentException('The host ID is mandatory while creating a new listing.', 400);
        }

        if (empty($data->listing_name)) {
            // the name is mandatory while creating a new listing on Airbnb
            throw new InvalidArgumentException('The name is mandatory while creating a new listing.', 400);
        }

        // set the new OTA listing ID involved by creating a new listing (just with the name)
        $this->listingId = VCMOtaListing::getInstance()->createAirbnbListing(
            $this->hostId,
            [
                'name' => $data->listing_name,
                // credentials related values (add new)
                'notify_url' => JUri::root(),
                'cms'        => VCMPlatformDetection::isJoomla() ? 'j' : 'wp',
            ]
        );

        // update progress data
        $this->setProgressData('listingId', $this->listingId);
        $this->setProgressData('summary', 'Listing created.');
    }

    /**
     * Updates the newly created listing with the provided details, since they cannot be
     * used directly within the POST (creation) request.
     * 
     * @param   object  $data  The onboarding details.
     * 
     * @return  void
     */
    protected function finalizeListing(object $data)
    {
        if ($this->listingUpdated) {
            // listing already finalized
            return;
        }

        // complete the listing details by updating it
        VCMOtaListing::getInstance()->updateAirbnbListing(
            $this->hostId,
            $this->listingId,
            [
                'property_type_group' => $data->property_type_group ?? null,
                'property_type_category' => $data->property_type_category ?? null,
                'room_type_category' => $data->room_type_category ?? null,
                'person_capacity' => (int) $this->room->totpeople,
                'bedrooms' => $data->bedrooms ?? null,
                'bathrooms' => $data->bathrooms ?? null,
                'beds' => $data->beds ?? null,
                'address' => $data->address ?? null,
                'city' => $data->city ?? null,
                'state' => $data->state ?? null,
                'postcode' => $data->postcode ?? null,
                'country' => $data->country ?? null,
                'latitude' => $data->latitude ?? null,
                'longitude' => $data->longitude ?? null,
            ]
        );

        $this->listingUpdated = true;

        // update progress data
        $this->setProgressData('summary', 'Listing populated.');
    }

    /**
     * Updates the listing descriptions according to the room details.
     * 
     * @return  void
     */
    protected function setDescriptions()
    {
        if ($this->descriptionsUpdated) {
            // listing descriptions already published
            return;
        }

        $listingSummary = '';
        $listingNotes = null;

        $longDescription  = strip_tags((string) $this->room->info);
        $shortDescription = (string) $this->room->smalldesc;

        if ($longDescription) {
            // long description given use it as summary
            $listingSummary = $longDescription;
            
            if ($shortDescription) {
                // short description given, use it as notes
                $listingNotes = $shortDescription;
            }
        } else if ($shortDescription) {
            // long description not given, use the short description as summary
            $listingSummary = $shortDescription;
        } else {
            // no short or long descriptions given, use the room name as summary
            $listingSummary = $this->room->name;
        }

        // set the listing descriptions
        VCMOtaListing::getInstance()->setAirbnbListingDescriptions(
            $this->hostId,
            $this->listingId,
            VCMAirbnbContent::getDefaultLocale(),
            [
                'summary' => $listingSummary,
                'notes' => $listingNotes,
            ]
        );

        $this->descriptionsUpdated = true;

        // update progress data
        $this->setProgressData('summary', 'Listing descriptions created.');
    }

    /**
     * Uploads the photos for the newly created listing.
     * 
     * @return  void
     */
    protected function uploadPhotos()
    {
        if ($this->photosUploaded) {
            // listing photos already uploaded
            return;
        }

        $photos = [];
        $captions = (array) json_decode((string) $this->room->imgcaptions);

        if (!empty($this->room->img)) {
            // push main photo
            $photos[] = [
                'path' => JPath::clean(VBO_SITE_PATH . '/resources/uploads/' . $this->room->img),
                'caption' => $this->room->name,
            ];
        }

        foreach (array_filter(explode(';;', (string) $this->room->moreimgs)) as $index => $extraPhoto) {
            // push extra photo
            $photos[] = [
                'path' => JPath::clean(VBO_SITE_PATH . '/resources/uploads/big_' . $extraPhoto),
                'caption' => $captions[$index] ?? null,
            ];
        }

        if (!$photos) {
            throw new Exception('The listing requires to have at least one photo.', 500);
        }

        $totUploaded = 0;

        try {
            // upload listing photos
            $totUploaded = VCMOtaListing::getInstance()->uploadAirbnbListingPhotos(
                $this->hostId,
                $this->listingId,
                $photos,
                $maxPhotosToUpload = 5
            );
        } catch (Exception $error) {
            // ignore photos upload failure
        }

        if (!$totUploaded) {
            throw $error ?? new Exception('Could not upload any valid listing photos.', 500);
        }

        $this->photosUploaded = true;

        // update progress data
        $this->setProgressData('summary', 'Listing photos uploaded.');
    }

    /**
     * Configures the booking settings of this listing according to the provided details.
     * 
     * @param   object  $data  The onboarding details.
     * 
     * @return  void
     */
    protected function updateBookingSettings(object $data)
    {
        if ($this->bookingSettingsUpdated) {
            // booking settings already configured
            return;
        }

        // configure the booking settings of this listing
        VCMOtaListing::getInstance()->updateAirbnbListingBookingSettings(
            $this->hostId,
            $this->listingId,
            [
                'check_in_time_start' => $data->checkin_start ?? null,
                'check_in_time_end' => $data->checkin_end ?? null,
                'check_out_time' => $data->checkout_end ?? null,
            ]
        );

        $this->bookingSettingsUpdated = true;

        // update progress data
        $this->setProgressData('summary', 'Listing booking settings created.');
    }

    /**
     * Configures the standard PnA model for the Airbnb listing.
     * 
     * @return  void
     */
    protected function setPnaModel()
    {
        if ($this->pnaModelDefined) {
            // pna model already defined
            return;
        }

        // configure the standard PnA model
        VCMOtaListing::getInstance()->setAirbnbListingPnAModel(
            $this->hostId,
            $this->listingId,
            [
                'model' => 'STANDARD',
            ]
        );

        $this->pnaModelDefined = true;

        // update progress data
        $this->setProgressData('summary', 'Listing Pricing and Availability model created.');
    }

    /**
     * Configures the pricing settings for the newly created listing on Airbnb.
     * 
     * @return  void
     */
    protected function setPricingSettings()
    {
        if ($this->pricingSettingsApplied) {
            // pna model already defined
            return;
        }

        // set listing pricing settings
        VCMOtaListing::getInstance()->setAirbnbListingPricingSettings(
            $this->hostId,
            $this->listingId,
            [
                'listing_currency' => VikBooking::getCurrencyCodePp(),
                'default_daily_price' => $this->listingMaxCost,
                'guests_included' => 1,
            ]
        );

        $this->pricingSettingsApplied = true;

        // update progress data
        $this->setProgressData('summary', 'Listing pricing settings created.');
    }

    /**
     * Publishes the newly created listing on Airbnb.
     * 
     * @param   object  $data  The onboarding details.
     * 
     * @return  void
     */
    protected function publishListing(object $data)
    {
        if ($this->listingPublished) {
            // listing already published
            return;
        }

        // first, API-connect the listing to trigger the validation
        VCMOtaListing::getInstance()->updateAirbnbListing(
            $this->hostId,
            $this->listingId,
            [
                // it looks like re-posting the listing creation details is required
                'property_type_group' => $data->property_type_group ?? null,
                'property_type_category' => $data->property_type_category ?? null,
                'room_type_category' => $data->room_type_category ?? null,
                'person_capacity' => (int) $this->room->totpeople,
                'bedrooms' => $data->bedrooms ?? null,
                'bathrooms' => $data->bathrooms ?? null,
                'beds' => $data->beds ?? null,
                'address' => $data->address ?? null,
                'city' => $data->city ?? null,
                'state' => $data->state ?? null,
                'postcode' => $data->postcode ?? null,
                'country' => $data->country ?? null,
                'latitude' => $data->latitude ?? null,
                'longitude' => $data->longitude ?? null,
                // submit the API-connect instruction
                'synchronization_category' => 'sync_all',
                // publish the listing
                'active' => true,
            ]
        );

        $this->listingPublished = true;

        // update progress data
        $this->setProgressData('summary', 'Listing was API-connected and published.');
    }

    /**
     * Maps the created listing onto the channel manager.
     * 
     * @param   object  $data  The onboarding details.
     * 
     * @return  void
     */
    protected function mapListing(object $data)
    {
        if ($this->listingMapped) {
            // listing already mapped onto the channel manager
            return;
        }

        // store on VCM the Airbnb listing mapping details
        $accountData = VikChannelManager::getChannelAccountData(VikChannelManagerConfig::AIRBNBAPI, $this->hostId);

        $otaPricingData = $accountData['otapricing'];

        if ($otaPricingData['RatePlan']['-1'] ?? []) {
            // adjust pricing/mapping information for the new listing by copying an existing account (useful for taxes eligibility)
            $otaPricingData['RatePlan']['-1']['daily_price'] = $this->listingMaxCost;
            $otaPricingData['RatePlan']['-1']['person_capacity'] = (int) $this->room->totpeople;
            $otaPricingData['RatePlan']['-1']['guests_included'] = 1;
            $otaPricingData['RatePlan']['-1']['price_per_extra_person'] = 0;
        }

        // prepare record object
        $record = new stdClass;
        $record->idroomvb    = $this->room->id;
        $record->idroomota   = $this->listingId;
        $record->idchannel   = VikChannelManagerConfig::AIRBNBAPI;
        $record->channel     = 'airbnbapi';
        $record->otaroomname = $data->listing_name ?? $this->room->name;
        $record->otapricing  = json_encode($otaPricingData);
        $record->prop_name   = ($accountData['prop_name'] ?? $this->hostId);
        $record->prop_params = json_encode($accountData['prop_params'] ?? []);

        // store the room mapping record
        JFactory::getDbo()->insertObject('#__vikchannelmanager_roomsxref', $record, 'id');

        $this->listingMapped = true;

        // update progress data
        $this->setProgressData('summary', 'Listing was mapped.');
    }

    /**
     * Send the bulk actions.
     * 
     * @return  void
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
            'uniquekey'    => VikChannelManagerConfig::AIRBNBAPI,
        ]);

        $this->bulkSent = true;

        // update progress data
        $this->setProgressData('summary', 'Bulk actions transmitted.');
    }
}
