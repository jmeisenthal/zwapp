const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const extractLess = new MiniCssExtractPlugin({filename: 'css/zwapp.css', chunkFilename: '[id].css'});
const CopyPlugin = require('copy-webpack-plugin');

module.exports = {
  mode: 'development',
  entry: './js/zwapp.js',
  output: {
  	filename: 'main.js',
  	path: __dirname + '/public',

  },
  module: {
    rules: [
      {
        test: /\.less|css$/,
        use: [
        	{ 
        		loader: MiniCssExtractPlugin.loader,
        	},
        	{ loader: 'css-loader' },
        	{ loader: 'less-loader' }
    	]
      },
      // {
      // 	test: /\.svg$/,
      // 	// loader: 'svg-inline-loader'
      // 	loader: 'url-loader'
      // 	// loader: 'file-loader?name=../img/[name].[ext]'
      // },
      {
      	test: /\.png|svg$/,
      	loader: 'file-loader',
      	options: {
      		name: '[name].[ext]',
      		outputPath: 'img',
      		publicPath: '../img' // For url()'s caled in .less; otherwise url resolves to css/img/...'
      	}
      },
      {
        test: /\.(woff(2)?|ttf|TTF|eot|svg)(\?v=\d+\.\d+\.\d+)?$/,
        loader: 'file-loader',
        options: {
          name: '[name].[ext]',
          outputPath: 'fonts',
          publicPath: '../fonts'
        }
     }
   ],
  },
  plugins: [
  	extractLess,
 //    new CopyPlugin([
 //    	{ from: './php/index.php'},
 //    	{ from: './php/**/*'},
 //     	{ from: './img/*'}
	// ])
  ],
};