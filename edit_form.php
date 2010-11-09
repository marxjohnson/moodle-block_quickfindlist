<?php
class block_quickfindlist_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $DB;
        global $COURSE;

        if (!empty($this->config->role)) {
            $currentrole = $this->config->role;
        } else {
            $currentrole = null;
        }

        $allusers = new stdClass;
        $allusers->id = -1;
        $allusers->name = get_string('allusers', 'block_quickfindlist');
        $roles = array_merge(array($allusers), $DB->get_records('role'));

        $rolesused = array();

        $select = 'SELECT * ';
        $from = 'FROM {block} AS b
                    JOIN {block_instances} AS bi ON b.name = blockname ';
        $where = 'WHERE name = "quickfindlist"
                    AND pagetypepattern = "?"
                    AND parentcontextid = ?
                    AND bi.id < ?';
        $params = array($this->block->instance->pagetypepattern, $this->block->instance->parentcontextid, $this->block->instance->id);
        if ($blocksonthispage = $DB->get_records_sql($select.$from.$where, $params)) {
                foreach ($blocksonthispage as $block) {
                if ($block->config = unserialize(base64_decode($block->configdata))) {
                    $rolesused[] = $block->config->role;
                }
            }
        }

        $select = HTML_QuickForm::createElement('select', 'config_role', get_string('role','block_quickfindlist'));

        foreach ($roles as $role) {
            $attributes = array();
            if ($currentrole == $role->id) {
                $attributes['selected'] = 'selected';
            } else if (in_array($role->id, $rolesused)) {
                $attributes['disabled'] = 'disabled';
            }
            
            $value = $role->id;
            $text = $role->name;

            $params = array($role->id);
            $subselect = 'SELECT COUNT(*) ';
            $subfrom = 'FROM {role_assignments} AS ra
                           JOIN {context} AS c ON c.id = contextid ';
            $subwhere = 'WHERE ra.userid = {user}.id
                           AND ra.roleid = ?';

            if ($COURSE->format != 'site') {
                $params[] = $COURSE->id;
                $subwhere .= ' AND contextlevel = 50 AND instanceid = ?';
            }

            $where = '('.$subselect.$subfrom.$subwhere.') > 0
                AND deleted = 0';

            $usercount = $DB->count_records_select('user', $where, $params);
            if ($usercount > 5000) {
                echo $text .= get_string('lotsofusers','block_quickfindlist').'('.$usercount.'), '.get_string('couldgetslow','block_quickfindlist');
            }
            $select->addOption($text, $value, $attributes);
        }

        // Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));
        $mform->addElement($select);
        $mform->addElement('text', 'config_userfields', get_string('userfields','block_quickfindlist'));
        $mform->setDefault('config_userfields', get_string('userfieldsdefault','block_quickfindlist'));
        $mform->addElement('text', 'config_url', get_string('url','block_quickfindlist'));
    }
}