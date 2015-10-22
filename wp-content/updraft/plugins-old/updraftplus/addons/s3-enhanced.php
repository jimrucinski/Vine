<?php
/*
UpdraftPlus Addon: s3-enhanced:Amazon S3, enhanced
Description: Adds enhanced capabilities for Amazon S3 users
Version: 1.0
Shop: /shop/s3-enhanced/
Latest Change: 1.8.9
*/

$updraftplus_addon_s3_enhanced = new UpdraftPlus_Addon_S3_Enhanced;

class UpdraftPlus_Addon_S3_Enhanced {

	public function __construct() {
		add_action('updraft_s3_extra_storage_options', array($this, 'extra_storage_options'));
		add_filter('updraft_s3_storageclass', array($this, 'storageclass'), 10, 3);
	}

	public function storageclass($class, $s3, $opts) {
		return (is_a($s3, 'UpdraftPlus_S3') && is_array($opts) && !empty($opts['rrs'])) ? UpdraftPlus_S3::STORAGE_CLASS_RRS : $class;
	}

	public function extra_storage_options($opts) {
		?>
		<tr class="updraftplusmethod s3">
		<th><?php _e('Reduced redundancy storage', 'updraftplus');?>:<br><a href="https://aws.amazon.com/about-aws/whats-new/2010/05/19/announcing-amazon-s3-reduced-redundancy-storage/"><em><?php _e('(Read more)', 'updraftplus');?></em></a></th>
		<td><input title="<?php echo htmlspecialchars(__("Check this box to use Amazon's reduced redundancy storage and tariff", 'updraftplus')); ?>" type="checkbox" name="updraft_s3[rrs]" id="updraft_s3_rrs" value="1" <?php if (!empty($opts['rrs']))  echo 'checked="checked"';?>/></td>
			</tr>
		<?php
	}

}
