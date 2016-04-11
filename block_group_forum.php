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

class block_group_forum extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_group_forum');
    }
    function instance_allow_multiple() {
        return false;
    }

    function has_config() {
        return false;
    }

     function applicable_formats() {
        // Default case: the block can be used in courses and site index, but not in activities
        return array(
            'mod-assign' => true
        );
    }
    
    function instance_allow_config() {
        return true;
    }
    
    function get_content() {
        global $CFG, $USER;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content;
        }

        if ($this->page->course->newsitems) {   // Create a nice listing of recent postings

            require_once($CFG->dirroot.'/mod/forum/lib.php');   // We'll need this

            $text = '';
            global $DB;
            if (!isset($this->config->forum)){
                $this->content->text = 'Please choose forum to display in settings';
                return $this->content;
            };
            $forum_id=$this->config->forum;
            $forum=null;
            $forums = $DB->get_records_select("forum", "id = ? ", array($forum_id), "id ASC");
            foreach ($forums as $forum_tmp) {
              $forum=$forum_tmp;
            }  
            $modinfo = get_fast_modinfo($this->page->course);
            if (empty($modinfo->instances['forum'][$forum->id])) {
                return '';
            }
            $cm = $modinfo->instances['forum'][$forum->id];

            if (!$cm->uservisible) {
                return '';
            }

            $context = get_context_instance(CONTEXT_MODULE, $cm->id);

			/// User must have perms to view discussions in that forum
            if (!has_capability('mod/forum:viewdiscussion', $context)) {
                return '';
            }

			/// First work out whether we can post to this group and if so, include a link
            $groupmode    = groups_get_activity_groupmode($cm);
            $currentgroup = groups_get_activity_group($cm, true);

            if (forum_user_can_post_discussion($forum, $currentgroup, $groupmode, $cm, $context)) {
                $text .= '<div class="newlink"><a target=_blank href="'.$CFG->wwwroot.'/mod/forum/post.php?forum='.$forum->id.'">'.
                          get_string('addanewtopic', 'forum').'</a>...</div>';
            }

			/// Get all the recent discussions we're allowed to see

            if (! $discussions = forum_get_discussions($cm, 'p.modified DESC', false,
                                                       $currentgroup, $this->page->course->newsitems) ) {
                $text .= '('.get_string('nonews', 'forum').')';
                $this->content->text = $text;
                return $this->content;
            }

			/// Actually create the listing now

            $strftimerecent = get_string('strftimerecent');
            $strmore = get_string('more', 'forum');

			/// Accessibility: markup as a list.
            $text .= "\n<ul class='unlist'>\n";
            foreach ($discussions as $discussion) {

                $discussion->subject = $discussion->name;

                $discussion->subject = format_string($discussion->subject, true, $forum->course);

                $text .= '<li class="post">'.
                         '<div class="head clearfix">'.
                         '<div class="date">'.userdate($discussion->modified, $strftimerecent).'</div>'.
                         '<div class="name">'.fullname($discussion).'</div></div>'.
                         '<div class="info">'.$discussion->subject.' '.
                         '<a  target=_blank href="'.$CFG->wwwroot.'/mod/forum/discuss.php?d='.$discussion->discussion.'">'.
                         $strmore.'...</a></div>'.
                         "</li>\n";
            }
            $text .= "</ul>\n";

            $this->content->text = $text;

            $this->content->footer = '<a target=_blank href="'.$CFG->wwwroot.'/mod/forum/view.php?f='.$forum->id.'">'.
                                      get_string('oldertopics', 'forum').'</a> ...';

        /// If RSS is activated at site and forum level and this forum has rss defined, show link
            if (isset($CFG->enablerssfeeds) && isset($CFG->forum_enablerssfeeds) &&
                $CFG->enablerssfeeds && $CFG->forum_enablerssfeeds && $forum->rsstype && $forum->rssarticles) {
                require_once($CFG->dirroot.'/lib/rsslib.php');   // We'll need this
                if ($forum->rsstype == 1) {
                    $tooltiptext = get_string('rsssubscriberssdiscussions','forum');
                } else {
                    $tooltiptext = get_string('rsssubscriberssposts','forum');
                }
                if (!isloggedin()) {
                    $userid = 0;
                } else {
                    $userid = $USER->id;
                }

                $this->content->footer .= '<br />'.rss_get_link($context->id, $userid, 'mod_forum', $forum->id, $tooltiptext);
            }

        }

        return $this->content;
    }
}


