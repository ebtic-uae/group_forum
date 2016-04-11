<?php
 
// The Collaborative Learning Environment (CLE) is an assignment plugin for Moodle that allows groups to work in real-time on assignments and provides insight to teachers into individual student contributions and group collaboration via usage statistics.

// Copyright (C) 2016 EBTIC

//  CLE is free software designed to work with the Moodle Learning Management system: 
//  you can redistribute it and/or modify it under the terms of the GNU General Public 
//  License as published by the Free Software Foundation, either version 3 of the 
//  License, or (at your option) any later version.

//  CLE is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.

//  You should have received a copy of the GNU General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.

/// For more information on Moodle see - http://moodle.org/

/**
 * This file enables the CLE plugin to be used within the Moodle learning management system 
 *
 * @since      Moodle 2.8.1
 * @package    cle
 * @copyright  2016 EBTIC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
class block_group_forum_edit_form extends block_edit_form {
 
    protected function specific_definition($mform) {
        global $DB;
        $forums=null;
        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block')); 
        
        $get_forums = $DB->get_records_select("forum", "course = ?", array($this->page->course->id), "id ASC");
         foreach ($get_forums as $forum_tmp) {
           $forums[$forum_tmp->id]=$forum_tmp->name;
        } 
        $mform->addElement('select','config_forum',get_string('blockstring','block_group_forum'),$forums);
        $mform->setDefault('config_forum', '0');
        $mform->setType('config_forum', PARAM_MULTILANG);        
    }
}
