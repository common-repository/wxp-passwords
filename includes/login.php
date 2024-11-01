<?php
if(!$this->wxp_valid){
	$title = isset($atts['title']) ? '<h2>'.$atts['title'].'</h2>' : '';
	?>
	<div class="wxp-sign-in-box">
		<?php echo $title; ?>
		<?php echo isset($_GET['wxp-err']) && $_GET['wxp-err']=='true' ? '<div class="wxp-pass-err">'.__('Please enter correct password.','wxp-passwords').'</div>' : '';  ?>
		<form method="post" action="<?php echo add_query_arg('action','wxp_pass_check',admin_url('admin-ajax.php')); ?>">
			<div class="form-group wxp-pass-row">
				<input type="password" name="password" id="password" class="form-control password-input-white" placeholder="Password" autocomplete="off">
			</div>
			<?php wp_nonce_field('wxp_ajax_login','wxp_security',false); ?>
			<input type="submit" name="submit" class="button button--white button--black-shadow" value="Sign In">
		</form>
	</div>
	<?php
}
else
{
	?>
	<div class="wxp-sign-in-box">
		<div class="wxp-sign-msg"><?php echo __('Only allowed pages are accessible to your account.','wxp-pro-manager'); ?></div>
	</div>
	<?php
}
?>