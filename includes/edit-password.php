<div class="wxp-pass-box">
	<form method="post" id="wxp-pass-frm">
		<div class="wxp-password-add-box">
            <div class="wxp-pass-row"><div class="wxp-pass-msg"></div></div>
			<div class="wxp-pass-row">
				<label><?php echo __('Password','wxp-pro-manager'); ?></label>
				<input type="text" name="wxp-pass-password" class="wxp-pass-input" value="<?php echo isset($row->password_text) ? $row->password_text : ''; ?>">
			</div>
			<div class="wxp-pass-row">
				<label><?php echo __('Valid for','wxp-pro-manager'); ?></label>
				<div class="wxp-time-selection">
					<div class="wxp-hour-selection">
						<select class="wxp-pass-input" name="wxp-pass-hour">
							<option value="0"><?php echo __('Hour','wxp-pro-manager'); ?></option>
							<?php
							for($h=0;$h<=24;$h++){
							    $selected = $h==$hour ? 'selected' : '';
								echo '<option value="'.$h.'" '.$selected.'>'.$h.'</option>';
							}
							?>
						</select>
					</div>
					<div class="wxp-minute-selection">
						<select class="wxp-pass-input" name="wxp-pass-minute">
							<option value="0"><?php echo __('Minute','wxp-pro-manager'); ?></option>
							<?php
							for($m=0;$m<=60;$m++){
								$selected = $m==$minute ? 'selected' : '';
								echo '<option value="'.$m.'" '.$selected.'>'.$m.'</option>';
							}
							?>
						</select>
					</div>
					<div class="wxp-second-selection">
						<select class="wxp-pass-input" name="wxp-pass-second">
							<option value="0"><?php echo __('Second','wxp-pro-manager'); ?></option>
							<?php
							for($s=0;$s<=60;$s++){
								$selected = $s==$second ? 'selected' : '';
								echo '<option value="'.$s.'" '.$selected.'>'.$s.'</option>';
							}
							?>
						</select>
					</div>
				</div>
			</div>
            <div class="wxp-pass-row">
                <label><?php echo __('Permission','wxp-pro-manager'); ?></label>
                <div class="wxp-pass-permission">
                    <div class="wxp-pass-allow">
                        <label><?php echo __('Allow access','wxp-pro-manager'); ?></label>
                        <ul>
							<?php
							$post_types = get_post_types(array('public'=>true));
							foreach($post_types as $type){
								if(in_array($type,array('attachment'))){
									continue;
								}
								$typeobj = get_post_type_object($type);
								echo '<li class="wxp-pass-post-types">';
								echo '<label>'.$typeobj->label.'</label>';
								$posts = get_posts(array(
									'post_type'=> $typeobj->name,
									'posts_per_page'=> -1,
									'order'    => 'DESC'
								));
								if(is_array($posts) && !empty($posts)){
									echo '<ul>';
									foreach($posts as $post){
										if($post->ID==$this->wxp_login_page){
											continue;
										}
										if($this->wxp_exclude_home && $this->wxp_home_page){
											if($post->ID==$this->wxp_home_page){
												continue;
											}
										}
									    $checked = is_array($rules['allow']) && in_array($post->ID,$rules['allow']) ? 'checked' : '';
										echo '<li id="wxp_a_id_'.$post->ID.'"><input name="wxp-allow-types[]" type="checkbox" class="wxp-permission" value="'.$post->ID.'" '.$checked.'>'.$post->post_title.'</li>';
									}
									echo '</ul>';
								}

								echo '</li>';
							}
							?>
                        </ul>
                    </div>
                    <div class="wxp-pass-block">
                        <label><?php echo __('Block access','wxp-pro-manager'); ?></label>
                        <ul>
							<?php

							$post_types = get_post_types(array('public'=>true));
							foreach($post_types as $type){
								if(in_array($type,array('attachment'))){
									continue;
								}
								$typeobj = get_post_type_object($type);
								echo '<li class="wxp-pass-post-types">';
								echo '<label>'.$typeobj->label.'</label>';
								$posts = get_posts(array(
									'post_type'=> $typeobj->name,
									'posts_per_page'=> -1,
									'order'    => 'DESC'
								));
								if(is_array($posts) && !empty($posts)){
									echo '<ul>';
									foreach($posts as $post){
										if($post->ID==$this->wxp_login_page){
											continue;
										}
										if($this->wxp_exclude_home && $this->wxp_home_page){
											if($post->ID==$this->wxp_home_page){
												continue;
											}
										}
										$checked = is_array($rules['block']) && in_array($post->ID,$rules['block']) ? 'checked' : '';
										echo '<li id="wxp_b_id_'.$post->ID.'"><input name="wxp-block-types[]"  type="checkbox" class="wxp-permission" value="'.$post->ID.'" '.$checked.'>'.$post->post_title.'</li>';
									}
									echo '</ul>';
								}

								echo '</li>';
							}
							?>
                        </ul>
                    </div>
                    <div class="wxp-clear"></div>
                </div>
            </div>
			<div class="wxp-pass-row">
                <input type="hidden" name="password_id" value="<?php echo $id; ?>" id="password_id">
				<button type="button" name="wxp-save-pass" class="button button-primary button-large wxp-update-pass"><?php echo __('Save Password','wxp-pro-manager'); ?></button>
			</div>
		</div>
	</form>
</div>