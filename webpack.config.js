const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const extractLess = new MiniCssExtractPlugin({filename: 'css/zwapp.css', chunkFilename: '[id].css'});
const CopyPlugin = require('copy-webpack-plugin');

module.exports = {
  mode: 'development',
  output: {
  	path: __dirname + '/public',

  },
  module: {
    rules: [
      {
        test: /\.less$/,
        use: [
        	{ 
        		loader: MiniCssExtractPlugin.loader,
        	},
        	{ loader: 'css-loader' },
        	{ loader: 'less-loader' }
    	]
      },
    ],
  },
  plugins: [
  	extractLess,
    new CopyPlugin([{ from: './php/index.php'}])
  ],
};