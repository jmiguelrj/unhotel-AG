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
 * Crontab service provider.
 * 
 * @since 1.9
 */
final class VCMCrontabProvider
{
    /**
     * Schedule the channel manager native crons through the provided crontab simulator.
     * 
     * @param   VBOCrontabSimulator  $crontab
     * 
     * @return  void
     */
    public function provide(VBOCrontabSimulator $crontab)
    {
        if (VCMPlatformDetection::isWordPress()) {
            // ignore in case of WordPress
            return;
        }

        /**
         * Action used retries all pending data transmission records that previously failed. By default,
         * only one failure per execution is retried, and its last_retry date is updated so
         * that the next execution will eventually parse another failure in cascade.
         * 
         * Should run every 5 minutes.
         * 
         * @since 1.8.20
         */
        $crontab->schedule(new VBOCrontabRunnerAware('request_schedules_retry', 5 * 60, function(VBOCrontabLogger $logger) {
            VCMRequestScheduler::getInstance()->retry(true);
        }));

        /**
         * Action used to monitor if any previously locked room due to pending payment should be released.
         * If any expired reservation is still not confirmed, the involved rooms will be updated.
         * 
         * Should run every 5 minutes.
         * 
         * @since 1.8.20
         */
        $crontab->schedule(new VBOCrontabRunnerAware('monitor_pending_locks', 5 * 60, function(VBOCrontabLogger $logger) {
            VCMRequestAvailability::getInstance()->monitorPendingLocks(true);
        }));

        /**
         * Action used to handle auto-responding features to OTA guest messages.
         * 
         * Should run every hour.
         * 
         * @since 1.8.21
         */
        $crontab->schedule(new VBOCrontabRunnerAware('chat_auto_responder', 60 * 60, function(VBOCrontabLogger $logger) {
            VCMChatAutoresponder::getInstance()->watchSchedules();
        }));

        /**
         * Action used to process the enqueued chat messages asynchronously.
         * 
         * Should run every 5 minutes.
         * 
         * @since 1.9.14
         */
        $crontab->schedule(new VBOCrontabRunnerAware('chat_async_processor', 60 * 60, function(VBOCrontabLogger $logger) {
            $jobs = 5;

            /**
             * In case the process is running outside a cron job, do not process more than one thread.
             * 
             * @since 1.9.16
             */
            if (!VCMFactory::getPlatform()->getCronEnvironment()->isRunning()) {
                // decrease the provided loop
                $jobs = 1;
            }

            VCMFactory::getChatAsyncMediator()->process($jobs);
        }));

        // AI-related tasks
        if (VikChannelManager::getChannel(VikChannelManagerConfig::AI)) {
            /**
             * Action used to register a periodic task to extract the topics from the guest questions.
             * 
             * Should run every 5 minutes.
             * 
             * @since 1.9
             */
            $crontab->schedule(new VBOCrontabRunnerAware('ai_extract_topics', 5 * 60, function(VBOCrontabLogger $logger) {
                (new VCMAiCronTopics)->extract();
            }));

            /**
             * Action used to let the AI auto-replies to the guest messages.
             * 
             * Should run every 5 minutes.
             * 
             * @since 1.9
             */
            $crontab->schedule(new VBOCrontabRunnerAware('ai_autoreply_messages', 5 * 60, function(VBOCrontabLogger $logger) {
                (new VCMAiCronMessages)->autoReply();
            }));

            /**
             * Action used to let the AI auto-replies to the reviews left by the guests.
             * 
             * Should run every hour.
             * 
             * @since 1.9
             */
            $crontab->schedule(new VBOCrontabRunnerAware('ai_autoreply_reviews', 60 * 60, function(VBOCrontabLogger $logger) {
                (new VCMAiCronReviews)->autoReply($pastDays = 7, $maxReplies = 2);
            }));

            /**
             * Action used to let the AI auto-reviews the guests.
             * 
             * Should run every hour.
             * 
             * @since 1.9
             */
            $crontab->schedule(new VBOCrontabRunnerAware('ai_autoreview_guests', 60 * 60, function(VBOCrontabLogger $logger) {
                (new VCMAiCronGuests)->autoReview($maxReviews = 2);
            }));
        }
    }
}
