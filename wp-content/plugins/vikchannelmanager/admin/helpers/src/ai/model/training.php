<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2024 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// Restricted access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * AI training model.
 * 
 * @since 1.9
 */
class VCMAiModelTraining extends VCMAiModelAware
{
    /**
     * Fetches the details of the provided training set.
     * 
     * @param   int  $id  The ID of the training set to fetch. 
     * 
     * @return  object
     * 
     * @throws  Exception
     */
    public function getItem(int $id)
    {
        $response = (new JHttp)->get($this->getEndPoint('trainings/' . (int) $id), $this->getHeaders(), $this->timeout);

        if ($response->code != 200) {
            throw new Exception($response->body, $response->code);
        }

        $item = json_decode($response->body);

        // normalize the attachments for local usage
        $item->attachments = array_values(array_filter(array_map([$this, 'getAttachment'], $item->attachments)));

        // normalize the listings from comma separated list to an array of numbers
        if (!is_array($item->id_listing)) {
            $item->id_listing = preg_split("/\s*,\s*/", $item->id_listing);
            $item->id_listing = array_values(array_filter($item->id_listing));
        }

        return $item;
    }

    /**
     * Fetches the list of available training sets.
     * 
     * @param   array   $filters  An associative array containing the following search filters:
     *                            - search    the matching keywords;
     *                            - category  the parent group of the training set;
     *                            - status    whether the training set is published (1), unpublished (0) or needs review (2);
     *                            - language  the language of the training set;
     *                            - listing   loads only the trainings explicitly assigned to this listing.
     * @param   array   $options  An associative array containing the following pagination options:
     *                            - ordering   the ordering column;
     *                            - direction  the ordering direction (asc or desc);
     *                            - offset     from which item the pagination should start;
     *                            - limit      the maximum number of items to take.
     * 
     * @return  object  An object holding the items and the pagination data.
     * 
     * @throws  Exception
     */
    public function getItems(array $filters = [], array $options = [])
    {
        $query = http_build_query(array_merge($filters, [
            'ordering' => $options['ordering'] ?? null,
            'direction' => $options['direction'] ?? null,
            'offset' => $options['offset'] ?? null,
            'limit' => $options['limit'] ?? null,
        ]));
        
        $response = (new JHttp)->get($this->getEndPoint('trainings/?' . $query), $this->getHeaders(), $this->timeout);

        if ($response->code != 200) {
            throw new Exception($response->body, $response->code);
        }

        $response = json_decode($response->body);

        // manually adjust the listings from comma separated string to array
        foreach ($response->items as $item) {
            if (!is_array($item->id_listing)) {
                $item->id_listing = preg_split("/\s*,\s*/", $item->id_listing);
                $item->id_listing = array_values(array_filter($item->id_listing));
            }
        }

        return $response;
    }

    /**
     * Creates a new training set.
     * 
     * @param   array|object  $data  The details of the training set.
     * 
     * @return  int  The ID of the created training set.
     * 
     * @throws  Exception
     */
    public function insert($data)
    {
        $data = (array) $data;
        unset($data['id'], $data['created']);

        $data['created_by'] = JFactory::getUser()->id;

        if (!empty($data['attachments'])) {
            foreach ($data['attachments'] as &$attachment) {
                if (!is_string($attachment)) {
                    $attachment = (array) $attachment;
                    $attachment = $attachment['filename'];
                }
            }
        }

        $response = (new JHttp)->post($this->getEndPoint('trainings'), json_encode($data), $this->getHeaders([
            'Content-Type' => 'application/json',
        ]), $this->timeout);

        if ($response->code != 200) {
            throw new Exception($response->body, $response->code);
        }

        return (int) $response->body;
    }

    /**
     * Updates an existing training set.
     * 
     * @param   array|object  $data  The details of the training set.
     * 
     * @return  void
     * 
     * @throws  Exception
     */
    public function update($data)
    {
        $data = (array) $data;
        
        if (empty($data['id'])) {
            throw new InvalidArgumentException('Missing training ID.', 400);
        }

        unset($data['modified']);

        $data['modified_by'] = JFactory::getUser()->id;

        if (!empty($data['attachments'])) {
            foreach ($data['attachments'] as &$attachment) {
                if (!is_string($attachment)) {
                    $attachment = (array) $attachment;
                    $attachment = $attachment['filename'];
                }
            }
        }

        $response = (new JHttp)->put($this->getEndPoint('trainings/' . $data['id']), json_encode($data), $this->getHeaders([
            'Content-Type' => 'application/json',
        ]), $this->timeout);

        if ($response->code != 200) {
            throw new Exception($response->body, $response->code);
        }
    }

    /**
     * Removes the specified training set.
     * 
     * @param   int  $id  The ID of the training set to delete.
     * 
     * @return  void
     * 
     * @throws  Exception
     */
    public function delete(int $id)
    {
        $response = (new JHttp)->delete($this->getEndPoint('trainings/' . (int) $id), $this->getHeaders(), $this->timeout);

        if ($response->code != 200) {
            throw new Exception($response->body, $response->code);
        }
    }

    /**
     * Converts a filename into a chat attachment object.
     * 
     * @param   string  $filename  The filename of the attachment.
     * 
     * @return  object|null  The attachment object or null in case the file doesn't exist.
     */
    public function getAttachment(string $filename)
    {
        $attachment = new stdClass;

        // construct path of 
        $path = JPath::clean(VCM_SITE_PATH . '/helpers/chat/attachments/docs/' . $filename);

        if (!JFile::exists($path)) {
            // the file does not exist any longer
            return null;
        }

        $fileInfo = pathinfo($path);

        // name without the extension (e.g. "picture")
        $attachment->name = $fileInfo['filename'];
        $attachment->id = $fileInfo['filename'];
        // name with file extension (e.g. "picture.png")
        $attachment->fullName = $filename;
        $attachment->filename = $filename;
        // file extension only (e.g. "png")
        $attachment->extension = $fileInfo['extension'];
        // fetch file mime type (e.g. image/png)
        $attachment->type = mime_content_type($path);
        // build download URL
        $attachment->url = VCM_SITE_URI . 'helpers/chat/attachments/docs/' . $filename;
        // flag attachment as custom (use a different URL than the default ones)
        $attachment->custom = true;

        return $attachment;
    }

    /**
     * Calculate the remaining days to the expiration of the specified training set.
     * It should be used only if the set needs to be reviewed.
     * 
     * @param   object  $item  The training set to analyze.
     * 
     * @param   int     The remaining days.
     * 
     * @since   1.9.16
     */
    public function getExpirationDays(object $item)
    {
        $now = JFactory::getDate('now');

        // calculate expiration date threshold (+30 days since creation)
        $threshold = JFactory::getDate($item->created);
        $threshold->modify('+30 days');

        if ($threshold < $now) {
            // training set already expired
            return 0;
        }

        // add 1 to the resulting days as margin
        return $now->diff($threshold)->days + 1;
    }
}
