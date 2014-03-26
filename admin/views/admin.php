<?php
/**
 * Anything Catalog Admin Page
 *
 * @package  Anything_Catalog_Admin
 * @author    Ricardo Correia <me@rcorreia.com>, ...
 * @license   GPL-2.0+
 * @link      http://Anything.pt
 * @copyright 2014 - @rfvcorreia, @samsyspt
 */

//Get Defined Options

$CptDetails = (object) get_option('SsysCatalogCPT');

$TaxDetails = get_option('SsysCatalogTax');

if($TaxDetails != FALSE) $TaxDetails = (object) $TaxDetails;

?>
<div class="wrap" id="Anything_catalog">
	<?php
	  if (isset( $_GET['m'] )){
	  	switch($_GET['m']){
			case 1:
				$msg = __('Item Details Updated','Anything-catalog');
				$class ='updated';
			break;
			case 2:
				$msg = __('Taxonomy Details Updated','Anything-catalog');
				$class ='updated';
			break;
			case 3:
				$msg = __('Taxonomy Created','Anything-catalog');
				$class ='updated';
			break;
			case 4:
				$msg = __('Taxonomy Deleted','Anything-catalog');
				$class ='updated';
			break;
	  	}
	  }
	?>
	<div id="icon-options-general" class="icon32"></div>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
	<?php 
	//Print message
	if (isset( $_GET['m'] )){ ?>
	<div class="<?php echo $class; ?>">
		<p><?php echo $msg; ?></p>
	</div>
	<?php } ?>
	
	<div id="poststuff">
	
		<div id="post-body" class="metabox-holder columns-2">
		
			<!-- main content -->
			<div id="post-body-content">
				
				<div class="meta-box-sortables ui-sortable">
					<div class="postbox">
					
						<h3><span><?php _e('Edit Item Details','Anything-catalog'); ?></span></h3>
						<div class="inside">
							<form name="CptDetails" action="admin-post.php" method="post">
								<?php wp_nonce_field( 'Anything-catalog-verify' ); ?>
								
								<p><label>Post Type Name: </label><input name="name" id="" type="text" value="<?php echo $CptDetails->name; ?>" class="regular-text" <?php if(isset( $CptDetails->name )) echo'readonly="readonly"'; ?> /><br /><small>Max 20 characters, can not contain capital letters or spaces. <strong>Reserved post types: post, page, attachment, revision, nav_menu_item.</strong></small></p>
								<hr />
								<p><label>Item Name (Singular): </label><input name="name_singular" id="" type="text" value="<?php echo $CptDetails->name_singular; ?>" class="regular-text" /></p>
								<hr />
								<p><label>Item Name (Plural): </label><input name="name_plural" id="" type="text" value="<?php echo  $CptDetails->name_plural; ?>" class="regular-text" /></p>
								<hr />
								<p><label>Item Label: </label><input name="label" id="" type="text" value="<?php echo $CptDetails->label; ?>" class="regular-text" /></p>
								<hr />
								<p><label>Menu Name: </label><input name="menu_name" id="" type="text" value="<?php echo  $CptDetails->menu_name; ?>" class="regular-text" /></p>
								<hr />
								<p><label>Description: </label><input name="description" id="" type="text" value="<?php echo $CptDetails->description; ?>" class="regular-text" /></p>
								<hr />
								<p><label>Icon URL: </label><input name="iconURL" id="" type="text" value="<?php echo $CptDetails->iconURL; ?>" class="regular-text" /></p>
								<hr />

								<input type="hidden" name="_submitted-form" value="CptDetails" />								
								<input type="hidden" name="action" value="ssysCatalog_save" />
								
								<input class="button-primary" type="submit" name="_submit" value="<?php _e( 'Define Item Details','Anything-catalog' ); ?>" />
							</form>
						</div> <!-- .inside -->
					
					</div> <!-- .postbox -->
					
				</div> <!-- .meta-box-sortables .ui-sortable -->
				<hr />
				<h3><?php _e('Edit Associated Taxonomies','Anything-catalog'); ?></h3>
				<table class="widefat">
					<thead>
						<tr>
							<th class="row-title"><?php _e('Taxonomy Name (slug) <br /><small>Max 20 characters, can not contain capital letters or spaces.<small>','Anything-catalog'); ?></th>
							<th><?php _e('Taxonomy Singular Name','Anything-catalog'); ?></th>
							<th><?php _e('Taxonomy Plural Name','Anything-catalog'); ?></th>
							<th><?php _e('Taxonomy Menu Name','Anything-catalog'); ?></th>
							<th class="upd">&nbsp;</th>
							<th class="del">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						<?php 
						$i = 0;
						if($TaxDetails != FALSE){
							foreach($TaxDetails as $tax){
							?>
							<tr <?php if($i%2 == 0) echo'class="alternate"'; ?> >
								<form name="_TaxUpdate_<?php echo $i; ?>" action="admin-post.php" method="post">
								<?php wp_nonce_field( 'Anything-catalog-verify' ); ?>
								
								<td class="row-title"><input name="name" id="" type="text" value="<?php echo  $tax['name']; ?>" class="all-options" readonly="readonly" /></td>
								<td><input name="name_singular" id="" type="text" value="<?php echo  $tax['name_singular']; ?>" class="all-options" /></td>
								<td><input name="name_plural" id="" type="text" value="<?php echo  $tax['name_plural']; ?>" class="all-options" /></td>
								<td><input name="menu_name" id="" type="text" value="<?php echo  $tax['menu_name']; ?>" class="all-options" /></td>
								<td class="upd"><button class="button-secondary dashicons dashicons-edit" type="submit" name="_Confirm_Edit" value="" /></td>
								<input type="hidden" name="_pos" value="<?php echo $i; ?>" />
								<input type="hidden" name="associated" value="<?php echo $CptDetails->name; ?>" /> 
								<input type="hidden" name="action" value="ssysTaxonomy_update" />
								
								</form>
								<td class="del">
									<form name="_TaxDel_<?php echo $i; ?>" action="admin-post.php" method="post">
										<?php wp_nonce_field( 'Anything-catalog-verify' ); ?>
										
										<input type="hidden" name="_pos" value="<?php echo $i; ?>" />
										<input type="hidden" name="action" value="ssysTaxonomy_delete" />
										<button class="button-secondary dashicons dashicons-no" type="submit" name="_Confirm_Del" value="" /></td>		
									</form>
							</tr>
							<?php
								$i++;
							}
						}
						?>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="6"><a id="newTaxBt" class="button-primary"><?php _e( 'Add New Taxonomy' ); ?></a>	</th>
						</tr>
						<tr id="newTaxRow">
							<form name="_TaxNew_<?php echo $i; ?>" action="admin-post.php" method="post">
							<?php wp_nonce_field( 'Anything-catalog-verify' ); ?>
							
							<td class="row-title"><input name="name" id="" type="text" value="" class="all-options" /></td>
							<td><input name="name_singular" id="" type="text" value="" class="all-options" /></td>
							<td><input name="name_plural" id="" type="text" value="" class="all-options" /></td>
							<td><input name="menu_name" id="" type="text" value="" class="all-options" /></td>
							<td colspan="2" class="upd"><button class="button-secondary dashicons dashicons-yes" type="submit" name="_Confirm_Edit" value="" /></td>
							<input type="hidden" name="_pos" value="<?php echo $i; ?>" />
							<input type="hidden" name="associated" value="<?php echo $CptDetails->name; ?>" /> 
							<input type="hidden" name="action" value="ssysTaxonomy_create" />
							
							</form>
						</tr>
					</tfoot>
				</table>
				
			</div> <!-- post-body-content -->
			
			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">
				
				<div class="meta-box-sortables">
					
					<div class="postbox">
					
						<h3><span>About the Plugin</span></h3>
						<div class="inside">
							Content space
						</div> <!-- .inside -->
						
					</div> <!-- .postbox -->
					
				</div> <!-- .meta-box-sortables -->
				
			</div> <!-- #postbox-container-1 .postbox-container -->
			
		</div> <!-- #post-body .metabox-holder .columns-2 -->
		
		<br class="clear">
	</div> <!-- #poststuff -->
</div>
