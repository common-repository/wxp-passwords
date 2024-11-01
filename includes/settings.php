<div class="wxp-pass-box">
	<form method="post" id="wxp-pass-frm">
		<div class="wxp-setting-box">
			<div class="wxp-pass-row wxp-settings-row">
				<label><?php echo __('Settings','wxp-pro-manager'); ?></label>
			</div>
			<div class="wxp-pass-row"><div class="wxp-pass-msg"></div></div>
			<div class="wxp-pass-row">
				<label><?php echo __('Select password login page','wxp-pro-manager'); ?></label>
				<?php
				$login_page_id = get_option('_wxp_password_login_page');
				wp_dropdown_pages(array('selected'=>$login_page_id,'show_option_none'=>__('Select login page','wxp-pro-manager')));
				?>
			</div>
			<div class="wxp-pass-row">
				<label><?php echo __('Exclude Home Page','wxp-pro-manager'); ?></label>
				<?php
				$exclude_home = get_option('_wxp_exclude_home_page');
				$value = isset($exclude_home) && $exclude_home=='no' ? 'no' : 'yes';
				$checked = $value=='yes' ? 'checked' : '';
				?>
				<input type="checkbox" name="wxp-pass-exclude-home" <?php echo $checked; ?>>
			</div>
			<div class="wxp-pass-row">
				<button type="button" name="wxp-save-settings" class="button button-primary button-large wxp-save-settings"><?php echo __('Save Settings','wxp-pro-manager'); ?></button>
			</div>
		</div>
	</form>
</div>