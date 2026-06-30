export default {
  plugins: {
    'postcss-pxtorem': {
      propList: ['*'],
      mediaQuery: true,
    },
    'postcss-preset-env': {},
  },
}
