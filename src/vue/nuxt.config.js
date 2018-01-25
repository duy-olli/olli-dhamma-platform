// Load environment configuration with dotenv
const results = require('dotenv').config({
  path: `.env.${process.env.NODE_ENV}`
})
if (results.error) {
  throw results.error
}

const envConfig = results.parsed
const webpack = require('webpack')
const CompressionPlugin = require('compression-webpack-plugin')
const SWPrecacheWebpackPlugin = require('sw-precache-webpack-plugin')
const nodeExternals = require('webpack-node-externals')

module.exports = {
  mode: 'universal',
  cache: process.env.NODE_ENV === 'development' ? false : true,
  loading: {
    color: '#3498db',
    height: '2px'
  },
  env: envConfig,
  performance: {
    prefetch: true,
    hints: process.env.NODE_ENV === 'production' ? 'warning' : false
  },
  router: {
    middleware: ['i18n', 'check-auth']
  },
  head: {
    titleTemplate: '%s - Dhamma OLLI',
    meta: [
      { charset: 'utf-8' },
      {
        name: 'viewport',
        content:
          'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no'
      },
      {
        name: 'theme-color',
        content: '#ffffff'
      }
    ],
    link: [
      {
        rel: 'apple-touch-icon',
        sizes: '180x180',
        href: '/apple-touch-icon.png'
      },
      {
        rel: 'icon',
        type: 'image/png',
        sizes: '32x32',
        href: '/favicon-32x32.png'
      },
      {
        rel: 'icon',
        type: 'image/png',
        sizes: '16x16',
        href: '/favicon-16x16.png'
      },
      { rel: 'manifest', href: '/manifest.json' },
      { rel: 'mask-icon', href: '/safari-pinned-tab.svg', color: '#5bbad5' }
    ]
  },
  css: [
    { src: 'node_modules/flag-icon-css/css/flag-icon.min.css', lang: 'css' },
    { src: '~/assets/scss/style.scss', lang: 'scss' }
  ],
  render: {
    bundleRenderer: {
      shouldPreload: (file, type) => {
        return ['script', 'style'].includes(type)
      }
    }
  },
  build: {
    analyze: false,
    vendor: [
      'axios',
      'vue-i18n',
      'vue-multiple-back-top',
      'vue-json-tree-view',
      'element-ui',
      'lodash'
    ],
    extend(config, { isClient }) {
      // Extend only webpack config for client-bundle
      if (isClient) {
        config.devtool = 'eval-source-map'
      }

      // fix fs module not found
      config.node = {
        fs: 'empty'
      }
    },
    devtool: 'eval-cheap-module-source-map',
    plugins: [
      new webpack.IgnorePlugin(/^\.\/locale$/, [/moment$/], [/lodash$/], [/element-ui$/]),
      new CompressionPlugin({
        asset: '[path].gz[query]',
        algorithm: 'gzip',
        test: /\.js$|\.css$|\.html$/,
        threshold: 10240,
        minRatio: 0
      }),
      new SWPrecacheWebpackPlugin({
        minify: true
      }),
      new webpack.optimize.UglifyJsPlugin({
        compress: { warnings: false, drop_console: false },
        comments: false,
        sourceMap: true,
        minimize: false
      })
    ]
  },
  plugins: [
    { src: '~plugins/element-ui.js', ssr: true },
    { src: '~plugins/i18n.js', ssr: true },
    { src: '~plugins/scroll-to-top.js', ssr: false },
    { src: '~plugins/json-view.js', ssr: false },
    { src: '~plugins/vuex-router-sync.js', ssr: false}
  ],
  modules: [
    '@nuxtjs/component-cache',
    '@nuxtjs/pwa'
  ],
  render: {
    static: {
      maxAge: '1y',
      setHeaders (res, path) {
        if (path.includes('sw.js')) {
          res.setHeader('Cache-Control', 'public, max-age=0')
        }
      }
    }
  },
  rules: [
    {
      test: /\.(png|jpe?g|gif|svg)$/,
      loader: 'url-loader',
      query: {
        limit: 1000, // 1KO
        name: 'img/[name].[hash:7].[ext]'
      }
    },
    {
      test: /\.(woff2?|eot|ttf|otf)(\?.*)?$/,
      loader: 'url-loader',
      query: {
        limit: 1000, // 1 KO
        name: 'fonts/[name].[hash:7].[ext]'
      }
    },
    {
      test: /\.(webm|mp4)$/,
      loader: 'file-loader',
      options: {
        name: 'videos/[name].[hash:7].[ext]'
      }
    },
    {
      test: /\.js$/,
      use: ['babel-loader'],
      exclude: /node_modules/
    }
  ]
}
