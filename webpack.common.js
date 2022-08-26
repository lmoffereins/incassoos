const path = require( "path" );
const webpack = require( "webpack" );
const MiniCssExtractPlugin = require( "mini-css-extract-plugin" );

const assetUrl = "/wp-content/plugins/incassoos/assets/app/build/";
const dir = "./assets/app/src";

module.exports = {
	entry: {
		main: `${dir}/main.js`
	},
	output: {
		publicPath: assetUrl,
		path: path.resolve( __dirname, "./assets/app/build" ),
		filename: "main.js"
	},
	module: {
		rules: [{
			test: /\.(html)$/,
			type: "asset/source"
		}, {
			test: /\.(css)$/,
			use: ["text-loader"]
		}, {
			test: /\.(jpg|jpeg|png|svg)$/,
			type: "asset/resource"
		}, {
			test: /\.(scss)$/,
			use: [MiniCssExtractPlugin.loader, "css-loader", "sass-loader"]
		}]
	},

	// Resolve
	// Define or redefine module names for easy use in app modules. When referring
	// to module locations, the `$` suffix refers to a specific file. Note that
	// the definition order matters, so that module `fsm$` (the core file) is defined
	// before the `fsm` module which provides a path to other files in the folder.
	resolve: {
		alias: {

			// External packages
			"StateMachine": "javascript-state-machine",

			// Internal packages
			"api":          path.resolve( __dirname, `${dir}/modules/api/main.js` ),
			"components":   path.resolve( __dirname, `${dir}/modules/components/main.js` ),
			"fsm$":         path.resolve( __dirname, `${dir}/modules/fsm/main.js` ),
			"fsm":          path.resolve( __dirname, `${dir}/modules/fsm` ),
			"services":     path.resolve( __dirname, `${dir}/services/main.js` ),
			"store":        path.resolve( __dirname, `${dir}/modules/store/main.js` ),
			"translations": path.resolve( __dirname, `${dir}/translations/main.js` ),

			// Core globals
			"jwt":          path.resolve( __dirname, `${dir}/modules/core/jwt.js` ),
			"settings":     path.resolve( __dirname, `${dir}/modules/core/settings.js` ),
			"util":         path.resolve( __dirname, `${dir}/modules/core/util.js` )
		}
	},
	plugins: [
		new MiniCssExtractPlugin(),
		new webpack.NormalModuleReplacementPlugin(
			/^vue$/,
			path.resolve( __dirname, `${dir}/wrappers/vue-wrapper.js` )
		),
		new webpack.NormalModuleReplacementPlugin(
			/^q$/,
			path.resolve( __dirname, `${dir}/wrappers/q-wrapper.js` )
		)
	]
};
