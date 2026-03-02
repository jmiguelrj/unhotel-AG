var path = require('path');
var webpack = require('webpack');

module.exports = {
	entry: {
		blocks: './src/main.js',
	},
	output: {
		path: __dirname,
		filename: '[name].js',
	},
	watch: true,
	module: {
		rules: [{
				test: /\.(js|jsx|mjs)$/,
				exclude: /(node_modules|bower_components)/,
				use: {
					loader: 'babel-loader',
				},
			}
		],
	},
	resolve: {
		modules: [
			path.resolve(__dirname, 'src'),
			'node_modules'
		],
	}
};
