<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_cdfields {

    private $output = "";

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        $this->trellis->check_perm( 'manage', 'cdfields' );

        $this->trellis->load_functions('cdfields');
        $this->trellis->load_lang('cdfields');

        $this->trellis->skin->set_active_link( 2 );

        switch( $this->trellis->input['act'] )
        {
            case 'list':
                $this->list_fields();
            break;
            case 'reorder':
                $this->reorder_fields();
            break;
            case 'add':
                $this->add_field();
            break;
            case 'edit':
                $this->edit_field();
            break;

            case 'doreorder':
                $this->do_reorder();
            break;
            case 'doadd':
                $this->do_add();
            break;
            case 'doedit':
                $this->do_edit();
            break;
            case 'dodel':
                $this->do_delete();
            break;

            default:
                $this->list_fields();
            break;
        }
    }

    #=======================================
    # @ List Fields
    #=======================================

    private function list_fields()
    {
        #=============================
        # Grab Fields
        #=============================

        $field_rows = '';

        if ( ! $fields = $this->trellis->func->cdfields->get( array( 'select' => array( 'id', 'name', 'type', 'required' ), 'order' => array( 'position' => 'asc' ) ) ) )
        {
            $field_rows .= "<tr><td class='bluecell-light' colspan='7'><strong><a href='<! TD_URL !>/admin.php?section=manage&amp;page=cdfields&amp;act=add'>{lang.no_cdfields}</a></strong></td></tr>";
        }
        else
        {
            foreach( $fields as $fid => $f )
            {
                if ( $f['type'] == 'textfield' )
                {
                    $f['type'] = '{lang.textfield}';
                }
                elseif ( $f['type'] == 'textarea' )
                {
                    $f['type'] = '{lang.textarea}';
                }
                elseif ( $f['type'] == 'dropdown' )
                {
                    $f['type'] = '{lang.dropdown}';
                }
                elseif ( $f['type'] == 'checkbox' )
                {
                    $f['type'] = '{lang.checkbox}';
                }
                elseif ( $f['type'] == 'radio' )
                {
                    $f['type'] = '{lang.radio}';
                }

                if ( $f['required'] )
                {
                    $f['required'] = '{lang.yes}';
                }
                else
                {
                    $f['required'] = '{lang.no}';
                }

                $field_rows .= "<tr>
                                    <td class='bluecellthin-light'><strong>{$f['id']}</strong></td>
                                    <td class='bluecellthin-dark'>{$f['name']}</td>
                                    <td class='bluecellthin-light' style='font-weight: normal'>{$f['type']}</td>
                                    <td class='bluecellthin-light' align='center'>{$f['required']}</td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=cdfields&amp;act=edit&amp;id={$f['id']}'><img src='<! IMG_DIR !>/button_edit.gif' alt='{lang.edit}' /></a></td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=cdfields&amp;act=dodel&amp;id={$f['id']}' onclick='return confirmDelete({$f['id']})'><img src='<! IMG_DIR !>/button_delete.gif' alt='{lang.delete}' /></a></td>
                                </tr>";
            }
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<script type='text/javascript'>
                        function confirmDelete(fid) {
                            dialogConfirm({
                                title: '{lang.dialog_delete_title}',
                                message: '{lang.dialog_delete_msg}',
                                yesButton: '{lang.dialog_delete_button}',
                                yesAction: function() { goToUrl('<! TD_URL !>/admin.php?section=manage&page=cdfields&act=dodel&id='+fid) },
                                noButton: '{lang.cancel}'
                            }); return false;
                        }
                        </script>
                        <div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.cdfields_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='2%' align='left'>{lang.id}</th>
                            <th class='bluecellthin-th' width='55%' align='left'>{lang.name}</th>
                            <th class='bluecellthin-th' width='27%' align='left'>{lang.type}</th>
                            <th class='bluecellthin-th' width='9%' align='center'>{lang.required}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.edit}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.delete}</th>
                        </tr>
                        ". $field_rows ."
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=cdfields&amp;act=add' ),
                            array( 'arrow_switch', '{lang.menu_reorder}', '<! TD_URL !>/admin.php?section=manage&amp;page=cdfields&amp;act=reorder' ),
                            array( 'compile', '{lang.menu_cache}', '<! TD_URL !>/admin.php?section=tools&amp;page=cache&amp;act=dorebuild&amp;id=cdfields' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_cdfields_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_cdfields_title}', '{lang.help_about_cdfields_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Add Field
    #=======================================

    private function add_field($error='')
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'cdfields', 'add' );

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;
        }

        $options_html = "<div id='opts_textfield'>{lang.size} ". $this->trellis->skin->textfield( 'opts_size', '', '', 0, 4 ) ."</div>
                        <div id='opts_textarea' style='display:none'>{lang.columns} ". $this->trellis->skin->textfield( 'opts_cols', '', '', 0, 4 ) ."&nbsp;&nbsp;{lang.rows} ". $this->trellis->skin->textfield( 'opts_rows', '', '', 0, 4 ) ."</div>
                        <div id='opts_dcr' style='display:none'>
                            <input type='hidden' name='opts_num' id='opts_num' value='1' />
                            <div id='opts_dcr_1'>{lang.key} <input type='text' name='opts_dcr_keys[]' id='opts_dcr_keys_1' value='' size='8' />&nbsp;&nbsp;{lang.name} <input type='text' name='opts_dcr_names[]' id='opts_dcr_names_1' value='' size='28' /> <img src='<! IMG_DIR !>/icon_circle_plus.png' alt='+' style='vertical-align:middle;cursor:pointer' onclick='addDCRopt()' /></div>
                        </div>";

        $depart_perms_html = "<table width='100%' cellpadding='0' cellspacing='0'>";
        $depart_count = 0;
        $depart_total = count( $this->trellis->cache->data['departs'] );

        foreach( $this->trellis->cache->data['departs'] as $did => $d )
        {
            $depart_count ++;

            if ( $depart_count > 2 ) $padding = " style='padding-top:5px'";

            if ( $depart_count & 1 )
            {
                if ( $depart_count == $depart_total )
                {
                    $depart_perms_html .= "<tr><td colspan='2'{$padding}>". $this->trellis->skin->checkbox( 'dp_'. $d['id'], $d['name'] ) ."</td></tr>";
                }
                else
                {
                    $depart_perms_html .= "<tr><td width='35%'{$padding}>". $this->trellis->skin->checkbox( 'dp_'. $d['id'], $d['name'] ) ."</td>";
                }
            }
            else
            {
                $depart_perms_html .= "<td width='65%'{$padding}>". $this->trellis->skin->checkbox( 'dp_'. $d['id'], $d['name'] ) ."</td></tr>";
            }
        }

        $depart_perms_html .= "</table>";

        $this->output .= "<script type='text/javascript'>

                        function showOpts() {
                            var opts = new Array( 'opts_textfield', 'opts_textarea', 'opts_dcr' );

                            var opt_selected = $('#type').val();

                            if ( opt_selected == 'dropdown' || opt_selected == 'checkbox' || opt_selected == 'radio' ) opt_selected = 'dcr';

                            for ( i=0; i<opts.length; i++ )
                            {
                                if ( $('#'+opts[i]).css('display') != 'none' && opts[i] != 'opts_'+ opt_selected ) $('#'+opts[i]).hide('blind');
                            }

                            if ( $('#opts_'+ opt_selected).css('display') == 'none' ) $('#opts_'+ opt_selected).show('blind');
                        }

                        function addDCRopt() {
                            var next_opt = parseInt( $('#opts_num').val() ) + 1;
                            $('#opts_num').val(next_opt);

                            $('#opts_dcr').append(\"<div id='opts_dcr_\"+ next_opt +\"' style='padding-top:4px;display:none'>{lang.key} <input type='text' name='opts_dcr_keys[]' id='opts_dcr_keys_\"+ next_opt +\"' value='' size='8' />&nbsp;&nbsp;{lang.name} <input type='text' name='opts_dcr_names[]' id='opts_dcr_names_\"+ next_opt +\"' value='' size='28' /> <img src='<! IMG_DIR !>/icon_minus_circle.png' alt='-' style='vertical-align:middle;cursor:pointer' onclick='removeDCRopt(\"+ next_opt +\")' />\");

                            $('#opts_dcr_'+ next_opt).show('blind');
                        }

                        function removeDCRopt(i) {
                            $('#opts_dcr_'+ i).hide('blind',function(){ $('#opts_dcr_'+ i).remove() });
                        }

                        </script>
                        <div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=cdfields&amp;act=doadd", 'add_cdfield', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.adding_cdfield}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.name}', $this->trellis->skin->textfield( 'name' ), 'a', '28%', '72%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.type}', $this->trellis->skin->drop_down( 'type', array( 'textfield' => '{lang.textfield}', 'textarea' => '{lang.textarea}', 'dropdown' => '{lang.dropdown}', 'checkbox' => '{lang.checkbox}', 'radio' => '{lang.radio}' ), '', '', 0, 'showOpts()' ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.additional_options} '. $this->trellis->skin->help_tip('{lang.tip_additional_options}'), $options_html, 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.required} '. $this->trellis->skin->help_tip('{lang.tip_required}'), $this->trellis->skin->yes_no_radio( 'required' ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.department_permissions} '. $this->trellis->skin->help_tip('{lang.tip_depart_perms}'), $depart_perms_html, 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'add', '{lang.button_add_cdfield}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'name'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_name}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );
        $this->output .= $this->trellis->skin->focus_js('name');

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=cdfields' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_cdfields_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit Field
    #=======================================

    private function edit_field($error='')
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'cdfields', 'edit' );

        if ( ! $f = $this->trellis->func->cdfields->get_single_by_id( 'all', $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_cdfield');

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;
        }

        $f['extra'] = unserialize( $f['extra'] );

        if ( $f['type'] == 'textfield' )
        {
            $opts_show_textfield = '';
            $opts_show_textarea = 'display:none';
            $opts_show_dcr = 'display:none';

            $opts_size = $f['extra']['size'];
        }
        elseif ( $f['type'] == 'textarea' )
        {
            $opts_show_textfield = 'display:none';
            $opts_show_textarea = '';
            $opts_show_dcr = 'display:none';

            $opts_cols = $f['extra']['cols'];
            $opts_rows = $f['extra']['rows'];
        }
        elseif ( $f['type'] == 'dropdown' || $f['type'] == 'checkbox' || $f['type'] == 'radio' )
        {
            $opts_show_textfield = 'display:none';
            $opts_show_textarea = 'display:none';
            $opts_show_dcr = '';

            $opts_rows_html = "";
            $opts_count = 0;

            foreach( $f['extra'] as $key => $name )
            {
                $opts_count ++;

                if ( $opts_count == 1 )
                {
                    $opts_key_1 = $key;
                    $opts_name_1 = $name;
                }
                else
                {
                    $opts_rows_html .= "<div id='opts_dcr_{$opts_count}'>{lang.key} <input type='text' name='opts_dcr_keys[]' id='opts_dcr_keys_{$opts_count}' value='{$key}' size='8' />&nbsp;&nbsp;{lang.name} <input type='text' name='opts_dcr_names[]' id='opts_dcr_names_{$opts_count}' value='{$name}' size='28' /> <img src='<! IMG_DIR !>/icon_minus_circle.png' alt='-' style='vertical-align:middle;cursor:pointer' onclick='removeDCRopt({$opts_count})' /></div>";
                }
            }
        }

        $options_html = "<div id='opts_textfield' style='{$opts_show_textfield}'>{lang.size} ". $this->trellis->skin->textfield( 'opts_size', $opts_size, '', 0, 4 ) ."</div>
                        <div id='opts_textarea' style='{$opts_show_textarea}'>{lang.columns} ". $this->trellis->skin->textfield( 'opts_cols', $opts_cols, '', 0, 4 ) ."&nbsp;&nbsp;{lang.rows} ". $this->trellis->skin->textfield( 'opts_rows', $opts_rows, '', 0, 4 ) ."</div>
                        <div id='opts_dcr' style='{$opts_show_dcr}'>
                            <input type='hidden' name='opts_num' id='opts_num' value='{$opts_count}' />
                            <div id='opts_dcr_1'>{lang.key} <input type='text' name='opts_dcr_keys[]' id='opts_dcr_keys_1' value='{$opts_key_1}' size='8' />&nbsp;&nbsp;{lang.name} <input type='text' name='opts_dcr_names[]' id='opts_dcr_names_1' value='{$opts_name_1}' size='28' /> <img src='<! IMG_DIR !>/icon_circle_plus.png' alt='+' style='vertical-align:middle;cursor:pointer' onclick='addDCRopt()' /></div>
                            {$opts_rows_html}
                        </div>";

        $depart_perms_html = "<table width='100%' cellpadding='0' cellspacing='0'>";
        $depart_count = 0;
        $depart_total = count( $this->trellis->cache->data['departs'] );

        $f['departs'] = unserialize( $f['departs'] );

        foreach( $this->trellis->cache->data['departs'] as $did => $d )
        {
            $depart_count ++;

            if ( $depart_count > 2 ) $padding = " style='padding-top:5px'";

            if ( $depart_count & 1 )
            {
                if ( $depart_count == $depart_total )
                {
                    $depart_perms_html .= "<tr><td colspan='2'{$padding}>". $this->trellis->skin->checkbox( 'dp_'. $d['id'], $d['name'], $f['departs'][ $d['id'] ] ) ."</td></tr>";
                }
                else
                {
                    $depart_perms_html .= "<tr><td width='35%'{$padding}>". $this->trellis->skin->checkbox( 'dp_'. $d['id'], $d['name'], $f['departs'][ $d['id'] ] ) ."</td>";
                }
            }
            else
            {
                $depart_perms_html .= "<td width='65%'{$padding}>". $this->trellis->skin->checkbox( 'dp_'. $d['id'], $d['name'], $f['departs'][ $d['id'] ] ) ."</td></tr>";
            }
        }

        $depart_perms_html .= "</table>";

        $this->output .= "<script type='text/javascript'>

                        function showOpts() {
                            var opts = new Array( 'opts_textfield', 'opts_textarea', 'opts_dcr' );

                            var opt_selected = $('#type').val();

                            if ( opt_selected == 'dropdown' || opt_selected == 'checkbox' || opt_selected == 'radio' ) opt_selected = 'dcr';

                            for ( i=0; i<opts.length; i++ )
                            {
                                if ( $('#'+opts[i]).css('display') != 'none' && opts[i] != 'opts_'+ opt_selected ) $('#'+opts[i]).hide('blind');
                            }

                            if ( $('#opts_'+ opt_selected).css('display') == 'none' ) $('#opts_'+ opt_selected).show('blind');
                        }

                        function addDCRopt() {
                            var next_opt = parseInt( $('#opts_num').val() ) + 1;
                            $('#opts_num').val(next_opt);

                            $('#opts_dcr').append(\"<div id='opts_dcr_\"+ next_opt +\"' style='padding-top:4px;display:none'>{lang.key} <input type='text' name='opts_dcr_keys[]' id='opts_dcr_keys_\"+ next_opt +\"' value='' size='8' />&nbsp;&nbsp;{lang.name} <input type='text' name='opts_dcr_names[]' id='opts_dcr_names_\"+ next_opt +\"' value='' size='28' /> <img src='<! IMG_DIR !>/icon_minus_circle.png' alt='-' style='vertical-align:middle;cursor:pointer' onclick='removeDCRopt(\"+ next_opt +\")' />\");

                            $('#opts_dcr_'+ next_opt).show('blind');
                        }

                        function removeDCRopt(i) {
                            $('#opts_dcr_'+ i).hide('blind',function(){ $('#opts_dcr_'+ i).remove() });
                        }

                        </script>
                        <div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=cdfields&amp;act=doedit&amp;id={$f['id']}", 'edit_cdfield', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.editing_cdfield} '. $f['name'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.name}', $this->trellis->skin->textfield( 'name', $f['name'] ), 'a', '28%', '72%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.type}', $this->trellis->skin->drop_down( 'type', array( 'textfield' => '{lang.textfield}', 'textarea' => '{lang.textarea}', 'dropdown' => '{lang.dropdown}', 'checkbox' => '{lang.checkbox}', 'radio' => '{lang.radio}' ), $f['type'], '', 0, 'showOpts()' ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.additional_options} '. $this->trellis->skin->help_tip('{lang.tip_additional_options}'), $options_html, 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.required} '. $this->trellis->skin->help_tip('{lang.tip_required}'), $this->trellis->skin->yes_no_radio( 'required', $f['required'] ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.department_permissions} '. $this->trellis->skin->help_tip('{lang.tip_depart_perms}'), $depart_perms_html, 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_cdfield}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'name'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_name}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=cdfields' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=cdfields&amp;act=add' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_cdfields_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Reorder Fields
    #=======================================

    private function reorder_fields()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'cdfields', 'reorder' );

        #=============================
        # Do Output
        #=============================

        $this->output = "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=cdfields&amp;act=doreorder", 'reorder_cdfields', 'post', 'return getOrder()' ) ."
                        ". $this->trellis->skin->group_title( '{lang.reordering_cdfields}' ) ."
                        ". $this->trellis->skin->group_sub( '{lang.reorder_cdfields_msg}' ) ."
                        <input type='hidden' name='order' id='order' value='' />";

        if ( ! $fields = $this->trellis->func->cdfields->get( array( 'select' => array( 'id', 'name', 'type' ), 'order' => array( 'position' => 'asc' ) ) ) )
        {
            $this->output .= "<div class='bluecell-light'><strong><a href='<! TD_URL !>/admin.php?section=manage&amp;page=cdfields&amp;act=add'>{lang.no_cdfields}</a></strong></div>";
        }
        else
        {
            $this->output .= "<ul class='draggable' id='sortable'>";

            foreach( $fields as $fid => $f )
            {
                if ( $f['type'] == 'textfield' )
                {
                    $f['type'] = '{lang.textfield}';
                }
                elseif ( $f['type'] == 'textarea' )
                {
                    $f['type'] = '{lang.textarea}';
                }
                elseif ( $f['type'] == 'dropdown' )
                {
                    $f['type'] = '{lang.dropdown}';
                }
                elseif ( $f['type'] == 'checkbox' )
                {
                    $f['type'] = '{lang.checkbox}';
                }
                elseif ( $f['type'] == 'radio' )
                {
                    $f['type'] = '{lang.radio}';
                }

                $this->output .= "<li class='bluecell-light' id='f_{$f['id']}'>{$f['name']} <span style='font-weight: normal'>({$f['type']})</span></li>";
            }

            $this->output .= "</ul>
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'reorder', '{lang.button_reorder_cdfields}' ) ) ."
                        </div>
                        <script type='text/javascript' language='javascript'>
                            $(function() {
                                $('#sortable').sortable({
                                    stop: function() {
                                        $('#order').val( $('#sortable').sortable('serialize') );
                                    }
                                });
                            });
                        </script>";
        }

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=cdfields' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=cdfields&amp;act=add' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_cdfields_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Add Field
    #=======================================

    private function do_add()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'cdfields', 'add' );

        if ( ! $this->trellis->input['name'] ) $this->add_field('no_name');

        if ( $this->trellis->input['type'] == 'dropdown' || $this->trellis->input['type'] == 'checkbox' || $this->trellis->input['type'] == 'radio' )
        {
            if ( empty( $this->trellis->input['opts_dcr_keys'] ) ) $this->add_field('no_opts_key');
            if ( empty( $this->trellis->input['opts_dcr_names'] ) ) $this->add_field('no_opts_name');
        }

        #=============================
        # Generate Options
        #=============================

        $options = array();

        if ( $this->trellis->input['type'] == 'textfield' )
        {
            $options['size'] = $this->trellis->input['opts_size'];
        }
        elseif ( $this->trellis->input['type'] == 'textarea' )
        {
            $options['cols'] = $this->trellis->input['opts_cols'];
            $options['rows'] = $this->trellis->input['opts_rows'];
        }
        if ( $this->trellis->input['type'] == 'dropdown' || $this->trellis->input['type'] == 'checkbox' || $this->trellis->input['type'] == 'radio' )
        {
            for( $i=0; $i<count($this->trellis->input['opts_dcr_keys']); $i++ )
            {
                $options[ $this->trellis->input['opts_dcr_keys'][ $i ] ] = $this->trellis->input['opts_dcr_names'][ $i ];
            }
        }

        #=============================
        # Generate Permissions
        #=============================

        $depart_perm = array();

        foreach ( $this->trellis->cache->data['departs'] as $did => $d )
        {
            $depart_perm[ $did ] = intval( $this->trellis->input[ 'dp_'. $did ] );
        }

        #=============================
        # Add Field
        #=============================

        $db_array = array(
                          'name'            => $this->trellis->input['name'],
                          'type'            => $this->trellis->input['type'],
                          'extra'            => $options,
                          'required'        => $this->trellis->input['required'],
                          'departs'            => $depart_perm,
                         );

        $field_id = $this->trellis->func->cdfields->add( $db_array );

        $this->trellis->log( array( 'msg' => array( 'dfield_added', $this->trellis->input['name'] ), 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->dfields_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_cdfield_added'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Edit Field
    #=======================================

    private function do_edit()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'cdfields', 'edit' );

        if ( ! $this->trellis->input['name'] ) $this->edit_field('no_name');

        if ( $this->trellis->input['type'] == 'dropdown' || $this->trellis->input['type'] == 'checkbox' || $this->trellis->input['type'] == 'radio' )
        {
            if ( empty( $this->trellis->input['opts_dcr_keys'] ) ) $this->edit_field('no_opts_key');
            if ( empty( $this->trellis->input['opts_dcr_names'] ) ) $this->edit_field('no_opts_name');
        }

        #=============================
        # Generate Options
        #=============================

        $options = array();

        if ( $this->trellis->input['type'] == 'textfield' )
        {
            $options['size'] = $this->trellis->input['opts_size'];
        }
        elseif ( $this->trellis->input['type'] == 'textarea' )
        {
            $options['cols'] = $this->trellis->input['opts_cols'];
            $options['rows'] = $this->trellis->input['opts_rows'];
        }
        if ( $this->trellis->input['type'] == 'dropdown' || $this->trellis->input['type'] == 'checkbox' || $this->trellis->input['type'] == 'radio' )
        {
            for( $i=0; $i<count($this->trellis->input['opts_dcr_keys']); $i++ )
            {
                $options[ $this->trellis->input['opts_dcr_keys'][ $i ] ] = $this->trellis->input['opts_dcr_names'][ $i ];
            }
        }

        #=============================
        # Generate Permissions
        #=============================

        $depart_perm = array();

        foreach ( $this->trellis->cache->data['departs'] as $did => $d )
        {
            $depart_perm[ $did ] = intval( $this->trellis->input[ 'dp_'. $did ] );
        }

        #=============================
        # Edit Field
        #=============================

        $db_array = array(
                          'name'            => $this->trellis->input['name'],
                          'type'            => $this->trellis->input['type'],
                          'extra'            => $options,
                          'required'        => $this->trellis->input['required'],
                          'departs'            => $depart_perm,
                         );

        $this->trellis->func->cdfields->edit( $db_array, $this->trellis->input['id'] );

        $this->trellis->log( array( 'msg' => array( 'dfield_edited', $this->trellis->input['name'] ), 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->dfields_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_cdfield_updated'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Delete Field
    #=======================================

    private function do_delete()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'cdfields', 'delete' );

        #=============================
        # Delete Field
        #=============================

        $this->trellis->func->cdfields->delete( $this->trellis->input['id'] );

        $this->trellis->log( array( 'msg' => array( 'dfield_deleted', $this->trellis->cache->data['dfields'][ $this->trellis->input['id'] ]['name'] ), 'type' => 'other', 'level' => 2 ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->dfields_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'error', $this->trellis->lang['error_cdfield_deleted'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Reorder Fields
    #=======================================

    private function do_reorder()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'cdfields', 'reorder' );

        #=============================
        # Reorder Departments
        #=============================

        parse_str( str_replace( '&amp;', '&', $this->trellis->input['order'] ), $order );

        if ( empty( $order['f'] ) ) $this->list_fields( 'no_reorder' );

        if ( $fields = $this->trellis->func->cdfields->get( array( 'select' => array( 'id', 'position' ) ) ) )
        {
            foreach ( $order['f'] as $position => $fid )
            {
                $position ++;

                if ( $position != $fields[ $fid ]['position'] )
                {
                    $this->trellis->func->cdfields->edit( array( 'position' => $position ), $fid );
                }
            }
        }

        $this->trellis->log( array( 'msg' => 'dfields_reordered', 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->dfields_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_cdfields_reordered'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

}
?>