<?php

/**
 * Incassoos App UI template
 *
 * @package Incassoos
 * @subpackage App
 */

?>

<!doctype html>
<html ng-app="incassoos" lang="en-US">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title><?php echo wp_get_document_title(); ?></title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">

	<?php incassoos_app_head(); ?>
</head>
<body ng-controller="AppController" class="loading" ng-keyup="keyboardInput($event)">

	<header id="app-header">
		<nav class="navbar navbar-default">
			<div id="app-header-left" ng-class="{
				'active':   viewingAbout,
				'disabled': disableAbout()
			}" ng-click="toggleAbout()">
				<span id="about-toggle">
					<img src="https://www.gravatar.com/avatar/?d=mm&f=y" />
				</span>
			</div>
			<div id="app-header-center">
				<h1><?php echo esc_html_x( 'Incassoos', 'App title', 'incassoos' ); ?></h1>
			</div>
			<div id="app-header-right" ng-class="{
				'active':   inSettingsMode(),
				'disabled': disableSettings()
			}" ng-click="toggleSettingsMode()">
				<span id="settings-link" class="glyphicon glyphicon-cog"></span>
			</div>
		</nav>
	</header>

	<div id="app-body" ng-class="{
		'viewing-about':       viewingAbout,
		'viewing-occasion':    viewingOccasionSelector,
		'open-consumer-panel': shouldShowConsumers(),
		'open-receipt':        viewingReceipt(),
		'open-orders':   viewingOrders(),
		'viewing-order': shouldShowOrder(),
		'open-settings':       inSettingsMode(),
		'viewing-product':     inSettingsMode() && viewingProduct(),
		'viewing-consumer':    viewingConsumer()
	}">

		<div id="about" ng-click="toggleAbout($event)">
			<ol class="list-group list-group-nested rounded">
				<header class="list-group-item">
					<h4 class="list-title"><?php echo esc_html_x( 'About', 'App label', 'incassoos' ); ?></h4>
				</header>
				<li id="about-version" class="list-group-item">
					<span class="thing"><?php echo esc_html_x( 'Version', 'App label', 'incassoos' ); ?></span>
					<span class="value"><?php printf( 'Incassoos %s', incassoos_get_version() ); ?></span>
				</li>

				<?php if ( current_user_can( 'incassoos_admin_page-incassoos' ) ) : ?>

					<li id="admin-link" class="list-group-item">
						<span class="thing"><?php echo esc_html_x( 'Administration', 'App label', 'incasoos' ); ?></span>
						<span class="value">
							<a href="<?php echo esc_url( add_query_arg( 'page', 'incassoos', admin_url( 'admin.php' ) ) ); ?>"><?php _e( 'Go to Dashboard', 'incasoos' ); ?></a>
						</span>
					</li>

				<?php endif; ?>

				<li id="logged-in" class="list-group-item">
					<span class="thing"><?php echo esc_html_x( 'Logged-in as', 'App label', 'incassoos' ); ?></span>
					<span class="value"><?php echo incassoos_get_user_display_name(); ?></span>
				</li>
				<footer class="list-group-item action">
					<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">
						<i class="glyphicon glyphicon-log-out"></i>
					</a>
				</footer>
			</ol>
		</div><!-- #about -->

		<form id="occasion-selector" ng-submit="submitOccasion()">
			<ol class="list-group list-group-nested rounded">
				<header class="list-group-item">
					<h4 class="list-title"><?php esc_html_e( 'Set the Occasion', 'incassoos' ); ?></h4>
				</header>
				<li class="list-group-item tabs">
					<label for="new-occasion-title" state-trigger="occasion:create" class="current" tabindex="0"><?php echo esc_html_x( 'Create New', 'Occasion', 'incassoos' ); ?></label>
					<label for="select-occasion" ng-if="occasions" state-trigger="occasion:select" tabindex="0"><?php echo esc_html_x( 'Existing', 'Occasion', 'incassoos' ); ?></label>
				</li>
				<li class="list-group-item state-active" state-target="occasion:create">
					<label>
						<input id="new-occasion-title" type="text" ng-model="createOccasion.title" />
						on
						<fieldgroup id="new-occasion-date">
							<input type="number" step="1" min="1" max="31" ng-model="createOccasion.dateDay" />
							<input type="number" step="1" min="1" max="12" ng-model="createOccasion.dateMonth" />
							<input type="number" step="1" min="1" max="9999" ng-model="createOccasion.dateYear" />
						</span>
					</label>
				</li>
				<li class="list-group-item state-inactive" ng-if="occasions" state-target="occasion:select">
					<label>
						<select id="select-occasion" ng-model="currentOccasion.id">
							<option value=""><?php esc_html_e( '&mdash; Select an Occasion &mdash;', 'incassoos' ); ?></option>
							<option ng-repeat="occasion in occasions" value="{{occasion.id}}">{{occasion.name}}</option>
						</select>
					</label>
				</li>
				<footer class="list-group-item">
					<label>
						<span id="submit-occasion" tabindex="0"></span>
						<input type="submit" value="<?php echo esc_html_x( 'Submit', 'Occasion', 'incassoos' ); ?>" />
					</label>
				</footer>
			</ol>
		</form><!-- #occasion-selector -->

		<!-- Input Form -->

		<div id="app-input-form">
			<header id="receipt-consumer" ng-click="togglePanel()">
				<div class="header-avatar">
					<img ng-src="{{inSettingsMode() ? 'https://www.gravatar.com/avatar/?d=mm&f=y' : consumer.avatar}}">
				</div>
				<div id="receipt-consumer-name">
					<span class="consumer-name">{{consumer.name}}</span>
					<span ng-hide="isConsumerGroupless()" class="consumer-group">{{consumer.group.name}}</span>
				</div>
			</header><!-- #receipt-consumer -->

			<div id="receipt-form">
				<div id="list-products" ng-click="isLargeScreen() || ( viewingReceipt() && toggleReceipt() )">
					<ol id="select-products" class="list-group">
						<header class="list-group-item" ng-click="inSettingsMode() && isLargeScreen() && setupNewProduct()">
							<h4 class="list-title">
								{{inSettingsMode()
									? '<?php esc_html_e( 'Manage Products',   'incassoos' ); ?>'
									: '<?php esc_html_e( 'Product Selection', 'incassoos' ); ?>'
								}}
								<span class="badge" ng-show="getReceiptAmount()">{{getReceiptAmount()}}</span>
							</h4>
						</header>
						<inc-product-list-items
							products="products"
							selector="addProductToReceipt(a)"
							action-secondary="subtractProductFromReceipt(a)"
							icon-secondary="{{inSettingsMode() ? 'trash' : 'minus'}}"
							counter="getProductSelected(a)">
						</inc-product-list-items>
					</ol>
				</div><!-- #list-products -->

				<div id="list-consumers">
					<ol id="select-consumers" class="list-group loading ng-class:{'searching': searchingConsumers()};">
						<header class="list-group-item">
							<h4 class="list-title">
								{{inSettingsMode()
									? '<?php esc_html_e( 'Manage Consumers',   'incassoos' ); ?>'
									: '<?php esc_html_e( 'Consumer Selection', 'incassoos' ); ?>'
								}}
							</h4>

							<input id="consumer-search" type="search" ng-model="consumerSearch">
							<label for="consumer-search" ng-click="toggleConsumerSearch($event)"></label>
						</header>
						<inc-consumer-list-items
							groups="groups"
							consumers="consumers"
							filter="showConsumer(a)"
							selector="selectConsumer(a)"
							selected="isConsumerSelected(a)"
							presser="toggleCurrentConsumer(a)"
							searcher="consumerSearch">
						</inc-consumer-list-items>
					</ol>
				</div><!-- #list-consumers -->
			</div><!-- #receipt-form -->

			<div id="receipt-products">
				<div id="receipt-button" ng-class="{'active': !isReceiptEmpty()}" ng-click="toggleReceipt()">
					<span>{{getReceiptPrice() | currency: "&euro;"}}</span>
				</div>

				<div id="receipt-overview">
					<ol id="products-selected" class="list-group rounded">
						<header class="list-group-item">
							<h4 class="list-title">
								<?php esc_html_e( 'Receipt', 'incassoos' ); ?>
								<span class="badge" ng-show="getReceiptAmount()">{{getReceiptAmount()}}</span>
							</h4>
						</header>
						<div class="list-group-wrapper">
							<li class="list-group-item" ng-repeat="product in receipt">
								<span class="product-name">{{product.name}}</span>
								<span class="product-amount badge">{{product.amount}}</span>
							</li>
						</div>
					</ol>
				</div>
			</div><!-- #receipt-summary -->

			<div id="product-details" ng-click="toggleCurrentProduct($event)">
				<ol class="list-group list-group-nested rounded">
					<header class="list-group-item">
						<h4 class="list-title">
							{{editingProduct()
								? '<?php esc_html_e( 'Edit product', 'incassoos' ); ?>: ' + currentProduct.name
								: '<?php esc_html_e( 'Add new Product', 'incassoos' ); ?>'
							}}
						</h4>
					</header>
					<div class="list-group-wrapper">
						<li class="list-group-item">
							<label>
								<span><?php echo esc_html_x( 'Name', 'App product label', 'incassoos' ); ?></span>
								<input id="product-detail-name" select-on-focus type="text" ng-model="currentProduct._edit.name"/>
							</label>
						</li>
						<li class="list-group-item">
							<label>
								<span><?php echo esc_html_x( 'Price', 'App product label', 'incassoos' ); ?></span>
								<input id="product-detail-price" string-to-price type="number" step="0.01" min="0.00" ng-model="currentProduct._edit.price"/>
							</label>
						</li>
					</div>
					<footer class="list-group-item action">
						<button type="button" class="alert-danger" ng-click="doActionSecondary()"><i class="glyphicon glyphicon-remove"></i></button>
						<button type="button" class="alert-success" ng-click="doActionPrimary()"><i class="glyphicon glyphicon-ok"></i></button>
					</footer>
				</ol>
			</div><!-- #product-details -->

			<div id="consumer-details" class="orders" ng-click="toggleCurrentConsumer($event)">
				<ol class="list-group list-group-nested rounded">
					<div class="header-avatar">
						<img ng-src="{{currentConsumer.avatar ? currentConsumer.avatar : ''}}" />
					</div>
					<header class="list-group-item">
						<h4 class="list-title">
							{{inSettingsMode()
								? '<?php esc_html_e( 'Edit consumer', 'incassoos' ); ?>'
								: currentConsumer.name
							}}</h4>
					</header>
					<inc-product-list-items
						ng-if="!inSettingsMode()"
						products="currentConsumer.products"
					></inc-product-list-items>
					<footer class="list-group-item" ng-if="!inSettingsMode()">
						<span class="order-header">{{currentOccasion.name}}</span>
						<span class="order-price">{{currentConsumerGetTotal() | currency: "&euro;"}}
							<span ng-if="currentConsumer.limit">/ {{currentConsumer.limit | currency: "&euro;"}}</span>
						</span>
					</footer>

					<div class="list-group-wrapper" ng-if="inSettingsMode()">
						<li class="list-group-item">
							<label>
								<span><?php echo esc_html_x( 'Name', 'App consumer label', 'incassoos' ); ?></span>
								<span class="value">{{currentConsumer.name}}</span>
							</label>
						</li>
						<li class="list-group-item">
							<label>
								<span><?php echo esc_html_x( 'Limit', 'App consumer label', 'incassoos' ); ?></span>
								<input id="consumer-limit" string-to-price type="number" step="0.01" min="0.00" ng-model="currentConsumer._edit.limit"/>
							</label>
						</li>
						<li class="list-group-item">
							<label>
								<span><?php esc_html_e( 'Show consumer', 'incassoos' ); ?></span>
								<div class="form-checkbox">
									<input type="checkbox" ng-model="currentConsumer._edit.show" /><label></label>
								</div>
							</label>
						</li>
					</div>
					<footer class="list-group-item action" ng-if="inSettingsMode()">
						<button type="button" class="alert-danger" ng-click="doActionSecondary()"><i class="glyphicon glyphicon-remove"></i></button>
						<button type="button" class="alert-success" ng-click="doActionPrimary()"><i class="glyphicon glyphicon-ok"></i></button>
					</footer>
				</ol>
			</div><!-- #consumer-details -->
		</div><!-- #app-input-form -->

		<!-- Order History -->

		<div id="app-orders" class="orders ng-class:{'no-orders': ! getOrdersCount()};">
			<ol id="all-orders" class="list-group loading">
				<header class="list-group-item" ng-click="toggleOrders()">
					<h4 class="list-title">
						<?php esc_html_e( 'Orders', 'incassoos' ); ?>
						<span class="badge">{{getOrdersCount()}}</span>
					</h4>
				</header>
				<div class="list-group-wrapper">
					<li class="list-group-item ng-class:{'edited': order.isEdited};" ng-repeat="order in orders | orderBy: '-'" ng-click="toggleCurrentOrder( order )">
						<div class="order-header">
							<span class="order-consumer">{{order.consumerName}}</span>
							<span class="order-size badge">{{sumOrderAmount( order )}}</span>
						</div>
						<span class="order-time">{{order.timestamp | date: 'HH:mm'}}</span>
					</li>
					<footer class="list-group-item">
						{{ haveOrders()
							? '<?php esc_html_e( 'Your history starts here', 'incassoos' ); ?>'
							: '<?php esc_html_e( 'Select an Occasion',       'incassoos' ); ?>'
						}}
						<span class="glyphicon glyphicon-{{haveOrders() ? 'glass' : 'erase'}}"></span>
					</footer>
				</div>
			</ol><!-- #history-orders -->

			<div id="single-order" ng-class="{'editing': editingCurrentOrder(), 'consumer-panel': editingOrderConsumer}" ng-click="toggleCurrentOrder($event)">
				<ol class="list-group list-group-nested rounded">
					<header class="list-group-item" ng-click="toggleOrderConsumerPanel()">
						<h4 class="list-title">
							{{getOrderConsumerName()}}
							<span class="badge">{{sumOrderAmount( false, true )}}</span>

							<span class="open-consumer-panel glyphicon glyphicon-chevron-right"></span>
							<span class="close-consumer-panel glyphicon glyphicon-chevron-left"></span>
						</h4>
					</header>
					<inc-product-list-items
						products="products"
						filter="showOrderProduct(a)"
						selector="addProductToOrder(a)"
						action-secondary="subtractProductFromOrder(a)"
						counter="getOrderProductAmount(a)">
					</inc-product-list-items>
					<inc-consumer-list-items
						groups="groups"
						consumers="consumers"
						filter="showConsumer(a)"
						selector="selectOrderConsumer(a)"
						selected="isOrderConsumerSelected(a)">
					</inc-consumer-list-items>
					<li class="list-group-item">
						<div class="order-header">
							<span class="order-time">{{currentOrder.timestamp | date: 'dd-MM-yyyy HH:mm:ss'}}</span>
						</div>
						<span class="order-price">{{sumOrderPrice() | currency: "&euro;"}}</span>
					</li>
					<footer class="list-group-item action">
						<button type="button" ng-class="classActionSecondary()" ng-click="doActionSecondary()"><i class="glyphicon glyphicon-{{iconActionSecondary()}}"></i></button>
						<button type="button" ng-class="classActionPrimary()" ng-click="doActionPrimary()"><i class="glyphicon glyphicon-{{iconActionPrimary()}}"></i></button>
					</footer>
				</ol>
			</div><!-- #single-order -->
		</div><!-- #app-orders -->

	</div><!-- #app-body -->

	<!-- App Actions (Footer) -->

	<footer id="app-footer">
		<button type="button" id="action-primary" ng-class="classActionPrimary()" ng-click="doActionPrimary()">
			<span class="glyphicon glyphicon-{{iconActionPrimary()}}"></span>
		</button>
		<button type="button" id="action-secondary" ng-class="classActionSecondary()" ng-click="doActionSecondary()">
			<span class="glyphicon glyphicon-{{iconActionSecondary()}}"></span>
		</button>
	</footer><!-- #app-footer -->

<?php incassoos_app_footer(); ?>

</body>
</html>
