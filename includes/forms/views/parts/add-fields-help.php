<?php defined( 'ABSPATH' ) or exit; ?>

<div class="nl4wp-admin">
	<h2><?php _e( 'Add more fields', 'newsletter-for-wp' ); ?></h2>

	<div class="help-text">

		<p>
			<?php echo __( 'To add more fields to your form, you will need to create those fields in NewsLetter first.', 'newsletter-for-wp' ); ?>
		</p>

		<p><strong><?php echo __( "Here's how:", 'newsletter-for-wp' ); ?></strong></p>

		<ol>
			<li>
				<p>
					<?php echo __( 'Log in to your NewsLetter account.', 'newsletter-for-wp' ); ?>
				</p>
			</li>
			<li>
				<p>
					<?php echo __( 'Add list fields to any of your selected lists.', 'newsletter-for-wp' ); ?>
					<?php echo __( 'Clicking the following links will take you to the right screen.', 'newsletter-for-wp' ); ?>
				</p>
				<ul class="children lists--only-selected">
					<?php foreach( $lists as $list ) { ?>
					<li data-list-id="<?php echo $list->id; ?>" class="<?php echo in_array( $list->id, $opts['lists'] ) ? '' : 'hidden'; ?>">
						<a href="https://admin.newsletter.com/lists/settings/merge-tags?id=<?php echo $list->web_id; ?>">
							<span class="screen-reader-text"><?php _e( 'Edit list fields for', 'newsletter-for-wp' ); ?> </span>
							<?php echo $list->name; ?>
						</a>
					</li>
					<?php } ?>
				</ul>
			</li>
			<li>
				<p>
					<?php echo __( 'Click the following button to have NewsLetter for WordPress pick up on your changes.', 'newsletter-for-wp' ); ?>
				</p>

				<p>
					<a class="button button-primary" href="<?php echo add_query_arg( array( '_nl4wp_action' => 'empty_lists_cache' ) ); ?>">
						<?php _e( 'Renew NewsLetter lists', 'newsletter-for-wp' ); ?>
					</a>
				</p>
			</li>
		</ol>


	</div>
</div>