<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - E4J srl
 * @copyright   Copyright (C) 2025 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Cron Job implementation for Task Manager Operators Reminder.
 * 
 * @since   1.18.0 (J) - 1.8.0 (WP)
 */
class VikBookingCronJobTaskManagerOperatorsReminder extends VBOCronJob
{
	// do not need to track the elements
    use VBOCronTrackerUnused;

    /**
     * This method should return all the form fields required to collect the information
     * needed for the execution of the cron job.
     * 
     * @return  array  An associative array of form fields.
     */
    public function getForm()
    {
        /**
         * Build a list of all special tags for the visual editor.
         */
        $special_tags_base = [
            '{operator_name}',
            '{tasks_list}',
            '{dashboard_url}',
        ];

        // editor buttons
        $editor_btns = $special_tags_base;

        // convert special tags into HTML buttons, displayed under the text editor
        $special_tags_base = array_map(function($tag) {
            return '<button type="button" class="btn" onclick="setCronTplTag(\'tpl_text\', \'' . $tag . '\');">' . $tag . '</button>';
        }, $special_tags_base);

        return [
            'cron_lbl' => [
                'type'  => 'custom',
                'label' => '',
                'html'  => '<h4><i class="' . VikBookingIcons::i('user-tie') . '"></i> <i class="' . VikBookingIcons::i('bell') . '"></i>&nbsp;' . $this->getTitle() . '</h4>',
            ],
            'remindbefored' => [
                'type'    => 'number',
                'label'   => JText::_('VBOCRONSMSREMPARAMBEFD'),
                'default' => 1,
                'min'     => -365,
                'max'     => 365
            ],
            'operators' => [
                'type'     => 'elements',
                'label'    => JText::_('VBMENUOPERATORS'),
                'help'     => sprintf('(%s)', JText::_('VBOFILTEISROPTIONAL')),
                'multiple' => true,
                'asset_options' => [
                    'placeholder' => JText::_('VBMENUOPERATORS'),
                    'allowClear'  => true,
                ],
                'elements' => VikBooking::getOperatorInstance()->getElements(),
            ],
            'statuses' => [
                'type'     => 'tags',
                'label'    => JText::_('VBSTATUS'),
                'help'     => sprintf('(%s)', JText::_('VBOFILTEISROPTIONAL')),
                'multiple' => true,
                'asset_options' => [
                    'placeholder' => JText::_('VBSTATUS'),
                    'allowClear'  => true,
                ],
                'tags' => [],
                'groups' => VBOFactory::getTaskManager()->getStatusGroupElements(),
                'wrapdivcls' => 'vbo-multiselect-inline-elems-wrap vbo-tagcolors-elems-wrap vbo-statuscolors-elems-wrap',
                'inline' => false,
                'style_selection' => true,
            ],
            'test' => [
                'type'    => 'select',
                'label'   => JText::_('VBOCRONSMSREMPARAMTEST'),
                'help'    => JText::_('VBOCRONEMAILREMPARAMTESTHELP'),
                'default' => 'OFF',
                'options' => [
                    'ON'  => JText::_('VBYES'),
                    'OFF' => JText::_('VBNO'),
                ],
            ],
            'subject' => [
                'type'    => 'text',
                'label'   => JText::_('VBOCRONEMAILREMPARAMSUBJECT'),
                'default' => JText::_('VBO_YOUR_UPCOMING_TASKS'),
            ],
            'tpl_text' => [
                'type'    => 'visual_html',
                'label'   => JText::_('VBOCRONSMSREMPARAMTEXT'),
                'default' => $this->getDefaultTemplate(),
                'attributes' => [
                    'id'    => 'tpl_text',
                    'style' => 'width: 70%; height: 150px;',
                ],
                'editor_opts' => [
                    'modes' => [
                        'visual',
                        'modal-visual',
                        'text',
                    ],
                    'gen_ai' => [
                        'placeholders' => 1,
                        'prompt' => 'Write a message for an operator that will be notified about the upcoming tasks for housekeeping and more.',
                    ],
                    'unset_buttons' => [
                        'preview',
                    ],
                ],
                'editor_btns' => $editor_btns,
            ],
            'buttons' => [
                'type'  => 'custom',
                'label' => '',
                'html'  => '<div class="btn-toolbar vbo-smstpl-toolbar vbo-cronparam-cbar" style="margin-top: -10px;">
                    <div class="btn-group pull-left vbo-smstpl-bgroup vik-contentbuilder-textmode-sptags">'
                        . implode("\n", $special_tags_base)
                    . '</div>
                </div>
                <script>

                    function setCronTplTag(taid, tpltag) {
                        var tplobj = document.getElementById(taid);
                        if (tplobj != null) {
                            var start = tplobj.selectionStart;
                            var end = tplobj.selectionEnd;
                            tplobj.value = tplobj.value.substring(0, start) + tpltag + tplobj.value.substring(end);
                            tplobj.selectionStart = tplobj.selectionEnd = start + tpltag.length;
                            tplobj.focus();
                            jQuery("#" + taid).trigger("change");
                        }
                    }

                </script>',
            ],
            'help' => [
                'type'  => 'custom',
                'label' => '',
                'html'  => '<p class="vbo-cronparam-suggestion"><i class="vboicn-lifebuoy"></i>' . JText::_('VBOCRONSMSREMHELP') . '</p>',
            ],
        ];
    }

    /**
     * Returns the title of the cron job.
     * 
     * @return  string
     */
    public function getTitle()
    {
        return JText::_('VBO_TASK_MANAGER') . ' - ' . JText::_('VBO_CRON_OPERATORS_REMINDER');
    }

    /**
     * Executes the cron job.
     * 
     * @return  boolean  True on success, false otherwise.
     */
    protected function execute()
    {
        $remindbefored = (int) $this->params->get('remindbefored');
        $operators = (array) $this->params->get('operators');
        $statuses = (array) $this->params->get('statuses');

        // calculate the target due date
        $target_date = date('Y-m-d', strtotime(sprintf('%s%d days', ($remindbefored < 0 ? '-' : '+'), $remindbefored)));

        $this->output('<p>Reading operator tasks due on ' . $target_date . '.</p>');

        // build the operators map
        $operatorsMap = [];

        if (!$operators) {
            // load all the operators
            $operatorsMap = VikBooking::getOperatorInstance()->getAll();

            // get the IDs of all operators
            $operators = array_keys($operatorsMap);
        }

        if (!$operatorsMap) {
            // load all the operators
            $operatorsMap = VikBooking::getOperatorInstance()->getAll();
        }

        // map and filter the list of operator IDs
        $operators = array_values(array_filter(array_map('intval', $operators)));
        if (!$operators) {
            // it is mandatory to filter the tasks by some operator IDs
            $this->output('<span>No operators to notify.</span>');
            return true;
        }

        $this->output('<p>Filtering tasks for ' . count($operators) . ' operators.</p>');

        // filter out empty status enums, for an optional filter
        $statuses = array_values(array_filter($statuses));

        if ($statuses) {
            $this->output('<p>Filtering tasks by ' . count($statuses) . ' status types.</p>');
        }

        // read tasks due on the calculated date, assigned to the given list of operators
        $tasks = VBOTaskModelTask::getInstance()->filterItems([
            'dates'     => sprintf('%s:%s', $target_date, $target_date),
            'assignees' => $operators,
            'statusId'  => $statuses,
        ]);

        if (!$tasks) {
            $this->output('<span>No tasks found.</span>');
            return true;
        }

        $this->output('<p>Total tasks found: ' . count($tasks) . '</p>');

        // check if we are running on test mode
        $test_mode = ($this->params->get('test', 'OFF') == 'ON');

        // build the list of tasks for each operator
        $operatorTasks = [];

        foreach ($tasks as $taskRecord) {
            $task = VBOTaskTaskregistry::getInstance((array) $taskRecord);
            foreach ($task->getAssigneeIds() as $assigneeId) {
                // push task to the current operator involved
                $operatorTasks[$assigneeId] = $operatorTasks[$assigneeId] ?? [];
                $operatorTasks[$assigneeId][] = $task;
            }
        }

        foreach ($operatorTasks as $assigneeId => $tasksList) {
            if (!($operatorsMap[$assigneeId]['email'] ?? null)) {
                $this->output('<span>Skipping operator ID ' . $assigneeId . ' because of empty email address.</span>');
                continue;
            }

            if ($test_mode) {
                // test mode enabled
                $this->output('<span>Skipping ' . count($tasksList) . ' task notification(s) for operator ' . trim(sprintf('%s %s', ($operatorsMap[$assigneeId]['first_name'] ?? ''), ($operatorsMap[$assigneeId]['last_name'] ?? ''))) . ' (test mode).</span>');
                continue;
            }

            if ($this->notifyOperatorTasks($operatorsMap[$assigneeId], $tasksList)) {
                // success
                $this->output('<span>Successfully sent ' . count($tasksList) . ' task notification(s) to operator ' . trim(sprintf('%s %s', ($operatorsMap[$assigneeId]['first_name'] ?? ''), ($operatorsMap[$assigneeId]['last_name'] ?? ''))) . '.</span>');
                // append execution log
                $this->appendLog('Successfully sent ' . count($tasksList) . ' task notification(s) to operator ' . trim(sprintf('%s %s', ($operatorsMap[$assigneeId]['first_name'] ?? ''), ($operatorsMap[$assigneeId]['last_name'] ?? ''))) . '.');
            } else {
                // error
                $this->output('<span>Could not send ' . count($tasksList) . ' task notification(s) to operator ' . trim(sprintf('%s %s', ($operatorsMap[$assigneeId]['first_name'] ?? ''), ($operatorsMap[$assigneeId]['last_name'] ?? ''))) . '.</span>');
            }
        }

        return true;
    }

    /**
     * Builds the email notification message for the given operator for the given tasks list.
     * 
     * @param   array                   $operator   Associative operator record details.
     * @param   VBOTaskTaskregistry[]   $tasksList  List of task registry objects to notify.
     * 
     * @return  string                  The operator tasks message to be sent via email.
     */
    private function buildOperatorTasksMessage(array $operator, array $tasksList)
    {
        // get the message from settings
        $message = $this->params->get('tpl_text');

        if (!trim($message)) {
            // get the default template
            $message = $this->getDefaultTemplate();
        }

        // build tasks list HTML string
        $tasks_list_str = '';
        $tasks_list_str .= '<ul>';
        foreach ($tasksList as $task) {
            $tasks_list_str .= sprintf(
                "<li>%s (%s) - %s</li>\n",
                $task->getTitle(),
                $task->getListingName($task->getListingId()),
                $task->getDueDate(true, 'Y-m-d H:i')
            );
        }
        $tasks_list_str .= '</ul>';

        // parse special tags
        $message = str_replace('{operator_name}', trim(sprintf('%s %s', ($operator['first_name'] ?? ''), ($operator['last_name'] ?? ''))), $message);
        $message = str_replace('{dashboard_url}', VikBooking::getOperatorInstance()->getToolUri('task_manager'), $message);
        $message = str_replace('{tasks_list}', $tasks_list_str, $message);

        return $message;
    }

    /**
     * Sends an email notification to the given operator for the given tasks list.
     * 
     * @param   array                   $operator   Associative operator record details.
     * @param   VBOTaskTaskregistry[]   $tasksList  List of task registry objects to notify.
     * 
     * @return  bool                    True on success, false otherwise.
     */
    private function notifyOperatorTasks(array $operator, array $tasksList)
    {
        // build operator tasks email message string
        $message = $this->buildOperatorTasksMessage($operator, $tasksList);

        if (empty($message)) {
            $this->output('<span>Notification message is empty.</span>');
            return false;
        }

        $is_html = (strpos($message, '<') !== false || strpos($message, '</') !== false);
        if ($is_html && !preg_match("/(<\/?br\/?>)+/", $message)) {
            // when no br tags found, apply nl2br
            $message = nl2br($message);
        }

        $admin_sendermail = VikBooking::getSenderMail();

        return VikBooking::getVboApplication()->sendMail($admin_sendermail, $admin_sendermail, $operator['email'], $admin_sendermail, $this->params->get('subject'), $message, $is_html);
    }

    /**
     * Returns the default e-mail template.
     * 
     * @return  string
     * 
     * @since   1.5.10
     */
    private function getDefaultTemplate()
    {
        static $tmpl = '';

        if (!$tmpl) {
            $sitelogo     = VBOFactory::getConfig()->get('sitelogo');
            $company_name = VikBooking::getFrontTitle();

            if ($sitelogo && is_file(VBO_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources'. DIRECTORY_SEPARATOR . $sitelogo)) {
                $tmpl .= '<p style="text-align: center;">'
                    . '<img src="' . VBO_ADMIN_URI . 'resources/' . $sitelogo . '" alt="' . htmlspecialchars($company_name) . '" /></p>'
                    . "\n";
            }

            $tmpl .= 
<<<HTML
<h1 style="text-align: center;">
    <span style="font-family: verdana;">$company_name</span>
</h1>
<hr class="vbo-editor-hl-mailwrapper">
<h4>Dear {operator_name},</h4>
<p><br></p>
<p>Here is a list of your upcoming tasks:</p>
<p><br></p>
<p>{tasks_list}</p>
<p><br></p>
<p><br></p>
<p>Tasks can be managed at the following URL:</p>
<p>{dashboard_url}</p>
<hr class="vbo-editor-hl-mailwrapper">
<p><br></p>
HTML
            ;
        }

        return $tmpl;
    }
}
