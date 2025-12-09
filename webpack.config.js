const path = require( 'path' );
const glob = require( 'glob' );
const ExtractCSS = require( 'mini-css-extract-plugin' );
const RemoveStyleJS = require( 'webpack-fix-style-only-entries' );
const { WebpackManifestPlugin } = require( 'webpack-manifest-plugin' );

const options = { publicPath: '' };

module.exports = {
  entry: {
    admin: './assets/js/admin.js',
    'admin-styles': './assets/scss/admin.scss'
  },
  plugins : [
    new RemoveStyleJS( { silent : true } ),
    new ExtractCSS( { filename : `[name].[hash].css` } ),
    new WebpackManifestPlugin(options),
  ],
  output: {
    filename: '[name].[hash].js',
    path: path.resolve(__dirname, 'dist'),
  },
  module : {
    rules : [
      {
        test    : /\.js$/,
        exclude : /node_modules/,
        use     : {
          loader  : 'babel-loader',
          options : {
            'presets': [
              [
                /**
                 * @link https://babeljs.io/docs/en/babel-preset-env#corejs
                 */
                '@babel/preset-env',
                {
                  useBuiltIns: 'usage',
                  corejs: {
                    version: 3,
                    proposals: true
                  },
                }
              ],
              '@babel/preset-react',
              '@wordpress/default'
            ],
          },
        },
      },

      {
        test    : /\.scss$/i,
        include : glob.sync( process.cwd( ) + '/!(dist|node_modules)/' ),
        use     : [
          ExtractCSS.loader,
          {
            loader  : 'css-loader',
            options : {
              sourceMap : true,
              url       : false,
            },
          },
          {
            loader  : 'sass-loader',
            options : {
              sourceMap   : true,
              sassOptions : {
                includePaths : glob.sync( process.cwd( ) + '/!(dist|node_modules)/' ),
                indentType   : 'tab',
                indentWidth  : 1,
                outputStyle  : 'expanded',
              },
            },
          },
        ],
      },
    ],
  },
};
