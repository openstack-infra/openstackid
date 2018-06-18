/**
 * Copyright 2018 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
const path              = require('path');
const webpack           = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const UglifyJSPlugin    = require('uglifyjs-webpack-plugin');
const                 _ = require('lodash');
const CopyWebpackPlugin = require('copy-webpack-plugin');
var PRODUCTION  = process.env.NODE_ENV === 'production';
console.log(`FLAVOR ${process.env.FLAVOR}`);

var plugins = [
    new ExtractTextPlugin({ filename: 'css/[name].css' }),
    new webpack.optimize.CommonsChunkPlugin({
        name: 'common',
        filename: '__common__.js',
        //chunks: ["main", "utils"],
        deepChildren: true
    }),
    new webpack.ProvidePlugin({
        $: 'jquery',
        jQuery: 'jquery'
    }),
    new CopyWebpackPlugin([
            {from: './node_modules/bootstrap-tagsinput/dist', to: 'bootstrap-tagsinput'},
            {from: './node_modules/typeahead.js/dist', to: 'typeahead'},
            {from: './node_modules/jquery.cookie/jquery.cookie.js', to: 'jquery-cookie/jquery.cookie.js'},
            {from: './node_modules/crypto-js/crypto-js.js', to: 'crypto-js/crypto-js.js'},
            {from: './node_modules/pwstrength-bootstrap/dist', to: 'pwstrength-bootstrap'},
            {from: './node_modules/sweetalert2/dist', to: 'sweetalert2'},
            {from: './node_modules/urijs/src', to: 'urijs'},
        ],
        {copyUnmodified: false}
    ),
];

var productionPlugins = [
    //new UglifyJSPlugin(),
    new webpack.DefinePlugin({
        'process.env': {
            'NODE_ENV': JSON.stringify('production')
        }
    })
];

var devPlugins = [];

function styleLoader(loaders) {
    if (PRODUCTION)
        return ExtractTextPlugin.extract({ fallback: 'style-loader', use: loaders });
    return [ 'style-loader', ...loaders ];
}

/**
 *
 * @returns {object}
 */
function postCSSLoader() {
    return {
        loader: "postcss-loader",
        options: {
            plugins: function () {
                return [require("autoprefixer")];
            }
        }
    }
}

module.exports = {
    entry: {
        'index': './resources/assets/js/index.js',
    },
    devtool: "source-map",
    devServer: {
        contentBase: './dist',
        historyApiFallback: true
    },
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, 'public/assets'),
        publicPath: '/assets/'
    },
    module: {
        rules: [
            { test: /\.js$/, exclude: /node_modules/, loader: "babel-loader" },
            {
                test: /\.css$/,
                exclude: /\.module\.css$/,
                use: styleLoader(['css-loader', postCSSLoader()])
            },
            {
                test: /\.less/,
                exclude: /\.module\.less/,
                use: styleLoader(['css-loader', postCSSLoader(), 'less-loader'])
            },
            {
                test: /\.scss/,
                exclude: /\.module\.scss/,
                use: styleLoader(['css-loader', postCSSLoader(), 'sass-loader'])
            },
            {
                test: /\.woff(2)?(\?v=[0-9]\.[0-9]\.[0-9])?$/,
                use: "url-loader?limit=10000&minetype=application/font-woff&name=fonts/[name].[ext]"
            },
            {
                test: /\.(ttf|eot)(\?v=[0-9]\.[0-9]\.[0-9])?$/,
                use: "file-loader?name=fonts/[name].[ext]"
            },
            {
                test: /\.jpg|\.png|\.gif$/,
                use: "file-loader?name=images/[name].[ext]"
            },
            {
                test: /\.svg/,
                use: "file-loader?name=svg/[name].[ext]!svgo-loader"
            },
            {
                test: /\.json/,
                use: "json-loader"
            }
        ]
    },
    plugins: PRODUCTION
        ? plugins.concat(productionPlugins)
        : plugins.concat(devPlugins),
};