<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      Alessio Gaggii - E4J srl
 * @copyright   Copyright (C) 2024 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4jconnect.com | https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

$sections = &$displayData['sections'];

// known languages list
$known_langs = [];
foreach (VikBooking::getVboApplication()->getKnownLanguages() as $tag => $lang) {
    $ltag = substr($tag, 0, 2);
    $known_langs[$ltag] = $lang['nativeName'];
}

if (count($known_langs) < 3) {
    // push some dummy languages
    $known_langs = array_merge($known_langs, [
        'it' => 'Italiano',
        'fr' => 'Français',
        'es' => 'Español',
        'de' => 'Deutsch',
        'pt' => 'Português',
        'jp' => 'Japanese',
    ]);
}

// unset current lang, if set
$current_lang = substr(JFactory::getLanguage()->getTag(), 0, 2);
unset($known_langs[$current_lang]);

// get a random language
$rand_lang = array_values($known_langs)[rand(0, count($known_langs) - 1)];

// build the discover sections
$sections = [
    // text generation
    [
        'title' => JText::_('VBO_AI_DISC_WRITER_FN_TEXT_GEN_TITLE'),
        'description' => JText::_('VBO_AI_DISC_WRITER_FN_TEXT_GEN_DESCR'),
        'icon' => VikBookingIcons::i('pen-fancy'),
        'options' => [
            // e-mail reminder
            [
                'summary' => JText::_('VBO_AI_DISC_WRITER_FN_TEXT_GEN_REMINDER_SUMM'),
                'example' => JText::_('VBO_AI_DISC_WRITER_FN_TEXT_GEN_REMINDER_EXA'),
                'role'    => 'get',
            ],
            // custom language
            [
                'summary' => JText::_('VBO_AI_DISC_WRITER_FN_TEXT_GEN_MESS_SUMM'),
                'example' => JText::sprintf('VBO_AI_DISC_WRITER_FN_TEXT_GEN_MESS_EXA', 'John Doe') . ' ' . JText::sprintf('VBO_AI_GEN_MESS_LANG', $rand_lang),
                'role'    => 'get',
            ],
        ],
    ],
];
