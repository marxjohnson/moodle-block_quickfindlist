<?php

class block_quickfindlist extends block_base {

    function init() {
        $this->content_type = BLOCK_TYPE_TEXT;
        $this->title = get_string('quickfindlist','block_quickfindlist');
        $this->content->footer = '';
    }


    function instance_allow_multiple() {
        return true;
    }

    function preferred_width() {
      // The preferred value is in pixels
      return 180;
    }

    function get_content() {
        global $CFG, $COURSE, $DB;
        if (empty($this->config->role)) {
            $select = 'SELECT * ';
            $from = 'FROM {block} AS b
                        JOIN {block_instances} AS bi ON b.name = blockname ';
            $where = 'WHERE name = "quickfindlist"
                        AND pagetypepattern = "?"
                        AND parentcontextid = ?
                        AND bi.id < ?';
            $params = array($this->instance->pagetypepattern, $this->instance->parentcontextid, $this->instance->id);
            if ($thispageqflblocks = $DB->get_records_sql($select.$from.$where, $params)){
                foreach ($thispageqflblocks as $thispageqflblock){
                    //don't give a warning for blocks without a role configured
                    if (@unserialize(base64_decode($thispageqflblock->configdata))->role < 1) {
                        $this->content->text = get_string('multiplenorole','block_quickfindlist');
                        return $this->content;
                   }
                }
            }

            $this->config->role = -1;
        }

        if ($role = $DB->get_record('role', array('id' => $this->config->role))) {
            $roleid = $role->id;
            $this->title = $role->name.get_string('list','block_quickfindlist');
        } else {
            $roleid = '-1';
            $this->title = get_string('allusers','block_quickfindlist').get_string('list','block_quickfindlist');
        }

        global $CFG, $USER, $COURSE;
        $context_system = get_context_instance(CONTEXT_SYSTEM);

        if (has_capability('block/quickfindlist:use', $context_system)) {
            if (empty($this->config->userfields)) {
                $this->config->userfields = get_string('userfieldsdefault','block_quickfindlist');
            }
            if (empty($this->config->url)) {
                $this->config->url = $CFG->wwwroot.'/user/view.php?course='.$COURSE->id.'&id=';
            }
            $name = optional_param('quickfindlistsearch'.$roleid, '', PARAM_TEXT);

            $this->content->text = '<a name="quickfindanchor'.$roleid.'"></a>
                <form action="'.$_SERVER['REQUEST_URI'].'#quickfindanchor'.$roleid.'" method="post">
                    <input style="width:120px;" autocomplete="off" onkeyup="quickfindsearch(\''.$roleid.'\', \''.$this->config->userfields.'\', \''.urlencode($this->config->url).'\', \''.$COURSE->format.'\', \''.$COURSE->id.'\')" id="quickfindlistsearch'.$roleid.'" name="quickfindlistsearch'.$roleid.'" value="'.$name.'" />
                    <span id="quickfindprogress'.$roleid.'" style="visibility:hidden;"><img src="'.$CFG->wwwroot.'/blocks/quickfindlist/pix/ajax-loader.gif" alt="Loading.." /></span>
                    <div><input type="submit" id="quickfindsubmit'.$roleid.'" name="quickfindsubmit'.$roleid.'" value="Search" /></div>
                </form>';

            $this->content->text .= '<div id="quickfindlist'.$roleid.'">';
            $quickfindsubmit[$roleid] = optional_param('quickfindsubmit'.$roleid, false, PARAM_ALPHA);

            if (!empty($quickfindsubmit[$roleid])) {
                if (!empty($name)) {
                    $params = array("%$name%");
                    $select = 'SELECT id, firstname, lastname, username ';
                    $from = 'FROM {user} AS u ';
                    $where = "WHERE deleted = 0 AND CONCAT(firstname, ' ', lastname) LIKE ? ";
                    if ($this->config->role != -1) {
                        $params[] = $this->config->role;
                        $subselect = 'SELECT COUNT(*) ';
                        $subfrom = 'FROM {role_assignments} AS ra
                                           JOIN {context} AS c ON c.id = contextid ';
                        $subwhere = 'WHERE ra.userid = u.id
                                           AND ra.roleid=?';
                        if ($COURSE->format != 'site') {
                            $params[] = $COURSE->id;
                            $subwhere .= ' AND contextlevel=50 AND instanceid = ?';
                        }
                        $where .= 'AND ('.$subselect.$subfrom.$subwhere.') > 0 ';
                    }
                    $order = 'ORDER BY lastname';
                    
                    if($people = $DB->get_records_sql($select.$from.$where.$order, $params)){
                        foreach ($people as $person) {
                            $userstring = str_replace('[[firstname]]', $person->firstname, $this->config->userfields);
                            $userstring = str_replace('[[lastname]]', $person->lastname, $userstring);
                            $userstring = str_replace('[[username]]', $person->username, $userstring);
                            $this->content->text .= '<div><a href="'.$this->config->url.$person->id.'">'.$userstring.'</a></div>';
                        }
                    }
                }
            }
            $this->content->text .= '</div>';

//            require_js(array($CFG->wwwroot.'/blocks/quickfindlist/quickfindlist.js',
//            'yui_yahoo',
//            'yui_event',
//            'yui_connection'));
//            $this->content->text.='<script type="text/javascript">var wwwroot="'.$CFG->wwwroot.'";var xhr;</script>';
        }
        $this->content->footer='';

        return $this->content;

    }
}
?>
