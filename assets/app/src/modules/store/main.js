/**
 * Store Construction
 *
 * @package Incassoos
 * @subpackage App/Store
 */
define([
	"vue",
	"vuex",
	"./state",
	"./mutations",
	"./actions",
	"./modules/consumers",
	"./modules/occasions",
	"./modules/orders",
	"./modules/products",
	"./modules/receipt"
], function( Vue, Vuex, state, mutations, actions, consumers, occasions, orders, products, receipt ) {

	// Enable store functionality
	Vue.use(Vuex);

	/**
	 * Create new reactive Vuex store
	 *
	 * @param {Object}
	 */
	return new Vuex.Store({
		strict: true,
		state: state,
		mutations: mutations,
		actions: actions,
		modules: {
			consumers: consumers,
			occasions: occasions,
			orders: orders,
			products: products,
			receipt: receipt
		}
	});
});
