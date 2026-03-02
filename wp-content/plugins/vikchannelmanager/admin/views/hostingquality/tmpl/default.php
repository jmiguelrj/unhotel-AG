<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2025 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

// build listing elements
$listingElements = [];
foreach ($this->listings_map as $listing_id => $listing_name) {
    $listingElements[] = [
        // cast ID to string to support large integers
        'id' => (string) $listing_id,
        'name' => $listing_name,
    ];
}

?>

<div class="vcm-hosting-quality-dashboard-wrap">

    <form action="index.php?option=com_vikchannelmanager&view=hostingquality" method="post" name="adminForm" id="adminForm">
        <div class="vcm-advanced-toolbar">
            <div class="vcm-advanced-toolbar-header">
                <div class="vcm-advanced-toolbar-account">
                <?php
                if (!empty($this->property_score['data']['overall_rating']['summary']['logo_url'])) {
                    ?>
                    <span class="vcm-host-account-pic">
                        <img class="vcm-host-account-profilepic" src="<?php echo $this->property_score['data']['overall_rating']['summary']['logo_url']; ?>" />
                    </span>
                    <?php
                }
                ?>
                    <span class="vcm-host-name"><?php echo $this->hosting_quality_data['host_name'] ?? ''; ?></span>
                    <span class="vcm-host-id">(ID <?php echo $this->hosting_quality_data['host_id'] ?? ''; ?>)</span>
                </div>
                <div class="vcm-advanced-toolbar-account-switch">
                    <select name="filters[host_id]">
                    <?php
                    foreach ($this->host_accounts as $host_account_id => $host_account_name) {
                        ?>
                        <option value="<?php echo $host_account_id; ?>"<?php echo $host_account_id == ($this->hosting_quality_data['host_id'] ?? '') ? ' selected="selected"' : ''; ?>><?php echo $host_account_name; ?></option>
                        <?php
                    }
                    ?>
                    </select>
                </div>
            </div>
            <div class="vcm-advanced-toolbar-filters">
                <div class="vcm-advanced-toolbar-filter<?php echo !empty($this->filters['listing_id']) ? ' vcm-filter-active' : ''; ?>">
                <?php
                echo VikBooking::getVboApplication()->renderElementsDropDown([
                    'placeholder'     => JText::_('VCM_AIRBNB_LISTINGS'),
                    'allow_clear'     => true,
                    'attributes'      => [
                        'name' => 'filters[listing_id]',
                    ],
                    'selected_value'  => $this->filters['listing_id'] ?? null,
                ], $listingElements);
                ?>
                </div>
            <?php
            // filter by review category
            if ($this->hosting_quality_data['info']['review_categories']) {
                $reviewCategories = [];
                foreach ($this->hosting_quality_data['info']['review_categories'] as $reviewCategory) {
                    $reviewCategories[] = [
                        'id' => $reviewCategory,
                        'name' => ucwords(implode(' ', explode('_', strtolower($reviewCategory)))),
                    ];
                }
                ?>
                <div class="vcm-advanced-toolbar-filter<?php echo !empty($this->filters['review_category']) ? ' vcm-filter-active' : ''; ?>">
                <?php
                echo VikBooking::getVboApplication()->renderElementsDropDown([
                    'placeholder'     => JText::_('VCM_CATEGORY_RATINGS'),
                    'allow_clear'     => true,
                    'attributes'      => [
                        'name' => 'filters[review_category]',
                    ],
                    'selected_value'  => $this->filters['review_category'] ?? null,
                ], $reviewCategories);
                ?>
                </div>
                <?php
            }

            // filter by review category tag
            $category_tags_groups = [
                [
                    'text' => JText::_('VCMGREVPOSITIVE'),
                    'elements' => [],
                ],
                [
                    'text' => JText::_('VCMGREVNEGATIVE'),
                    'elements' => [],
                ],
            ];
            foreach ($this->hosting_quality_data['category_tags_map'] as $categoryTagEnum => $categoryTagName) {
                if (preg_match('/^guest_review_host_(positive|negative)/i', $categoryTagEnum, $matches)) {
                    if (strtolower($matches[1] === 'positive')) {
                        // positive review category tag
                        $category_tags_groups[0]['elements'][] = [
                            'id' => $categoryTagEnum,
                            'text' => $categoryTagName,
                        ];
                    } else {
                        // negative review category tag
                        $category_tags_groups[1]['elements'][] = [
                            'id' => $categoryTagEnum,
                            'text' => $categoryTagName,
                        ];
                    }
                }
            }
            if ($category_tags_groups[0]['elements'] || $category_tags_groups[1]['elements']) {
                ?>
                <div class="vcm-advanced-toolbar-filter<?php echo !empty($this->filters['category_tag']) ? ' vcm-filter-active' : ''; ?>">
                <?php
                echo VikBooking::getVboApplication()->renderElementsDropDown([
                    'placeholder'     => JText::_('VCM_GUEST_FEEDBACK'),
                    'allow_clear'     => true,
                    'attributes'      => [
                        'name' => 'filters[category_tag]',
                    ],
                    'selected_value'  => $this->filters['category_tag'] ?? null,
                ], [], $category_tags_groups);
                ?>
                </div>
                <?php
            }
            ?>
                <div class="vcm-advanced-toolbar-submit">
                    <input type="submit" class="btn" value="<?php echo JHtml::_('esc_attr', JText::_('VCMBCAHSUBMIT')); ?>" />
                </div>
            </div>
        </div>
    </form>

	<div class="vcm-dashboard-scorecard-airbnbapi vcm-hosting-quality-dashboard-container">

        <div class="vcm-hosting-quality-tot-listings">
            <?php VikBookingIcons::e('home'); ?>
        <?php
        if (empty($this->filters['listing_id'])) {
            ?>
            <span><?php echo JText::sprintf('VCM_N_LISTINGS', $this->hosting_quality_data['info']['tot_filtered_listings'] ?? $this->hosting_quality_data['info']['tot_listings'] ?? 0); ?></span>
            <?php
        } else {
            ?>
            <span><?php echo $this->listings_map[$this->filters['listing_id']] ?? $this->filters['listing_id']; ?></span>
            <?php
        }
        if (empty($this->filters['listing_id']) && !empty($this->hosting_quality_data['info']['tot_filtered_listings']) && $this->hosting_quality_data['info']['tot_filtered_listings'] < ($this->hosting_quality_data['info']['tot_listings'] ?? 0)) {
            // display the involved listing names
            ?>
            <div class="vcm-hosting-quality-listings-list">
                <ul>
                <?php
                foreach ($this->hosting_quality_data['info']['filtered_listing_ids'] as $listing_id) {
                    ?>
                    <li>
                        <a href="index.php?option=com_vikchannelmanager&view=hostingquality&filters[listing_id]=<?php echo $listing_id; ?>"><?php echo $this->listings_map[$listing_id] ?? $listing_id; ?></a>
                    </li>
                    <?php
                }
                ?>
                </ul>
            </div>
            <?php
        }
        ?>
        </div>

		<div class="vcm-hosting-quality-dashboard-section" data-type="review-stats">
			<div class="vcm-hosting-quality-blocks">

				<div class="vcm-hosting-quality-block" data-type="ratings">
					<div class="vcm-hosting-quality-block-title"><?php VikBookingIcons::e('star'); ?> <span><?php echo JText::_('VCM_RATINGS'); ?></span></div>
					<div class="vcm-hosting-quality-block-stats">
						<div class="vcm-hosting-quality-block-stat" data-impact="<?php echo VCMAirbnbContent::getValueImpactEnum($this->hosting_quality_data['stats']['best_rating_listing_score'] ?? 0); ?>">
							<div class="vcm-hosting-quality-block-stat-text">
								<div class="vcm-hosting-quality-block-stat-text-main"><?php echo $this->hosting_quality_data['listings_map'][$this->hosting_quality_data['stats']['best_rating_listing_id']] ?? $this->hosting_quality_data['stats']['best_rating_listing_id']; ?></div>
                            <?php
                            if (empty($this->filters['listing_id'])) {
                                ?>
								<div class="vcm-hosting-quality-block-stat-text-sub"><?php echo JText::_('VCM_BEST_RATING'); ?></div>
                                <?php
                            }
                            ?>
							</div>
							<div class="vcm-hosting-quality-block-stat-score">
								<div class="vcm-hosting-quality-block-stat-score-main"><?php echo $this->hosting_quality_data['stats']['best_rating_listing_score'] ?? '?'; ?><span class="vcm-hosting-quality-score-submain">/5</span></div>
							</div>
						</div>
                    <?php
                    if (empty($this->filters['listing_id'])) {
                        ?>
						<div class="vcm-hosting-quality-block-stat" data-impact="<?php echo VCMAirbnbContent::getValueImpactEnum($this->hosting_quality_data['stats']['worst_rating_listing_score'] ?? 0); ?>">
							<div class="vcm-hosting-quality-block-stat-text">
								<div class="vcm-hosting-quality-block-stat-text-main"><?php echo $this->hosting_quality_data['listings_map'][$this->hosting_quality_data['stats']['worst_rating_listing_id']] ?? $this->hosting_quality_data['stats']['worst_rating_listing_id']; ?></div>
								<div class="vcm-hosting-quality-block-stat-text-sub"><?php echo JText::_('VCM_WORST_RATING'); ?></div>
							</div>
							<div class="vcm-hosting-quality-block-stat-score">
								<div class="vcm-hosting-quality-block-stat-score-main"><?php echo $this->hosting_quality_data['stats']['worst_rating_listing_score'] ?? '?'; ?><span class="vcm-hosting-quality-score-submain">/5</span></div>
							</div>
						</div>
                        <?php
                    }
                    ?>
					</div>
				</div>

				<div class="vcm-hosting-quality-block" data-type="reviews">
					<div class="vcm-hosting-quality-block-title">
						<?php VikBookingIcons::e('comments'); ?> 
						<span><?php echo JText::_('VCMMENUREVIEWS'); ?></span>
						<span class="vcm-hosting-quality-block-title-sub"><?php echo !empty($this->filters['listing_id']) ? ($this->hosting_quality_data['stats']['most_reviewed_listing_count'] ?? 0) : ($this->hosting_quality_data['info']['tot_reviews'] ?? 0); ?></span>
					</div>
					<div class="vcm-hosting-quality-block-stats">
						<div class="vcm-hosting-quality-block-stat" data-impact="<?php echo VCMAirbnbContent::getValueImpactEnum($this->hosting_quality_data['stats']['most_reviewed_listing_count'] ?? 0, ['succes' => 50, 'warning' => 20, 'neutral' => 9]); ?>">
							<div class="vcm-hosting-quality-block-stat-text">
								<div class="vcm-hosting-quality-block-stat-text-main"><?php echo $this->hosting_quality_data['listings_map'][$this->hosting_quality_data['stats']['most_reviewed_listing_id']] ?? $this->hosting_quality_data['stats']['most_reviewed_listing_id']; ?></div>
                            <?php
                            if (empty($this->filters['listing_id'])) {
                                ?>
								<div class="vcm-hosting-quality-block-stat-text-sub"><?php echo JText::_('VCM_MAXIMUM'); ?></div>
                                <?php
                            }
                            ?>
							</div>
							<div class="vcm-hosting-quality-block-stat-score">
								<div class="vcm-hosting-quality-block-stat-score-main"><?php echo $this->hosting_quality_data['stats']['most_reviewed_listing_count'] ?? '?'; ?></div>
							</div>
						</div>
                    <?php
                    if (empty($this->filters['listing_id'])) {
                        ?>
						<div class="vcm-hosting-quality-block-stat" data-impact="<?php echo VCMAirbnbContent::getValueImpactEnum($this->hosting_quality_data['stats']['least_reviewed_listing_count'] ?? 0, ['succes' => 50, 'warning' => 20, 'neutral' => 9]); ?>">
							<div class="vcm-hosting-quality-block-stat-text">
								<div class="vcm-hosting-quality-block-stat-text-main"><?php echo $this->hosting_quality_data['listings_map'][$this->hosting_quality_data['stats']['least_reviewed_listing_id']] ?? $this->hosting_quality_data['stats']['least_reviewed_listing_id']; ?></div>
								<div class="vcm-hosting-quality-block-stat-text-sub"><?php echo JText::_('VCM_MINIMUM'); ?></div>
							</div>
							<div class="vcm-hosting-quality-block-stat-score">
								<div class="vcm-hosting-quality-block-stat-score-main"><?php echo $this->hosting_quality_data['stats']['least_reviewed_listing_count'] ?? '?'; ?></div>
							</div>
						</div>
                        <?php
                    }
                    ?>
					</div>
				</div>

				<?php
				// review categories
				?>
				<div class="vcm-hosting-quality-block" data-type="review-categories">
					<div class="vcm-hosting-quality-block-title"><?php VikBookingIcons::e('chart-line'); ?> <span><?php echo JText::_('VCM_CATEGORY_RATINGS'); ?></span></div>
					<div class="vcm-hosting-quality-block-stats">
                    <?php
                    $counter = 0;
                    $tot_rank_review_cats = count($this->hosting_quality_data['stats']['rank_review_categories'] ?? []);
                    foreach (($this->hosting_quality_data['stats']['rank_review_categories'] ?? []) as $categoryName => $categoryScore) {
                        ?>
                        <div class="vcm-hosting-quality-block-stat" data-impact="<?php echo VCMAirbnbContent::getValueImpactEnum($categoryScore); ?>">
                            <div class="vcm-hosting-quality-block-stat-text">
                                <div class="vcm-hosting-quality-block-stat-text-main"><?php echo ucwords($categoryName); ?></div>
                                <div class="vcm-hosting-quality-block-stat-text-sub"><?php
                                if (!$counter) {
                                    echo JText::_('VCM_BEST_RATING');
                                } elseif ($counter + 1 === $tot_rank_review_cats) {
                                    echo JText::_('VCM_WORST_RATING');
                                }
                                ?></div>
                            </div>
                            <div class="vcm-hosting-quality-block-stat-score">
                                <div class="vcm-hosting-quality-block-stat-score-main"><?php echo $categoryScore; ?><span class="vcm-hosting-quality-score-submain">/5</span></div>
                            </div>
                        </div>
                        <?php
                        $counter++;
                    }
                    ?>
					</div>
				</div>

                <?php
                // review category tags
                ?>
                <div class="vcm-hosting-quality-block" data-type="review-category-tags">
                    <div class="vcm-hosting-quality-block-title"><?php VikBookingIcons::e('thumbs-up'); ?> <span><?php echo JText::_('VCM_GUEST_FEEDBACK'); ?></span></div>
                    <div class="vcm-hosting-quality-block-stats">
                    <?php
                    $counter = 0;
                    foreach (($this->hosting_quality_data['stats']['top_positive_category_tags'] ?? []) as $tagName => $tagCount) {
                        $lowerTagName = strtolower($tagName);
                        $useTagName = $this->hosting_quality_data['category_tags_map'][$lowerTagName] ?? ucwords(implode(' ', explode('_', $lowerTagName)));
                        ?>
                        <div class="vcm-hosting-quality-block-stat" data-impact="success">
                            <div class="vcm-hosting-quality-block-stat-text">
                                <div class="vcm-hosting-quality-block-stat-text-main"><?php echo $useTagName; ?></div>
                                <div class="vcm-hosting-quality-block-stat-text-sub"><?php echo VCMAirbnbReview::getCategoryTagCategory($lowerTagName); ?></div>
                            </div>
                            <div class="vcm-hosting-quality-block-stat-score">
                                <div class="vcm-hosting-quality-block-stat-score-main"><?php echo $tagCount; ?></div>
                                <div class="vcm-hosting-quality-block-stat-score-sub"><?php echo JText::_('VCM_TIMES'); ?></div>
                            </div>
                        </div>
                        <?php
                        if (++$counter === 3) {
                            break;
                        }
                    }
                    $counter = 0;
                    foreach (($this->hosting_quality_data['stats']['top_negative_category_tags'] ?? []) as $tagName => $tagCount) {
                        $lowerTagName = strtolower($tagName);
                        $useTagName = $this->hosting_quality_data['category_tags_map'][$lowerTagName] ?? ucwords(implode(' ', explode('_', $lowerTagName)));
                        ?>
                        <div class="vcm-hosting-quality-block-stat" data-impact="error">
                            <div class="vcm-hosting-quality-block-stat-text">
                                <div class="vcm-hosting-quality-block-stat-text-main"><?php echo $useTagName; ?></div>
                                <div class="vcm-hosting-quality-block-stat-text-sub"><?php echo VCMAirbnbReview::getCategoryTagCategory($lowerTagName); ?></div>
                            </div>
                            <div class="vcm-hosting-quality-block-stat-score">
                                <div class="vcm-hosting-quality-block-stat-score-main"><?php echo $tagCount; ?></div>
                                <div class="vcm-hosting-quality-block-stat-score-sub"><?php echo JText::_('VCM_TIMES'); ?></div>
                            </div>
                        </div>
                        <?php
                        if (++$counter === 3) {
                            break;
                        }
                    }
                    ?>
                    </div>
                </div>

            <?php
            // chart - positive review category tags
            $positiveTagsDataSets = [
                'labels' => array_map(function($tagName) {
                    return $this->hosting_quality_data['category_tags_map'][strtolower($tagName)] ?? $tagName;
                }, array_keys($this->hosting_quality_data['stats']['top_positive_category_tags'] ?? [])),
                'datasets' => [
                    [
                        'label' => sprintf('%s (%s)', JText::_('VCM_CATEGORY_RATINGS'), JText::_('VCMGREVPOSITIVE')),
                        'backgroundColor' => 'rgba(65, 190, 110, 0.6)',
                        'borderColor' => 'rgba(65, 190, 110, 1)',
                        'data' => array_values($this->hosting_quality_data['stats']['top_positive_category_tags'] ?? []),
                    ],
                ],
            ];
            if ($positiveTagsDataSets['labels']) {
                // display chart
                ?>
                <div class="vcm-hosting-quality-block" data-type="chart-positive-tags">
                    <div class="vcm-hosting-quality-block-title">
                        <?php VikBookingIcons::e('chart-line'); ?> 
                        <span><?php echo JText::_('VCM_GUEST_FEEDBACK') . ' - ' . JText::_('VCMGREVPOSITIVE'); ?></span>
                        <span class="vcm-hosting-quality-block-title-sub"><?php echo array_sum($this->hosting_quality_data['stats']['top_positive_category_tags'] ?? []); ?></span>
                    </div>
                    <div class="vcm-hosting-quality-block-stats">
                        <canvas id="vcm-chart-positive-tags"></canvas>
                    </div>
                </div>
                <script>
                    VBOCore.DOMLoaded(() => {
                        let ctx = document.getElementById('vcm-chart-positive-tags').getContext('2d');
                        let chart = new Chart(ctx, {
                            type: 'horizontalBar',
                            data: {
                                labels: <?php echo json_encode($positiveTagsDataSets['labels']); ?>,
                                datasets: <?php echo json_encode($positiveTagsDataSets['datasets']); ?>,
                            },
                            options: {
                                legend: {
                                    display: false,
                                },
                                scales: {
                                    xAxes: [{
                                        ticks: {
                                            beginAtZero: true,
                                        },
                                    }],
                                },
                                tooltips: {
                                    callbacks: {
                                        label: (item, value) => {
                                            return item.value + ' ' + <?php echo json_encode(JText::_('VCM_TIMES')); ?>;
                                        },
                                    },
                                },
                            },
                        });
                    });
                </script>
                <?php
            }

            // chart - negative review category tags
            $negativeTagsDataSets = [
                'labels' => array_map(function($tagName) {
                    return $this->hosting_quality_data['category_tags_map'][strtolower($tagName)] ?? $tagName;
                }, array_keys($this->hosting_quality_data['stats']['top_negative_category_tags'] ?? [])),
                'datasets' => [
                    [
                        'label' => sprintf('%s (%s)', JText::_('VCM_CATEGORY_RATINGS'), JText::_('VCMGREVNEGATIVE')),
                        'backgroundColor' => 'rgba(212, 43, 46, 0.6)',
                        'borderColor' => 'rgba(212, 43, 46, 1)',
                        'data' => array_values($this->hosting_quality_data['stats']['top_negative_category_tags'] ?? []),
                    ],
                ],
            ];
            if ($negativeTagsDataSets['labels']) {
                // display chart
                $negativeTagsDataSets['datasets'][0]['data'] = array_map(function($val) {
                    return $val * -1;
                }, $negativeTagsDataSets['datasets'][0]['data']);
                ?>
                <div class="vcm-hosting-quality-block" data-type="chart-negative-tags">
                    <div class="vcm-hosting-quality-block-title">
                        <?php VikBookingIcons::e('chart-line'); ?> 
                        <span><?php echo JText::_('VCM_GUEST_FEEDBACK') . ' - ' . JText::_('VCMGREVNEGATIVE'); ?></span>
                        <span class="vcm-hosting-quality-block-title-sub"><?php echo array_sum($this->hosting_quality_data['stats']['top_negative_category_tags'] ?? []); ?></span>
                    </div>
                    <div class="vcm-hosting-quality-block-stats">
                        <canvas id="vcm-chart-negative-tags"></canvas>
                    </div>
                </div>
                <script>
                    VBOCore.DOMLoaded(() => {
                        let ctx = document.getElementById('vcm-chart-negative-tags').getContext('2d');
                        let chart = new Chart(ctx, {
                            type: 'horizontalBar',
                            data: {
                                labels: <?php echo json_encode($negativeTagsDataSets['labels']); ?>,
                                datasets: <?php echo json_encode($negativeTagsDataSets['datasets']); ?>,
                            },
                            options: {
                                legend: {
                                    display: false,
                                },
                                scales: {
                                    yAxes: [{
                                        position: 'right',
                                    }],
                                    xAxes: [{
                                        ticks: {
                                            beginAtZero: true,
                                            callback: (value) => {
                                                return Math.abs(value);
                                            },
                                        },
                                    }],
                                },
                                tooltips: {
                                    callbacks: {
                                        label: (item, value) => {
                                            return Math.abs(item.value) + ' ' + <?php echo json_encode(JText::_('VCM_TIMES')); ?>;
                                        },
                                    },
                                },
                            },
                        });
                    });
                </script>
                <?php
            }
            ?>

			</div>
		</div>

        <div class="vcm-hosting-quality-dashboard-section" data-type="trip-issues">
            <div class="vcm-hosting-quality-blocks">

            <?php
            // quality status groups
            foreach (VCMAirbnbReview::getQualityStatusGroups() as $qualityStatusGroup) {
                $groupIcon = 'hand-paper';
                if ($qualityStatusGroup['impact'] == 'success') {
                    $groupIcon = 'check';
                } elseif ($qualityStatusGroup['impact'] == 'warning') {
                    $groupIcon = 'exclamation-circle';
                } elseif ($qualityStatusGroup['impact'] == 'error') {
                    $groupIcon = 'exclamation-triangle';
                }
                $groupAffectedListingsCount = 0;
                $groupAffectedListingIds = [];
                foreach (array_keys($qualityStatusGroup['statuses']) as $statusEnum) {
                    $useStatusEnum = strtoupper($statusEnum);
                    $groupAffectedListingsCount += count($this->listing_quality_issues['quality_standards'][$useStatusEnum] ?? []);
                    $groupAffectedListingIds = array_merge($groupAffectedListingIds, $this->listing_quality_issues['quality_standards'][$useStatusEnum] ?? []);
                }
                ?>
                <div class="vcm-hosting-quality-block vcm-hosting-quality-status-block">
                    <div class="vcm-hosting-quality-block-title"><?php VikBookingIcons::e($groupIcon); ?> <span><?php echo $qualityStatusGroup['name']; ?></span></div>
                    <div class="vcm-hosting-quality-block-stats">
                        <div class="vcm-hosting-quality-block-stat" data-impact="<?php echo $qualityStatusGroup['impact']; ?>">
                            <div class="vcm-hosting-quality-block-stat-text">
                                <div class="vcm-hosting-quality-block-stat-text-main"><?php echo implode(', ', array_values($qualityStatusGroup['statuses'])); ?></div>
                            <?php
                            $groupAffectedListingNamesHtml = array_map(function($parseListingId) {
                                return '<span class="vcm-hosting-quality-listing-target">' . ($this->listing_quality_issues['listings_map'][$parseListingId] ?? $parseListingId) . '</span>';
                            }, $groupAffectedListingIds);
                                ?>
                                <div class="vcm-hosting-quality-block-stat-text-sub"><?php echo implode("\n", $groupAffectedListingNamesHtml); ?></div>
                            </div>
                            <div class="vcm-hosting-quality-block-stat-score">
                                <div class="vcm-hosting-quality-block-stat-score-main">
                                    <?php echo $groupAffectedListingsCount; ?>
                                    <span class="vcm-hosting-quality-score-submain">/<?php echo !empty($this->filters['listing_id']) ? 1 : $this->hosting_quality_data['info']['tot_filtered_listings'] ?? $this->hosting_quality_data['info']['tot_listings'] ?? 0; ?></span>
                                </div>
                                <div class="vcm-hosting-quality-block-stat-score-sub"><?php echo JText::_('VCM_AIRBNB_LISTINGS'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }

            // chart - listings score ranking
            $listingsScoreRankingDataSets = [
                'labels' => array_map(function($parseListingId) {
                    return $this->hosting_quality_data['listings_map'][$parseListingId] ?? $this->listing_quality_issues['listings_map'][$parseListingId] ?? $parseListingId;
                }, array_keys($this->hosting_quality_data['stats']['rank_listing_scores'] ?? [])),
                'datasets' => [
                    [
                        'label' => JText::_('VCM_LISTINGS_RANKING'),
                        'backgroundColor' => 'rgba(72, 72, 223, 0.6)',
                        'borderColor' => 'rgba(72, 72, 223, 1)',
                        'data' => array_values($this->hosting_quality_data['stats']['rank_listing_scores'] ?? []),
                    ],
                ],
            ];
            if ($listingsScoreRankingDataSets['labels']) {
                // display chart
                ?>
                <div class="vcm-hosting-quality-block" data-type="listings-score-ranking">
                    <div class="vcm-hosting-quality-block-title">
                        <?php VikBookingIcons::e('home'); ?> 
                        <span><?php echo JText::_('VCM_LISTING_RATINGS_RANKING'); ?></span>
                    </div>
                    <div class="vcm-hosting-quality-block-stats">
                        <canvas id="vcm-chart-listings-score-ranking"></canvas>
                    </div>
                </div>
                <script>
                    VBOCore.DOMLoaded(() => {
                        let ctx = document.getElementById('vcm-chart-listings-score-ranking').getContext('2d');
                        let chart = new Chart(ctx, {
                            type: 'horizontalBar',
                            data: {
                                labels: <?php echo json_encode($listingsScoreRankingDataSets['labels']); ?>,
                                datasets: <?php echo json_encode($listingsScoreRankingDataSets['datasets']); ?>,
                            },
                            options: {
                                legend: {
                                    display: false,
                                },
                                scales: {
                                    xAxes: [{
                                        ticks: {
                                            beginAtZero: true,
                                            max: 5,
                                        },
                                    }],
                                },
                                tooltips: {
                                    callbacks: {
                                        label: (item, value) => {
                                            return Math.abs(item.value) + '/5';
                                        },
                                    },
                                },
                            },
                        });
                    });
                </script>
                <?php
            }

            if (!empty($this->filters['listing_id']) && ($this->listing_quality_issues['reservation_issues'][$this->filters['listing_id']] ?? null)) {
                ?>
                <div class="vcm-hosting-quality-block" data-type="reservation-issues">
                    <div class="vcm-hosting-quality-block-title">
                        <?php VikBookingIcons::e('hand-paper'); ?> 
                        <span><?php echo JText::_('VCM_PENALTY_EVENTS'); ?></span>
                    </div>
                    <div class="vcm-hosting-quality-block-stats">
                    <?php
                    foreach ($this->listing_quality_issues['reservation_issues'][$this->filters['listing_id']] as $res_issue) {
                        if (empty($res_issue['ota_reservation_id'])) {
                            // reservation confirmation code is required
                            continue;
                        }
                        ?>
                        <div class="vcm-hosting-quality-block-stat" data-impact="warning">
                            <div class="vcm-hosting-quality-block-stat-text">
                                <div class="vcm-hosting-quality-block-stat-text-main"><?php echo JText::_('VCM_RESERVATION'); ?></div>
                                <div class="vcm-hosting-quality-block-stat-text-sub">
                                    <button type="button" class="btn btn-small" onclick="VBOCore.handleDisplayWidgetNotification({widget_id: 'booking_details'}, {bid: '<?php echo $res_issue['ota_reservation_id']; ?>'});"><?php echo $res_issue['ota_reservation_id']; ?></button>
                                </div>
                            </div>
                            <div class="vcm-hosting-quality-block-stat-score">
                            <?php
                            if (!empty($res_issue['ota_review_id'])) {
                                // negative review
                                ?>
                                <div class="vcm-hosting-quality-block-stat-score-main">
                                    <button type="button" class="btn btn-danger btn-small" onclick="VBOCore.handleDisplayWidgetNotification({widget_id: 'guest_reviews'}, {ota_review_id: '<?php echo $res_issue['ota_review_id']; ?>'});"><?php echo JText::_('VCM_GUEST_REVIEW'); ?></button>
                                </div>
                                <div class="vcm-hosting-quality-block-stat-score-sub"><?php echo !empty($res_issue['low_overall_rating']) ? $res_issue['low_overall_rating'] . '/5' : ''; ?></div>
                                <?php
                            } elseif (!empty($res_issue['cancellation'])) {
                                // cancellation reason
                                $canc_reason = ucwords(implode(' ', explode('_', strtolower($res_issue['cancellation']))));
                                ?>
                                <div class="vcm-hosting-quality-block-stat-score-main">
                                    <span class="badge badge-error"><?php echo $canc_reason; ?></span>
                                </div>
                                <?php
                            }
                            ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    </div>
                </div>
                <?php
            }
            ?>

            </div>
        </div>

	</div>
</div>

<?php
// debug raw contents
if (JFactory::getApplication()->input->getBool('e4j_debug', false)) {
    echo 'Listings map<pre>' . print_r($this->listings_map, true) . '</pre>';
    echo 'Listing quality issues<pre>' . print_r($this->listing_quality_issues, true) . '</pre>';
    echo 'Property score<pre>' . print_r($this->property_score, true) . '</pre>';
    echo 'Hosting Quality Data<pre>' . print_r($this->hosting_quality_data, true) . '</pre>';
}
