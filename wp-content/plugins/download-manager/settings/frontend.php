

                 <div class="panel panel-default">
                     <div class="panel-heading"><?php echo __('Front-end UI Settings','wpdmpro'); ?></div>
                     <div class="panel-body">

                         <div class="form-group">
                             <label><?php echo __('Allowed User Roles to Create Package From Front-end','wpdmpro'); ?></label>
                             <select name="__wpdm_front_end_access[]" class="chzn-select role" multiple="multiple" id="fronend-ui-access" style="min-width: 450px">
                                 <?php

                                 $currentAccess = maybe_unserialize(get_option( '__wpdm_front_end_access', array()));
                                 $selz = '';

                                 ?>

                                 <?php
                                 global $wp_roles;
                                 $roles = array_reverse($wp_roles->role_names);
                                 foreach( $roles as $role => $name ) {



                                     if(  $currentAccess ) $sel = (in_array($role,$currentAccess))?'selected=selected':'';
                                     else $sel = '';



                                     ?>
                                     <option value="<?php echo $role; ?>" <?php echo $sel  ?>> <?php echo $name; ?></option>
                                 <?php } ?>
                             </select>

                         </div>
                         <div class="form-group">
                             <label><?php echo __('Message For Blocked Users:','wpdmpro'); ?></label>
                             <textarea id="__wpdm_front_end_access_blocked" name="__wpdm_front_end_access_blocked" class="form-control"><?php echo stripslashes(get_option('__wpdm_front_end_access_blocked'));?></textarea>
                         </div>


                         <div class="form-group">
                             <label><?php echo __('When Someone Create a Package:','wpdmpro'); ?></label><br/>
                             <select name="__wpdm_ips_frontend">
                                 <option value="publish"><?php echo __('Publish Instantly'); ?></option>
                                 <option value="pending" <?php selected(get_option('__wpdm_ips_frontend'), 'pending'); ?>><?php echo __('Pending for Review', 'wpdmpro'); ?></option>
                             </select>
                         </div>

                         <div class="form-group">
                             <label><?php echo __('When File Already Exists:','wpdmpro'); ?></label><br/>
                             <select name="__wpdm_overwrite_file_frontend">
                                 <option value="0"><?php echo __('Rename New File'); ?></option>
                                 <option value="1" <?php echo get_option('__wpdm_overwrite_file_frontend',0)==1?'selected=selected':''; ?>><?php echo __('Overwrite', 'wpdmpro'); ?></option>
                             </select>
                         </div>

                         <div class="form-group">
                         <label><?php echo __('Allowed File Types From Front-end:','wpdmpro'); ?></label>
                         <input type="text" class="form-control" value="<?php echo get_option('__wpdm_allowed_file_types','*'); ?>" name="__wpdm_allowed_file_types" />
                         </div>

                         <div class="form-group">
                         <label><?php echo __('Max Upload Size From Front-end','wpdmpro'); ?></label>
                         <input type="text" class="form-control" style="width: 100px;display: inline" title="0 for system default" name="__wpdm_max_upload_size" value="<?php echo get_option('__wpdm_max_upload_size',(wp_max_upload_size()/1048576)); ?>"> MB<br/>
                         </div>






                     </div>
                 </div>

                 <div class="panel panel-default">
                     <div class="panel-heading"><?php echo __('Category Page Options','wpdmpro'); ?></div>
                     <div class="panel-body">
                        <fieldset id="cpi">
                            <legend><label><input type="radio" name="__wpdm_cpage_style"  <?php checked(get_option('__wpdm_cpage_style'),'basic'); ?> value="basic"> Use Basic Style</label></legend>

                         <div class="form-group">
                             <?php
                              $cpageinfo = get_option('__wpdm_cpage_info');
                             ?>
                             <label><?php echo __('Select Package Info To Show in Category Page:','wpdmpro'); ?></label><br/>
                             <label><input <?php checked(isset($cpageinfo['version']),1); ?> type="checkbox" name="__wpdm_cpage_info[version]" value="1"> <?php echo __('Show Version','wpdmpro'); ?></label><br/>
                             <label><input <?php checked(isset($cpageinfo['view_count']),1); ?> type="checkbox" name="__wpdm_cpage_info[view_count]" value="1"> <?php echo __('Show View Count','wpdmpro'); ?></label><br/>
                             <label><input <?php checked(isset($cpageinfo['download_count']),1); ?> type="checkbox" name="__wpdm_cpage_info[download_count]" value="1"> <?php echo __('Show Download Count','wpdmpro'); ?></label><br/>
                             <label><input <?php checked(isset($cpageinfo['package_size']),1); ?> type="checkbox" name="__wpdm_cpage_info[package_size]" value="1"> <?php echo __('Show Package Size','wpdmpro'); ?></label><br/>
                             <label><input <?php checked(isset($cpageinfo['download_link']),1); ?> type="checkbox" name="__wpdm_cpage_info[download_link]" value="1"> <?php echo __('Show Download Link','wpdmpro'); ?></label>

                         </div>

                         <div class="form-group">
                             <label><?php echo __('Show Package Info:','wpdmpro'); ?></label><br/>
                             <select name="__wpdm_cpage_excerpt">
                                 <option value="after"><?php echo __('After Excerpt','wpdmpro'); ?></option>
                                 <option value="before" <?php selected(get_option('__wpdm_cpage_excerpt'), 'before'); ?>><?php echo __('Before Excerpt','wpdmpro'); ?></option>
                             </select>
                         </div>
                        </fieldset>

                         <fieldset id="cpi">
                             <legend><label><input type="radio" name="__wpdm_cpage_style" <?php checked(get_option('__wpdm_cpage_style'),'ltpl'); ?> value="ltpl"> Use Link Template</label></legend>



                             <div class="form-group">
                                 <label><?php echo __('Select Link Template:','wpdmpro'); ?></label><br/>
                                 <select name="__wpdm_cpage_template" id="lnk_tpl" onchange="jQuery('#lerr').remove();">
                                     <?php
                                     $ctpls = scandir(WPDM_BASE_DIR.'/templates/');
                                     array_shift($ctpls);
                                     array_shift($ctpls);
                                     $ptpls = $ctpls;
                                     foreach($ctpls as $ctpl){
                                         $tmpdata = file_get_contents(WPDM_BASE_DIR.'/templates/'.$ctpl);
                                         if(preg_match("/WPDM[\s]+Link[\s]+Template[\s]*:([^\-\->]+)/",$tmpdata, $matches)){

                                             ?>
                                             <option value="<?php echo $ctpl; ?>"  <?php selected(get_option('__wpdm_cpage_template'),$ctpl); ?>><?php echo $matches[1]; ?></option>
                                         <?php
                                         }
                                     }
                                     $templates = get_option("_fm_link_templates",true);
                                     if($templates) $templates = maybe_unserialize($templates);
                                     if(is_array($templates)){
                                         foreach($templates as $id=>$template) {
                                             ?>
                                             <option value="<?php echo $id; ?>"  <?php selected(get_option('__wpdm_cpage_template'),$id); ?>><?php echo $template['title']; ?></option>
                                         <?php } } ?>
                                 </select><br/>
                                 <em><?php echo __('Selected link template will replace the excerpt','wpdmpro'); ?></em>
                             </div>
                         </fieldset>





                     </div>
                 </div>


<style> fieldset#cpi { border: 1px solid #dddddd !important; padding: 0 20px; margin: 0 0 20px 0; border-radius: 3px !important; } fieldset#cpi legend{ font-size: 11pt; margin: 0;padding: 10px; border:0; width:auto; font-weight: 700; } fieldset#cpi legend input { margin: 0 !important; }</style>