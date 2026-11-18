const path = require('path');
const autoprefixer = require('autoprefixer');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');

/**
 * Bundle the dashboard with locally hosted dependencies.
 * @param {Object} env Webpack environment values.
 * @param {Object} argv Webpack command-line options.
 * @returns {Object} Webpack configuration.
 */
module.exports = function createWebpackConfig(env, argv) {
    return {
        entry: {charts: path.join(__dirname, 'src', 'charts', 'index.js')},
        output: {path: path.join(__dirname, 'dist'), filename: '[name]Index.js', clean: true},
        optimization: {minimizer: [new CssMinimizerPlugin(), new TerserPlugin()]},
        plugins: [new MiniCssExtractPlugin({filename: '[name].css'})],
        devtool: argv.mode === 'development' ? 'cheap-module-source-map' : 'source-map',
        module: {
            rules: [
                {test: /\.js$/, exclude: /node_modules/, use: {loader: 'babel-loader', options: {presets: ['@babel/preset-env']}}},
                {test: /\.(sa|sc|c)ss$/, use: [MiniCssExtractPlugin.loader, 'css-loader',
                    {loader: 'postcss-loader', options: {postcssOptions: {plugins: [autoprefixer()]}}}, 'sass-loader']},
            ],
        },
        externals: {jquery: 'jQuery'},
    };
};
