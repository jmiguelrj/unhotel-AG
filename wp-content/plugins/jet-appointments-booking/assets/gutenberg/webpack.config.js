const path = require( 'path' );
const TerserPlugin = require( 'terser-webpack-plugin' );

const WPExtractorPlugin = require(
	'@wordpress/dependency-extraction-webpack-plugin',
);

module.exports = {
	context: path.resolve( __dirname, 'src' ),
	entry: {
		'jfb/builder.actions': './actions/index.js',
		'jfb/builder.blocks': './blocks/index.js',
		'jfb/builder.actions.v2': './actions.v2/index.js',
	},
	output: {
		path: path.resolve( __dirname, '../js' ),
		filename: '[name].js',
		devtoolNamespace: 'jet-appointments-booking-block-editor',
	},
	resolve: {
		modules: [
			path.resolve( __dirname, 'src' ),
			'node_modules'
		],
		extensions: [ '.js', '.jsx' ],
	},
	plugins: [
		new WPExtractorPlugin(),
	],
	module: {
		rules: [
			{
				test: /\.jsx?$/,
				loader: 'babel-loader',
				exclude: /node_modules/,
			},
		],
	},
	externalsType: 'window',
	externals: {
		'jet-form-builder-components': [ 'jfb', 'components' ],
		'jet-form-builder-data': [ 'jfb', 'data' ],
		'jet-form-builder-actions': [ 'jfb', 'actions' ],
		'jet-form-builder-blocks-to-actions': [ 'jfb', 'blocksToActions' ],
	},
	optimization: {
		minimizer: [ new TerserPlugin( {
			extractComments: false,
		} ) ],
	},
};
