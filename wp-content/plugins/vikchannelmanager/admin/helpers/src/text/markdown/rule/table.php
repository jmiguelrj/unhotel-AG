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
 * Fetches tables in markdown syntax. The tables should be built as:
 * Header 1 | Header 2
 * -------- | --------
 * Column 1 | Column 2
 * Column 1 | Column 2
 *
 * @since 1.9
 */
class VCMTextMarkdownRuleTable extends VCMTextMarkdownRuleaware
{
    /** @var bool */
    protected $plain;

    /**
     * Class constructor.
     * 
     * @param  bool  $plain  Whether the resulting text should be plain rather than HTML.
     */
    public function __construct(bool $plain = false)
    {
        $this->plain = $plain;
    }

    /**
     * @inheritDoc
     */
    public function parse(string $markdown)
    {
        // convert table syntax into standard HTML
        $markdown = preg_replace_callback("/([^\n]+?) *\| *([^\n]+?)\R([\|\-: ]+)\R(.*?)(?=\R{2,}|<\/[a-z]+>|$)/s", function($match) {
            if ($this->plain) {
                return $match[0];
            }

            // trim initial and ending pipes
            $match[1] = preg_replace("/^\| ?/", '', $match[1]);
            $match[2] = preg_replace("/ ?\|$/", '', $match[2]);

            $match[3] = preg_replace("/^\| ?/", '', $match[3]);
            $match[3] = preg_replace("/ ?\|$/", '', $match[3]);

            // merge headers
            $headers   = array_merge(array($match[1]), explode('|', $match[2]));
            $separator = $match[3];
            $rows      = preg_split("/\R/", $match[4]);

            // find number of columns
            if (!preg_match_all("/\-+ *\|?/", $separator, $matches) || ($count = count($matches[0])) == 0) {
                // malformed separator, return original string
                return $match[0];
            }

            // check header
            if (count($headers) > $count) {
                // the number of <th> is higher than the specified amount, return original string
                return $match[0];
            }

            // explode the columns of each row
            $rows = array_map(function($row) {
                // trim initial and ending pipes
                $row = preg_replace("/^\| ?/", '', $row);
                $row = preg_replace("/ ?\|$/", '', $row);

                return explode('|', $row);
            }, $rows);

            // merge headers and rows
            array_unshift($rows, $headers);

            // fix missing columns and trim values
            foreach ($rows as &$row) {
                $row = array_filter($row);

                for ($i = 0, $n = count($row); $i < count($headers); $i++) {
                    if ($i < $n) {
                        $row[$i] = trim($row[$i]);
                    } else {
                        $row[$i] = '';
                    }
                }
            }

            // remove empty rows
            $rows = array_filter($rows, function($elem) {
                return (bool) array_filter($elem);
            });

            // get separators
            $separator = array_map('trim', explode('|', $separator));

            // build the HTML table
            return '<div style="overflow-x: scroll;">' . $this->getHtmlTable($rows, $separator) . '</div>';
        }, $markdown);

        return $markdown;
    }

    /**
     * Builds the HTML table.
     *
     * @param   array   $rows       The table rows.
     * @param   array   $separator  The table separators, used to fetch the column alignments.
     *
     * @return  string  The table.  
     */
    protected function getHtmlTable(array $rows, array $separator = [])
    {
        $head = $body = '';

        // fetch cell alignments
        foreach ($separator as &$s) {
            if (preg_match("/^:[\-]+[^:]$/", $s)) {
                // check for left alignment (:----)
                $s = 'left';
            } else if (preg_match("/^[^:][\-]+:$/", $s)) {
                // check for right alignment (----:)
                $s = 'right';
            } else if (preg_match("/^:[\-]+:$/", $s)) {
                // check for center alignment (:---:)
                $s = 'center';
            } else {
                // malformed syntax or not specified, use none
                $s = '';
            }

            if ($s) {
                $s = " style=\"text-align: $s;\"";
            }
        }

        // build head
        foreach ($rows[0] as $i => $th) {
            $head .= "<th{$separator[$i]}>$th</th>";
        }

        // build body
        for ($i = 1; $i < count($rows); $i++) {
            $body .= "<tr>";

            // don't exceed the headers limit
            for ($j = 0; $j < count($rows[0]); $j++) {
                $body .= "<td{$separator[$j]}>{$rows[$i][$j]}</td>";
            }

            $body .= "</tr>";
        }

        return sprintf("<table><thead><tr>%s</tr></thead><tbody>%s</tbody></table>", $head, $body);
    }
}
