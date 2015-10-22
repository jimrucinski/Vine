
<link rel="stylesheet" href="<?php echo plugins_url('/download-manager/css/demo_table.css'); ?>"/>
<script language="JavaScript"
        src="<?php echo plugins_url('/download-manager/js/jquery.dataTables.min.js'); ?>"></script>
<script type="text/javascript">
    function filelist_dt() {
        jQuery("#wpdm-files").dataTable({
            "iDisplayLength": -1,
            "aLengthMenu": [
                [-1],
                ["All"]
            ],
            "aoColumns": [
                { "bSortable": false },
                null,
                null,
                { "bSortable": false }

            ] });
    }
    jQuery(document).ready(function () {
        filelist_dt();
        jQuery("#wpdm-files tbody").sortable();
        jQuery("#adpcon").sortable({placeholder: "adp-ui-state-highlight"});


    });
</script>

 
            <div id="currentfiles">

                <?php

                $files = maybe_unserialize(get_post_meta($post->ID, '__wpdm_files', true));
                
                if (!is_array($files)) $files = array();

                ?>

                <table class="table table-striped" id="wpdm-files">
                    <thead>
                    <tr>
                        <th style="width: 50px;background: transparent;"><?php echo __("Action", "wpdmpro"); ?></th>
                        <th style="width: 40%;"><?php echo __("Filename", "wpdmpro"); ?></th>
                        <th style="width: 40%;"><?php echo __("Title", "wpdmpro"); ?></th>
                        <th style="width: 130px;background: transparent;"><?php echo __("Password", "wpdmpro"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $file_index = 0;
                    $fileinfo = get_post_meta($post->ID, '__wpdm_fileinfo', true);
                    if (!$fileinfo) $fileinfo = array();
                    foreach ($files as $value): ++$file_index;
                        if (!@is_array($fileinfo[$value])) $fileinfo[$value] = array('title'=>'','password'=>'');

                        ?>
                        <tr class="cfile">
                            <td style="width: 50px;">
                                <input class="fa" type="hidden" value="<?php echo $value; ?>" name="file[files][]">
                                <img align="left" rel="del"
                                     src="<?php echo plugins_url('download-manager/images/minus.png'); ?>">
                            </td>
                            <td style="width: 40%;"><div style="height: 20px;overflow: hidden" title="<?php echo $value; ?>" class="ttip"><?php echo $value; ?></div></td>
                            <td style="width: 35%;"><input type="text"
                                    style="width:99%;height:25px;max-width:99%;min-width:99%;"
                                    name='file[fileinfo][<?php echo $value; ?>][title]' class="form-control" value="<?php echo $fileinfo[$value]['title']; ?>" />
                            </td>
                            <td style="width: 150px;"><input style="height: 25px;width:50px;display: inline" class="form-control" type="text"
                                                             id="indpass_<?php echo $file_index; ?>"
                                                             name='file[fileinfo][<?php echo $value; ?>][password]'
                                                             value="<?php echo $fileinfo[$value]['password']; ?>"> <button
                                     class="genpass btn btn-warning btn-xs"
                                    title='Generate Password'
                                    onclick="return generatepass('indpass_<?php echo $file_index; ?>')">
                                    <i class="icon icon-key"></i></button></td>
                        </tr>
                    <?php
                    endforeach;
                    ?>
                    </tbody>
                </table>


                <?php if ($files): ?>
                    <script type="text/javascript">

                        jQuery('.ttip').tooltip();
                        jQuery('img[rel=del], img[rel=undo]').click(function () {

                            if (jQuery(this).attr('rel') == 'del') {

                                jQuery(this).parents('tr.cfile').removeClass('cfile').addClass('dfile').find('input.fa').attr('name', 'del[]');
                                jQuery(this).attr('rel', 'undo').attr('src', '<?php echo plugins_url(); ?>/download-manager/images/add.png').attr('title', 'Undo Delete');

                            } else {


                                jQuery(this).parents('tr.dfile').removeClass('dfile').addClass('cfile').find('input.fa').attr('name', 'file[files][]');
                                jQuery(this).attr('rel', 'del').attr('src', '<?php echo plugins_url(); ?>/download-manager/images/minus.png').attr('title', 'Delete File');


                            }


                        });


                    </script>


                <?php endif; ?>


            </div>


