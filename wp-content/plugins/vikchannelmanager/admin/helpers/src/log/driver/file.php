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
 * This logger can be used to register messages within a log file.
 * 
 * @since 1.9.16
 */
class VCMLogDriverFile extends VCMLogAbstract
{
    /** @var string */
    protected $path;

    /** @var array */
    protected $options;

    /** @var resource */
    protected $fp;

    /**
     * Class constructor.
     * 
     * @param  string  $path     The path to the folder holding the log files.
     * @param  array   $options  A configuration array.
     */
    public function __construct(string $path, array $options = [])
    {
        // sanitize the file name
        $this->path = JPath::clean(dirname($path) . '/' . JFile::makeSafe(basename($path)));

        // check whether the file should be removed
        if (!empty($options['overwrite']) && JFile::exists($this->path))
        {
            // delete the existing file
            JFile::delete($this->path);
        }

        $this->options = $options;
    }

    /**
     * Class destructor.
     */
    public function __destruct()
    {
        if ($this->fp) {
            fclose($this->fp);
        }

        // check whether we should auto-clean old files
        if (!empty($this->options['gc_threshold'])) {
            try {
                // get threshold date
                $threshold = JFactory::getDate($this->options['gc_threshold'])->getTimestamp();
            } catch (Exception $error) {
                // fallback to default threshold
                $threshold = JFactory::getDate('-1 month')->getTimestamp();
            }

            // load all files inside the logs folder
            $files = JFolder::files(dirname($this->path), '.', false, true);

            if ($files) {
                foreach ((array) $files as $file) {
                    // in case the last modify date of the file is older than the specified threshold, delete it
                    if (filemtime($file) < $threshold) {
                        JFile::delete($file);
                    }
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function log(string $level, string $message, array $context = [])
    {
        if (!strlen($message)) {
            // nothing to log here
            return;
        }

        // apply context tags to message template
        $message = $this->interpolate($message, $context);

        // check file pointer
        $this->openFile();

        // prepare message string
        $message = $this->renderMessage($level, $message, $context);

        if ($this->fp) {
            // log on file
            fwrite($this->fp, $message);
        }
    }

    /**
     * Helper method used to render the log message.
     * Children classes can overwrite this method to extend the properties to store.
     * 
     * @see log() method for an extended documentation about the supported parameters.
     * 
     * @return  string  The message string to save.
     */
    protected function renderMessage(string $level, string $message, array $context = [])
    {
        return '# ' . JHtml::_('date', 'now', 'Y-m-d H:i:s') . ' | ' . ucfirst($level) . "\n\n" . trim($message) . "\n\n---\n\n";
    }

    /**
     * Opens once the log file pointer.
     * 
     * @return  void
     */
    protected function openFile()
    {
        if ($this->fp) {
            // do not open file more than once
            return;
        }

        $folder = dirname($this->path);

        // in case the folder does not exist, create it
        if (!JFolder::exists($folder)) {
            JFolder::create($folder);
        }

        if (!JFile::exists($this->path)) {
            $heading = '';

            if (preg_match("/\.php$/", $this->path))
            {
                $heading = '<?php exit; ?>' . "\n\n" . $heading;
            }

            if ($heading)
            {
                JFile::write($this->path, $heading);
            }
        }

        $this->fp = fopen($this->path, 'a');
    }
}
