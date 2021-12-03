<?php 
$current_user = wp_get_current_user();	
$roles = $current_user->roles;
$role = array_shift( $roles ); 

?>

<!-- Content -->
<div class="row">

	<!-- Item -->
	<div class="col-lg-3 col-md-6">
		<div class="dashboard-stat color-1">
			<div class="dashboard-stat-content"><h4>0</h4> <span><?php esc_html_e('Number of Suppliers','sa_core'); ?></span></div>
			<div class="dashboard-stat-icon"><i class="im im-icon-Truck"></i></div>
		</div>
	</div>

	<!-- Item -->
	<div class="col-lg-3 col-md-6">
		<div class="dashboard-stat color-2">
			<div class="dashboard-stat-content"><h4>0</h4> <span><?php esc_html_e('Documents Processed','sa_core'); ?></span></div>
			<div class="dashboard-stat-icon"><i class="im im-icon-Line-Chart"></i></div>
		</div>
	</div>
	
	<!-- Item -->
	<div class="col-lg-3 col-md-6">
		<div class="dashboard-stat color-3">
			<div class="dashboard-stat-content wallet-totals"><h4>0</h4> <span><?php esc_html_e('Total Due','sa_core'); ?> <strong class="wallet-currency">$</strong></span></div>
			<div class="dashboard-stat-icon"><i class="im im-icon-Money-2"></i></div>
		</div>
	</div>
	
	<!-- Item -->
	<div class="col-lg-3 col-md-6">
		<div class="dashboard-stat color-4">
			<div class="dashboard-stat-content wallet-totals"><h4>0</h4> <span><?php esc_html_e('Average Amount','sa_core') ?> <strong class="wallet-currency">$</strong></span></div>
			<div class="dashboard-stat-icon"><i class="im im-icon-Coins"></i></div>
		</div>
	</div>
	
</div>