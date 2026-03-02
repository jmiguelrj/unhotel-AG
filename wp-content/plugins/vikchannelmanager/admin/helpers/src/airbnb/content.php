<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2023 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Airbnb listing contents helper.
 * 
 * @since   1.9.2
 */
final class VCMAirbnbContent
{
    /**
     * Returns a list of property type group enumerations, or the supported
     * property types under a specific type group.
     * 
     * @param   string  $group  Optional group filter to get the property
     *                          types supported by the given group code.
     * 
     * @return  array
     */
    public static function getListingPropertyTypeGroups($group = null)
    {
        $ptg = [
            'apartments' => [
                'apartment',
                'condominium',
                'serviced_apartment',
                'loft',
                'casa_particular',
            ],
            'bnb' => [
                'bnb',
                'farm_stay',
                'lodge',
                'casa_particular',
                'minsu',
                'ryokan',
            ],
            'boutique_hotels_and_more' => [
                'boutique_hotel',
                'aparthotel',
                'hostel',
                'hotel',
                'lodge',
                'kezhan',
                'resort',
                'serviced_apartment',
                'heritage_hotel',
            ],
            'houses' => [
                'bungalow',
                'cabin',
                'chalet',
                'cottage',
                'dome_house',
                'earthhouse',
                'farm_stay',
                'house',
                'houseboat',
                'hut',
                'lighthouse',
                'tiny_house',
                'townhouse',
                'villa',
                'casa_particular',
                'cycladic_house',
                'dammuso',
                'shepherds_hut',
                'trullo',
                'pension',
            ],
            'secondary_units' => [
                'guesthouse',
                'guest_suite',
                'farm_stay',
            ],
            'unique_homes' => [
                'barn',
                'boat',
                'rv',
                'campsite',
                'castle',
                'cave',
                'dome_house,earthhouse',
                'farm_stay',
                'houseboat',
                'hut',
                'igloo',
                'island',
                'lighthouse',
                'plane',
                'tent',
                'tiny_house',
                'tipi',
                'train',
                'treehouse',
                'windmill',
                'yurt',
                'pension',
                'shepherds_hut',
                'ranch',
                'holiday_park',
                'tower',
                'riad',
                'religious_building',
                'shipping_container',
            ],
        ];

        if (!$group) {
            return array_keys($ptg);
        }

        return $ptg[$group] ?? $ptg;
    }

    /**
     * Returns a list of property type categories related to a group.
     * 
     * @return  array
     */
    public static function getListingPropertyTypeCategories()
    {
        return [
            'aparthotel',
            'apartment',
            'barn',
            'bnb',
            'boat',
            'boutique_hotel',
            'bungalow',
            'cabin',
            'campsite',
            'casa_particular',
            'castle',
            'cave',
            'chalet',
            'condominium',
            'cottage',
            'cycladic_house',
            'dammuso',
            'dome_house',
            'earthhouse',
            'farm_stay',
            'guest_suite',
            'guesthouse',
            'heritage_hotel',
            'holiday_park',
            'hostel',
            'hotel',
            'house',
            'houseboat',
            'hut',
            'igloo',
            'island',
            'kezhan',
            'lighthouse',
            'lodge',
            'loft',
            'minsu',
            'pension',
            'plane',
            'ranch',
            'religious_building',
            'resort',
            'riad',
            'rv',
            'ryokan',
            'serviced_apartment',
            'shepherds_hut',
            'shipping_container',
            'tent',
            'tiny_house',
            'tipi',
            'tower',
            'townhouse',
            'train',
            'treehouse',
            'trullo',
            'villa',
            'windmill',
            'yurt',
        ];
    }

    /**
     * Returns an associative list of room type categories.
     * 
     * @return  array
     */
    public static function getListingRoomTypeCategories()
    {
        return [
            'private_room' => 'Private room',
            'entire_home'  => 'Entire home',
            'shared_room'  => 'Shared room',
        ];
    }

    /**
     * Returns an associative list of supported listing view enumerations.
     * 
     * @return  array
     */
    public static function getListingViews()
    {
        return [
            'bay_view' => 'Bay view.',
            'beach_view' => 'Beach view.',
            'canal_view' => 'Canal view.',
            'city_view' => 'City view.',
            'courtyard_view' => 'Courtyard view.',
            'desert_view' => 'Desert view.',
            'garden_view' => 'Garden view.',
            'golf_view' => 'Golf course view.',
            'harbor_view' => 'Harbor view.',
            'lake_view' => 'Lake view.',
            'marina_view' => 'Marina view.',
            'mountain_view' => 'Mountain view.',
            'ocean_view' => 'Ocean view.',
            'park_view' => 'Park view.',
            'pool_view' => 'Pool view.',
            'resort_view' => 'Resort view.',
            'river_view' => 'River view.',
            'sea_view' => 'Sea view.',
            'valley_view' => 'Valley view.',
            'vineyard_view' => 'Vineyard view.',
        ];
    }

    /**
     * Returns a list of supported locales.
     * 
     * @return  array
     */
    public static function getSupportedLocales()
    {
        return [
            'az',
            'id',
            'bs',
            'ca',
            'cs',
            'da',
            'de',
            'et',
            'en-AU',
            'en-CA',
            'en',
            'es',
            'fr',
            'hr',
            'xh',
            'zu',
            'is',
            'it',
            'sw',
            'lv',
            'lt',
            'hu',
            'mt',
            'ms',
            'nl',
            'no',
            'pl',
            'pt',
            'ro',
            'sq',
            'sk',
            'sl',
            'sr',
            'fi',
            'sv',
            'tl',
            'vi',
            'tr',
            'el',
            'bg',
            'mk',
            'ru',
            'uk',
            'ka',
            'hy',
            'he',
            'ar',
            'hi',
            'th',
            'ko',
            'ja',
            'zh',
            'zh-TW',
        ];
    }

    /**
     * Returns the default supported locale.
     * 
     * @return  string  The locale identifier.
     */
    public static function getDefaultLocale()
    {
        $lang = JFactory::getLanguage()->getTag();
        $lang_main = $lang;

        if (strpos($lang, '-') !== false) {
            $parts = explode('-', $lang);
            if (count($parts) === 2) {
                $lang = strtolower($parts[0]) . '-' . strtoupper($parts[1]);
                $lang_main = strtolower($parts[0]);
            }
        }

        $supported = self::getSupportedLocales();

        if (in_array($lang, $supported)) {
            return $lang;
        }

        if (in_array($lang_main, $supported)) {
            return $lang_main;
        }

        // default to English
        return 'en';
    }

    /**
     * Returns an enumeration impact value hitting a specific threshold.
     * 
     * @param   int|float   $value          The value to analyse.
     * @param   ?array      $thresholds     Optional thresholds map.
     * 
     * @return  string                      Matched enumeration impact value.
     * 
     * @since   1.9.14
     */
    public static function getValueImpactEnum($value, ?array $thresholds = null)
    {
        $value = (float) $value;

        if (!$thresholds) {
            $thresholds = [
                'success' => 4,
                'warning' => 3.5,
            ];
        }

        if ($value > ($thresholds['success'] ?? 4)) {
            return 'success';
        }

        if ($value > ($thresholds['warning'] ?? 3.5)) {
            return 'warning';
        }

        if (($thresholds['neutral'] ?? null) && $value >= $thresholds['neutral']) {
            return 'neutral';
        }

        return 'error';
    }

    /**
     * Loads the information for all the currently configured Airbnb host accounts.
     * 
     * @return  array   Associative list of mapped host accounts data.
     * 
     * @since   1.9.14
     */
    public static function loadHostAccounts()
    {
        $dbo = JFactory::getDbo();

        $hostAccounts = [];

        $dbo->setQuery(
            $dbo->getQuery(true)
                ->select([
                    $dbo->qn('prop_name'),
                    $dbo->qn('prop_params'),
                ])
                ->from($dbo->qn('#__vikchannelmanager_roomsxref'))
                ->where($dbo->qn('idchannel') . ' = ' . VikChannelManagerConfig::AIRBNBAPI)
                ->group($dbo->qn('prop_name'))
                ->group($dbo->qn('prop_params'))
        );

        foreach ($dbo->loadAssocList() as $account) {
            $params = (array) json_decode($account['prop_params'], true);
            if (empty($params['user_id'])) {
                continue;
            }
            $hostAccounts[$params['user_id']] = $account['prop_name'] ?: $params['user_id'];
        }

        return $hostAccounts;
    }

    /**
     * Normalizes the hosting quality data payload for various display purposes.
     * Hosting quality data contains statistics retrieved for all host listings.
     * 
     * @param   array    $data     The hosting quality data payload (list of listing objects) to normalize.
     * @param   ?array   $options  Associative list of options for normalizing data.
     * 
     * @return  array              Normalized hosting quality data payload for display.
     * 
     * @since   1.9.14
     */
    public static function normalizeHostingQualityData(array $data, ?array $options = null)
    {
        $dbo = JFactory::getDbo();

        // collect all the known review category tags
        $review_category_tags_list = VCMAirbnbReview::getCategoryTags();

        // start default statistic values
        $hosting_quality_data = [
            'host_id' => (string) ($options['host_id'] ?? ''),
            'host_name' => '',
            'listings_map' => (array) ($options['listings_map'] ?? []),
            'category_tags_map' => array_combine(array_keys($review_category_tags_list), array_map(function($category_tag) {
                return $category_tag['descr'] ?? '';
            }, $review_category_tags_list)),
            'info' => [
                'review_categories' => [],
                'listing_ids'       => array_column($data, 'entity_id'),
                'tot_listings'      => count($data),
                'tot_reviews'       => array_sum(array_column($data, 'count')),
            ],
            'stats' => [
                'top_positive_category_tags'   => [],
                'top_negative_category_tags'   => [],
                'rank_review_categories'       => [],
                'rank_listing_scores'          => [],
                'best_rating_listing_id'       => null,
                'worst_rating_listing_id'      => null,
                'best_rating_listing_score'    => 0,
                'worst_rating_listing_score'   => 0,
                'most_reviewed_listing_id'     => null,
                'least_reviewed_listing_id'    => null,
                'most_reviewed_listing_count'  => 0,
                'least_reviewed_listing_count' => 0,
            ],
        ];

        // collect all the available review category (group) enumerations
        foreach (array_column($data, 'category_stats') as $category_stats) {
            foreach (array_column($category_stats, 'category') as $category) {
                if (!in_array($category, $hosting_quality_data['info']['review_categories'])) {
                    // push review category tag
                    $hosting_quality_data['info']['review_categories'][] = $category;
                }
            }
        }

        // apply options on global data
        if ($options['listing_id'] ?? null) {
            // filter data by listing ID
            $filterListingId = $options['listing_id'];
            $data = array_values(array_filter($data, function($listing_stats) use ($filterListingId) {
                return ($listing_stats['entity_id'] ?? '') == $filterListingId;
            }));
        }
        if ($options['review_category'] ?? null) {
            // filter data by review category (i.e. "accuracy", "location", "checkin", "communication", "value", "cleanliness")
            $filterReviewCategory = strtolower((string) $options['review_category']);
            // get rid of the stats for the listings who do not contain the required review category
            $data = array_values(array_filter($data, function($listing_stats) use ($filterReviewCategory) {
                return in_array($filterReviewCategory, array_map('strtolower', array_column(((array) $listing_stats['category_stats'] ?? []), 'category')));
            }));
            // map the eligible listing category stats by keeping just the required review category
            $data = array_map(function($listing_stats) use ($filterReviewCategory) {
                $listing_stats['category_stats'] = array_values(array_filter($listing_stats['category_stats'], function($category_stats) use ($filterReviewCategory) {
                    return ($category_stats['category'] ?? '') == $filterReviewCategory;
                }));
                return $listing_stats;
            }, $data);
        }
        if ($options['category_tag'] ?? null) {
            // filter data by review category tag (i.e. "GUEST_REVIEW_HOST_POSITIVE_LOTS_TO_DO", "GUEST_REVIEW_HOST_NEGATIVE_DIRTY_OR_DUSTY")
            $filterCategoryTag = strtoupper((string) $options['category_tag']);
            // get rid of the stats for the listings who do not contain the required category tag
            $data = array_values(array_filter($data, function($listing_stats) use ($filterCategoryTag) {
                $found = false;
                foreach ((array) $listing_stats['category_stats'] ?? [] as $category_stats) {
                    if (in_array($filterCategoryTag, array_keys($category_stats['category_tag_count_map'] ?? []))) {
                        $found = true;
                        break;
                    }
                }
                return $found;
            }));
        }

        // update total number of filtered listings and IDs
        $hosting_quality_data['info']['tot_filtered_listings'] = count($data);
        $hosting_quality_data['info']['filtered_listing_ids'] = array_column($data, 'entity_id');

        // map listing review ratings and counters
        $listing_review_ratings  = [];
        $listing_review_counters = [];

        // map review category ratings
        $review_category_ratings = [];

        // scan all listing statistic objects
        foreach ($data as $listing_stats) {
            $listing_stats = (array) $listing_stats;
            $listingId = $listing_stats['entity_id'] ?? 0;
            $listing_review_ratings[$listingId] = $listing_stats['rating'] ?? 0;
            $listing_review_counters[$listingId] = $listing_stats['count'] ?? 0;
            foreach (($listing_stats['category_stats'] ?? []) as $category_stats) {
                $category_stats = (array) $category_stats;
                // push review category ranking
                $categoryName = $category_stats['category'] ?? '';
                $categoryRating = (float) $category_stats['rating'] ?? 0;
                $review_category_ratings[$categoryName] = $review_category_ratings[$categoryName] ?? [];
                $review_category_ratings[$categoryName][] = $categoryRating;

                // scan all review category tags
                foreach (($category_stats['category_tag_count_map'] ?? []) as $tag => $count) {
                    if (stripos($tag, 'POSITIVE') !== false) {
                        // update count value for positive review tag
                        $hosting_quality_data['stats']['top_positive_category_tags'][$tag] = ($hosting_quality_data['stats']['top_positive_category_tags'][$tag] ?? 0) + (int) $count;
                    } elseif (stripos($tag, 'NEGATIVE') !== false) {
                        // update count value for negative review tag
                        $hosting_quality_data['stats']['top_negative_category_tags'][$tag] = ($hosting_quality_data['stats']['top_negative_category_tags'][$tag] ?? 0) + (int) $count;
                    }
                }
            }
        }

        // sort positive and negative review category tags (associative-descending)
        arsort($hosting_quality_data['stats']['top_positive_category_tags']);
        arsort($hosting_quality_data['stats']['top_negative_category_tags']);

        // count review category rating average values and sort them in descending order
        foreach ($review_category_ratings as $categoryName => $categoryRatings) {
            $hosting_quality_data['stats']['rank_review_categories'][$categoryName] = round(array_sum($categoryRatings) / count($categoryRatings), 2);
        }
        arsort($hosting_quality_data['stats']['rank_review_categories']);

        // sort listing review ratings and counters (associative-descending)
        arsort($listing_review_ratings);
        arsort($listing_review_counters);

        // set best and worst rated listing ID (cast to string large integers for JS)
        $hosting_quality_data['stats']['best_rating_listing_id'] = (string) key($listing_review_ratings);
        $hosting_quality_data['stats']['worst_rating_listing_id'] = (string) key(array_slice($listing_review_ratings, -1, 1, true));
        $hosting_quality_data['stats']['best_rating_listing_score'] = (float) current($listing_review_ratings);
        $hosting_quality_data['stats']['worst_rating_listing_score'] = (float) end($listing_review_ratings);

        if (($options['purpose'] ?? '') === 'view') {
            $hosting_quality_data['stats']['rank_listing_scores'] = $listing_review_ratings;
        }

        // set most and least reviewed listing ID (cast to string large integers for JS)
        $hosting_quality_data['stats']['most_reviewed_listing_id'] = (string) key($listing_review_counters);
        $hosting_quality_data['stats']['least_reviewed_listing_id'] = (string) key(array_slice($listing_review_counters, -1, 1, true));
        $hosting_quality_data['stats']['most_reviewed_listing_count'] = (int) current($listing_review_counters);
        $hosting_quality_data['stats']['least_reviewed_listing_count'] = (int) end($listing_review_counters);

        // check if we need to map the listing IDs and names
        if (empty($hosting_quality_data['listings_map']) && !empty($options['host_id'])) {
            $dbo->setQuery(
                $dbo->getQuery(true)
                    ->select([
                        $dbo->qn('idroomota'),
                        $dbo->qn('otaroomname'),
                        $dbo->qn('prop_name'),
                    ])
                    ->from($dbo->qn('#__vikchannelmanager_roomsxref'))
                    ->where($dbo->qn('idchannel') . ' = ' . VikChannelManagerConfig::AIRBNBAPI)
                    ->where($dbo->qn('prop_params') . ' LIKE ' . $dbo->q('%' . $options['host_id'] . '%'))
            );
            $listings_map = $dbo->loadAssocList();
            $hosting_quality_data['listings_map'] = array_combine(array_column($listings_map, 'idroomota'), array_column($listings_map, 'otaroomname'));

            // set host name
            $hosting_quality_data['host_name'] = $hosting_quality_data['host_name'] ?: $listings_map[0]['prop_name'] ?? '';
        } elseif (!empty($options['host_id'])) {
            // fetch host name
            $dbo->setQuery(
                $dbo->getQuery(true)
                    ->select([
                        $dbo->qn('prop_name'),
                    ])
                    ->from($dbo->qn('#__vikchannelmanager_roomsxref'))
                    ->where($dbo->qn('idchannel') . ' = ' . VikChannelManagerConfig::AIRBNBAPI)
                    ->where($dbo->qn('prop_params') . ' LIKE ' . $dbo->q('%' . $options['host_id'] . '%'))
            , 0, 1);
            $host_name = $dbo->loadResult();

            if ($host_name) {
                // set host name
                $hosting_quality_data['host_name'] = $host_name;
            }
        }

        return $hosting_quality_data;
    }

    /**
     * Normalizes the listing trip issues and quality standards from the listing contents payload.
     * 
     * @param   array    $contents The listing content data payloads to normalize.
     * @param   ?array   $options  Associative list of options for normalizing data.
     * 
     * @return  array              Normalized listing trip issues and quality standards data payloads.
     * 
     * @since   1.9.14
     */
    public static function normalizeListingTripIssues(array $contents, ?array $options = null)
    {
        $listing_trip_issues = [
            'listings_map'       => [],
            'quality_standards'  => [],
            'reservation_issues' => [],
        ];

        foreach ($contents as $content) {
            if (!empty($content['setting']['id']) && !empty($content['setting']['name'])) {
                // map listing data
                $listing_trip_issues['listings_map'][$content['setting']['id']] = $content['setting']['name'];
            }

            // access listing ID
            $listing_id = $content['setting']['id'];

            if (empty($content['setting']['quality_standards']['state'])) {
                // nothing to report on quality
                continue;
            }

            // quality-standard state
            $quality_state = (string) $content['setting']['quality_standards']['state'];

            // push listing ID under the current quality state
            $listing_trip_issues['quality_standards'][$quality_state] = $listing_trip_issues['quality_standards'][$quality_state] ?? [];
            $listing_trip_issues['quality_standards'][$quality_state][] = $listing_id;

            if (is_array($content['setting']['reservation_issues'] ?? null) && $content['setting']['reservation_issues']) {
                // trip issues
                $listing_trip_issues['reservation_issues'][$listing_id] = [];
                foreach ($content['setting']['reservation_issues'] as $trip_issue) {
                    if (!empty($trip_issue['confirmation_code']) || !empty($trip_issue['review_issues']['review_id'])) {
                        // push trip issue details
                        $listing_trip_issues['reservation_issues'][$listing_id][] = [
                            'ota_reservation_id' => $trip_issue['confirmation_code'] ?? null,
                            'ota_review_id' => $trip_issue['review_issues']['review_id'] ?? null,
                            'low_overall_rating' => $trip_issue['review_issues']['low_overall_rating'] ?? null,
                            'cancellation' => $trip_issue['cancellation'] ?? null,
                        ];
                    }
                }

                if (!$listing_trip_issues['reservation_issues'][$listing_id]) {
                    // nothing reported as a trip issue
                    unset($listing_trip_issues['reservation_issues'][$listing_id]);
                }
            }
        }

        return $listing_trip_issues;
    }
}
