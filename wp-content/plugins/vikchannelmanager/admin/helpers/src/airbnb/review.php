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
 * Airbnb Review helper.
 * 
 * @since   1.9
 */
final class VCMAirbnbReview
{
    /**
     * Returns the map of the review category tags.
     * 
     * @param   string  $type       Optional type of tags to return.
     * @param   string  $category   Optional category of tags to return.
     * 
     * @return  array
     */
    public static function getCategoryTags($type = null, $category = null)
    {
        $map = [
            'host_review_guest_positive_neat_and_tidy' => [
                'category' => 'cleanliness',
                'descr' => 'Neat & tidy',
            ],
            'host_review_guest_positive_kept_in_good_condition' => [
                'category' => 'cleanliness',
                'descr' => 'Kept in good condition',
            ],
            'host_review_guest_positive_took_care_of_garbage' => [
                'category' => 'cleanliness',
                'descr' => 'Took care of garbage',
            ],
            'host_review_guest_negative_ignored_checkout_directions' => [
                'category' => 'cleanliness',
                'descr' => 'Ignored check-out directions',
            ],
            'host_review_guest_negative_garbage' => [
                'category' => 'cleanliness',
                'descr' => 'Excessive garbage',
            ],
            'host_review_guest_negative_messy_kitchen' => [
                'category' => 'cleanliness',
                'descr' => 'Messy kitchen',
            ],
            'host_review_guest_negative_damage' => [
                'category' => 'cleanliness',
                'descr' => 'Damaged property',
            ],
            'host_review_guest_negative_ruined_bed_linens' => [
                'category' => 'cleanliness',
                'descr' => 'Ruined bed linens',
            ],
            'host_review_guest_negative_arrived_early' => [
                'category' => 'respect_house_rules',
                'descr' => 'Arrived too early',
            ],
            'host_review_guest_negative_stayed_past_checkout' => [
                'category' => 'respect_house_rules',
                'descr' => 'Stayed past checkout',
            ],
            'host_review_guest_negative_unapproved_guests' => [
                'category' => 'respect_house_rules',
                'descr' => 'Unapproved guests',
            ],
            'host_review_guest_negative_unapproved_pet' => [
                'category' => 'respect_house_rules',
                'descr' => 'Unapproved pet',
            ],
            'host_review_guest_negative_did_not_respect_quiet_hours' => [
                'category' => 'respect_house_rules',
                'descr' => 'Didn’t respect quiet hours',
            ],
            'host_review_guest_negative_unapproved_filming' => [
                'category' => 'respect_house_rules',
                'descr' => 'Unapproved filming or photography',
            ],
            'host_review_guest_negative_unapproved_event' => [
                'category' => 'respect_house_rules',
                'descr' => 'Unapproved event',
            ],
            'host_review_guest_negative_smoking' => [
                'category' => 'respect_house_rules',
                'descr' => 'Smoking',
            ],
            'host_review_guest_positive_helpful_messages' => [
                'category' => 'communication',
                'descr' => 'Helpful messages',
            ],
            'host_review_guest_positive_respectful' => [
                'category' => 'communication',
                'descr' => 'Respectful',
            ],
            'host_review_guest_positive_always_responded' => [
                'category' => 'communication',
                'descr' => 'Always responded',
            ],
            'host_review_guest_negative_unhelpful_messages' => [
                'category' => 'communication',
                'descr' => 'Unhelpful responses',
            ],
            'host_review_guest_negative_disrespectful' => [
                'category' => 'communication',
                'descr' => 'Disrespectful',
            ],
            'host_review_guest_negative_unreachable' => [
                'category' => 'communication',
                'descr' => 'Unreachable',
            ],
            'host_review_guest_negative_slow_responses' => [
                'category' => 'communication',
                'descr' => 'Slow responses',
            ],
            'guest_review_host_positive_looked_like_photos' => [
                'category' => 'accuracy',
                'descr' => 'Looked like the photos',
            ],
            'guest_review_host_positive_matched_description' => [
                'category' => 'accuracy',
                'descr' => 'Matched the description',
            ],
            'guest_review_host_positive_had_listed_amenities_and_services' => [
                'category' => 'accuracy',
                'descr' => 'Had listed amenities & services',
            ],
            'guest_review_host_negative_smaller_than_expected' => [
                'category' => 'accuracy',
                'descr' => 'Smaller than expected',
            ],
            'guest_review_host_negative_did_not_match_photos' => [
                'category' => 'accuracy',
                'descr' => 'Didn’t match the photos',
            ],
            'guest_review_host_negative_needs_maintenance' => [
                'category' => 'accuracy',
                'descr' => 'Needs maintenance',
            ],
            'guest_review_host_negative_unexpected_fees' => [
                'category' => 'accuracy',
                'descr' => 'Unexpected fees',
            ],
            'guest_review_host_negative_excessive_rules' => [
                'category' => 'accuracy',
                'descr' => 'Excessive rules',
            ],
            'guest_review_host_negative_unexpected_noise' => [
                'category' => 'accuracy',
                'descr' => 'Unexpected noise',
            ],
            'guest_review_host_negative_inaccurate_location' => [
                'category' => 'accuracy',
                'descr' => 'Inaccurate location',
            ],
            'guest_review_host_negative_missing_amenity' => [
                'category' => 'accuracy',
                'descr' => 'Missing amenity or service',
            ],
            'guest_review_host_positive_responsive_host' => [
                'category' => 'checkin',
                'descr' => 'Responsive Host',
            ],
            'guest_review_host_positive_clear_instructions' => [
                'category' => 'checkin',
                'descr' => 'Clear instructions',
            ],
            'guest_review_host_positive_easy_to_find' => [
                'category' => 'checkin',
                'descr' => 'Easy to find',
            ],
            'guest_review_host_positive_easy_to_get_inside' => [
                'category' => 'checkin',
                'descr' => 'Easy to get inside',
            ],
            'guest_review_host_positive_flexible_check_in' => [
                'category' => 'checkin',
                'descr' => 'Flexible check-in',
            ],
            'guest_review_host_negative_hard_to_locate' => [
                'category' => 'checkin',
                'descr' => 'Hard to locate',
            ],
            'guest_review_host_negative_unclear_instructions' => [
                'category' => 'checkin',
                'descr' => 'Unclear instructions',
            ],
            'guest_review_host_negative_trouble_with_lock' => [
                'category' => 'checkin',
                'descr' => 'Trouble with lock',
            ],
            'guest_review_host_negative_unresponsive_host' => [
                'category' => 'checkin',
                'descr' => 'Unresponsive Host',
            ],
            'guest_review_host_negative_had_to_wait' => [
                'category' => 'checkin',
                'descr' => 'Had to wait',
            ],
            'guest_review_host_negative_hard_to_get_inside' => [
                'category' => 'checkin',
                'descr' => 'Hard to get inside',
            ],
            'guest_review_host_positive_felt_at_home' => [
                'category' => 'checkin',
                'descr' => 'Felt right at home',
            ],
            'guest_review_host_positive_spotless_furniture_and_linens' => [
                'category' => 'cleanliness',
                'descr' => 'Spotless furniture & linens',
            ],
            'guest_review_host_positive_free_of_clutter' => [
                'category' => 'cleanliness',
                'descr' => 'Free of clutter',
            ],
            'guest_review_host_positive_squeaky_clean_bathroom' => [
                'category' => 'cleanliness',
                'descr' => 'Squeaky-clean bathroom',
            ],
            'guest_review_host_positive_pristine_kitchen' => [
                'category' => 'cleanliness',
                'descr' => 'Pristine kitchen',
            ],
            'guest_review_host_negative_dirty_or_dusty' => [
                'category' => 'cleanliness',
                'descr' => 'Dirty or dusty',
            ],
            'guest_review_host_negative_noticeable_smell' => [
                'category' => 'cleanliness',
                'descr' => 'Noticeable smell',
            ],
            'guest_review_host_negative_stains' => [
                'category' => 'cleanliness',
                'descr' => 'Stains',
            ],
            'guest_review_host_negative_excessive_clutter' => [
                'category' => 'cleanliness',
                'descr' => 'Excessive clutter',
            ],
            'guest_review_host_negative_messy_kitchen' => [
                'category' => 'cleanliness',
                'descr' => 'Messy kitchen',
            ],
            'guest_review_host_negative_hair_or_pet_hair' => [
                'category' => 'cleanliness',
                'descr' => 'Hair or pet hair',
            ],
            'guest_review_host_negative_dirty_bathroom' => [
                'category' => 'cleanliness',
                'descr' => 'Dirty bathroom',
            ],
            'guest_review_host_negative_trash_left_behind' => [
                'category' => 'cleanliness',
                'descr' => 'Trash left behind',
            ],
            'guest_review_host_negative_broken_or_missing_lock' => [
                'category' => 'accuracy',
                'descr' => 'Broken or missing lock on door',
            ],
            'guest_review_host_negative_unexpected_guests' => [
                'category' => 'accuracy',
                'descr' => 'Unexpected guest in space',
            ],
            'guest_review_host_negative_incorrect_bathroom' => [
                'category' => 'accuracy',
                'descr' => 'Incorrect bathroom type',
            ],
            'guest_review_host_positive_always_responsive' => [
                'category' => 'communication',
                'descr' => 'Always responsive',
            ],
            'guest_review_host_positive_local_recommendations' => [
                'category' => 'communication',
                'descr' => 'Local recommendations',
            ],
            'guest_review_host_positive_proactive' => [
                'category' => 'communication',
                'descr' => 'Proactive',
            ],
            'guest_review_host_positive_helpful_instructions' => [
                'category' => 'communication',
                'descr' => 'Helpful instructions',
            ],
            'guest_review_host_positive_considerate' => [
                'category' => 'communication',
                'descr' => 'Considerate',
            ],
            'guest_review_host_negative_slow_to_respond' => [
                'category' => 'communication',
                'descr' => 'Slow to respond',
            ],
            'guest_review_host_negative_not_helpful' => [
                'category' => 'communication',
                'descr' => 'Not helpful',
            ],
            'guest_review_host_negative_missing_house_instructions' => [
                'category' => 'communication',
                'descr' => 'Missing house instructions',
            ],
            'guest_review_host_negative_unclear_checkout_tasks' => [
                'category' => 'communication',
                'descr' => 'Unclear checkout tasks',
            ],
            'guest_review_host_negative_inconsiderate' => [
                'category' => 'communication',
                'descr' => 'Inconsiderate',
            ],
            'guest_review_host_negative_excessive_checkout_tasks' => [
                'category' => 'communication',
                'descr' => 'Excessive checkout tasks',
            ],
            'guest_review_host_positive_peaceful' => [
                'category' => 'location',
                'descr' => 'Peaceful',
            ],
            'guest_review_host_positive_beautiful_surroundings' => [
                'category' => 'location',
                'descr' => 'Beautiful surroundings',
            ],
            'guest_review_host_positive_private' => [
                'category' => 'location',
                'descr' => 'Private',
            ],
            'guest_review_host_positive_great_restaurants' => [
                'category' => 'location',
                'descr' => 'Great restaurants',
            ],
            'guest_review_host_positive_lots_to_do' => [
                'category' => 'location',
                'descr' => 'Lots to do',
            ],
            'guest_review_host_positive_walkable' => [
                'category' => 'location',
                'descr' => 'Walkable',
            ],
            'guest_review_host_negative_noisy' => [
                'category' => 'location',
                'descr' => 'Noisy',
            ],
            'guest_review_host_negative_not_much_to_do' => [
                'category' => 'location',
                'descr' => 'Not much to do',
            ],
            'guest_review_host_negative_bland_surroundings' => [
                'category' => 'location',
                'descr' => 'Bland surroundings',
            ],
            'guest_review_host_negative_not_private' => [
                'category' => 'location',
                'descr' => 'Not private',
            ],
            'guest_review_host_negative_inconvenient_location' => [
                'category' => 'location',
                'descr' => 'Inconvenient location',
            ],
            'accuracy_other' => [
                'category' => 'accuracy',
                'descr' => 'accuracy "other"',
            ],
            'check_in_other' => [
                'category' => 'checkin',
                'descr' => 'checkin "other"',
            ],
            'cleanliness_other' => [
                'category' => 'cleanliness',
                'descr' => 'cleanliness "other"',
            ],
            'communication_other' => [
                'category' => 'communication',
                'descr' => 'communication "other"',
            ],
            'location_other' => [
                'category' => 'location',
                'descr' => 'location "other"',
            ],
            'respect_house_rules_other' => [
                'category' => 'respect_house_rules',
                'descr' => 'respect house rules "other"',
            ],
        ];

        // check for type filter
        if ($type) {
            if (!strcasecmp($type, 'guest_review_host')) {
                // filter by category tags on a guest review for the host
                $map = array_filter($map, function($key) {
                    return strpos($key, 'guest_review_host') === 0;
                }, ARRAY_FILTER_USE_KEY);
            } elseif (!strcasecmp($type, 'host_review_guest')) {
                // filter by category tags on a host-to-guest review
                $map = array_filter($map, function($key) {
                    return strpos($key, 'host_review_guest') === 0;
                }, ARRAY_FILTER_USE_KEY);
            }
        }

        // check for category filter
        if ($category) {
            $map = array_filter($map, function($cat_tag) use ($category) {
                return !strcasecmp($cat_tag['category'] ?? '', $category);
            });
        }

        return $map;
    }

    /**
     * Returns the description for the given review category tag, if any.
     * 
     * @param   string  $tag  The review category tag to look for.
     * 
     * @return  string        The description found or an empty string.
     */
    public static function getCategoryTagDescription($tag)
    {
        $map = static::getCategoryTags();

        $info = $map[$tag] ?? [];

        return $info['descr'] ?? '';
    }

    /**
     * Returns the category for the given review category tag, if any.
     * 
     * @param   string  $tag  The review category tag to look for.
     * 
     * @return  string        The category found or an empty string.
     * 
     * @since   1.9.14
     */
    public static function getCategoryTagCategory($tag)
    {
        $map = static::getCategoryTags();

        $info = $map[$tag] ?? [];

        return ucwords($info['category'] ?? '');
    }

    /**
     * Returns the map of the quality status enumerations or a value of one status.
     * 
     * @param   ?string     $status     Optional status data to fetch.
     * @param   ?string     $data       Optional status data to fetch.
     * 
     * @return  array|string            Entire map, status array or status string data.
     * 
     * @since   1.9.14
     */
    public static function getQualityStatusEnums(?string $status = null, ?string $data = null)
    {
        $map = [
            'good' => [
                'name'   => 'Good',
                'descr'  => 'No quality issues reported.',
                'impact' => 'success',
                'group'  => 'Good',
            ],
            'educate' => [
                'name'   => 'Education',
                'descr'  => 'A quality issue has been reported. Host should review guidelines and educational materials.',
                'impact' => 'success',
                'group'  => 'Good or Education',
            ],
            'warn' => [
                'name'   => 'Warning',
                'descr'  => 'A few quality issues have been reported. Host should review our guidelines again',
                'impact' => 'warning',
                'group'  => 'Warning',
            ],
            'probation' => [
                'name'   => 'Probation',
                'descr'  => 'A few quality issues have been reported. Listing is at risk of suspension soon. Host should review our guidelines again.',
                'impact' => 'warning',
                'group'  => 'Warning',
            ],
            'additional_warn' => [
                'name'   => 'Additional Warning',
                'descr'  => 'Too many quality issues have been reported. The listing is at risk for removal.',
                'impact' => 'error',
                'group'  => 'Risk of suspension or removal',
            ],
            'pending_removal' => [
                'name'   => 'Pending Removal',
                'descr'  => 'Too many quality issues have been reported. The listing is marked for removal after 30 days.',
                'impact' => 'error',
                'group'  => 'Risk of suspension or removal',
            ],
            'suspended' => [
                'name'   => 'Suspended',
                'descr'  => 'Too many quality issues have been reported. The listing has been suspended until hosts reviews Airbnb guidelines again.',
                'impact' => 'neutral',
                'group'  => 'Listings removed',
            ],
            'removed' => [
                'name'   => 'Removed',
                'descr'  => 'Too many quality issues have been reported. The listing has been removed from Airbnb.',
                'impact' => 'neutral',
                'group'  => 'Listings removed',
            ],
        ];

        if (!$status) {
            return $map;
        }

        if (!$data) {
            return $map[$status] ?? [];
        }

        return $map[$status][$data] ?? '';
    }

    /**
     * Returns a list of quality status groups data.
     * 
     * @param   bool    $positive   True to include also the positive statuses.
     * 
     * @return  array               List of quality status groups data.
     * 
     * @since   1.9.14
     */
    public static function getQualityStatusGroups(bool $positive = false)
    {
        $statuses = static::getQualityStatusEnums();

        if (!$positive) {
            unset($statuses['good']);
        }

        $groupsData   = [];
        $groupNames   = array_values(array_unique(array_column($statuses, 'group')));
        $groupImpacts = array_values(array_unique(array_column($statuses, 'impact')));

        foreach ($groupNames as $k => $groupName) {
            $groupData = [
                'name' => $groupName,
                'impact' => $groupImpacts[$k] ?? 'neutral',
                'statuses' => [],
            ];
            foreach ($statuses as $enum => $status) {
                if ($status['group'] == $groupName) {
                    // push status enum to this group
                    $groupData['statuses'][$enum] = $status['name'];
                }
            }
            $groupsData[] = $groupData;
        }

        return $groupsData;
    }
}
